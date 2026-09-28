#!/usr/bin/env node
// Visual + automated contrast check for theme_categoriaboard, in light and
// dark mode, at desktop and mobile widths. Screenshots and the axe report
// go to .visual/ (gitignored, never published — see README, section
// "Modo escuro com verificação visual obrigatória").
//
// Usage: npm run visual-check
import {chromium} from 'playwright';
import AxeBuilder from '@axe-core/playwright';
import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import {PAGES, INTERACTIVE_PAGES, VIEWPORTS, MODES} from './pages.mjs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, '../..');
const BASE_URL = process.env.CATEGORIAB_BASE_URL || 'http://localhost:8000';
const OUT_DIR = path.join(ROOT, '.visual');
const PAGE_TIMEOUT_MS = 15000;
// Last-resort circuit breaker per page/spec. Must comfortably exceed the
// worst case of gotoAsUser's two attempts (each up to ~1.5x PAGE_TIMEOUT_MS
// between the goto/login/hop-follow steps) — sized too tight here, this
// used to fire while a previous attempt's goto/login was still running in
// the background (Promise.race abandons the loser, it does not cancel it),
// which then raced the *next* spec's actions on the same shared page.
const OUTER_TIMEOUT_MS = 60000;
const CREDENTIALS = {
    aluno: {username: 'aluno.demo', password: 'Categoriaboard@2026'},
    admin: {username: 'admin', password: 'Categoriaboard@2026'},
};

fs.mkdirSync(OUT_DIR, {recursive: true});

function log(...args) {
    // Flushed immediately (no buffering downstream): callers pipe this
    // through `tee`, never `| tail`, precisely so progress is visible while
    // the script is still running instead of only at the very end.
    console.log(new Date().toISOString().slice(11, 19), ...args);
}

/**
 * Runs fn() but rejects after ms if it hasn't settled — Moodle pages can
 * poll (notifications, messaging) forever, so Playwright's own
 * waitForNavigation/goto timeouts aren't always enough of a backstop.
 */
async function withTimeout(fn, ms, label) {
    let timer;
    const timeout = new Promise((_, reject) => {
        timer = setTimeout(() => reject(new Error(`timeout after ${ms}ms: ${label}`)), ms);
    });
    try {
        return await Promise.race([fn(), timeout]);
    } finally {
        clearTimeout(timer);
    }
}

/**
 * @param {import('playwright').Page} page
 * @param {'aluno'|'admin'} who
 */
async function login(page, who) {
    // Single attempt — retrying a whole login (session death, wrong-user
    // page, etc.) is gotoAsUser's job. Stacking two retry loops on top of
    // each other let worst-case duration blow past the outer per-page
    // circuit breaker while still running in the background, corrupting
    // later steps that shared the same page.
    const {username, password} = CREDENTIALS[who];
    // networkidle, not 'load': core_form/passwordunmask clones #password
    // into a fresh input (to add the show/hide toggle) via an AMD module
    // that is still fetching/running right after the 'load' event. A fill()
    // that lands before that clone swap is on a node about to be discarded,
    // so the value is silently lost and the form later submits with an
    // empty password — confirmed by capturing the actual POST body during a
    // flaky run. Waiting for networkidle here (safe: the login page itself
    // has no long-polling background requests, unlike /my/ dashboards)
    // ensures the clone has already happened before we touch the field.
    await page.goto(`${BASE_URL}/login/index.php`, {waitUntil: 'networkidle', timeout: PAGE_TIMEOUT_MS});
    const usernameField = await page.$('#username');
    if (!usernameField) {
        throw new Error(`login form not present at ${page.url()} (already logged in as someone else? cookies should have been cleared first)`);
    }
    await page.fill('#username', username);
    await page.fill('#password', password);

    await Promise.all([
        page.waitForNavigation({waitUntil: 'load', timeout: PAGE_TIMEOUT_MS}),
        page.click('#loginbtn'),
    ]);

    // On a brand-new session, Moodle inserts an extra hop to confirm
    // cookies work (redirects through login/index.php?testsession=...)
    // before landing on the real destination. Keep following navigations
    // until we're actually off any login/index.php variant.
    let hops = 0;
    while (page.url().includes('/login/index.php') && hops < 2) {
        await page.waitForNavigation({waitUntil: 'load', timeout: 3000}).catch(() => {});
        hops += 1;
    }
}

/**
 * @param {string} mode 'light' | 'dark'
 */
async function setModePreference(page, mode) {
    // The page needs to already have loaded http://localhost:8000 once
    // before localStorage is writable for that origin.
    await page.evaluate((m) => {
        localStorage.setItem('theme_categoriaboard_preference', m);
    }, mode);
}

/**
 * Navigates to `path` as `who`, logging in first if needed. If the session
 * turns out to have died mid-run (an occasional flake under load — the page
 * unexpectedly lands back on login/index.php even though login() succeeded
 * earlier), forces one fresh login and retries the navigation once before
 * giving up.
 *
 * @returns {Promise<string>} the (possibly updated) loggedInAs value
 */
async function gotoAsUser(page, path, who, mode, loggedInAs) {
    for (let attempt = 1; attempt <= 2; attempt += 1) {
        if (who !== 'none' && (loggedInAs !== who || attempt === 2)) {
            if (loggedInAs !== null) {
                await page.context().clearCookies();
            }
            await login(page, who);
            // Right after Moodle regenerates the session id on login, the
            // very next request occasionally still carries the pre-login
            // cookie (a settle race, not a real auth failure — confirmed
            // this page reliably works one or two navigations later). A
            // short pause here is cheaper than the full re-login attempt#2
            // below has to fall back to otherwise.
            await page.waitForTimeout(500);
            await setModePreference(page, mode);
            loggedInAs = who;
        } else if (who === 'none' && loggedInAs !== null) {
            await page.context().clearCookies();
            loggedInAs = null;
        }

        await page.goto(`${BASE_URL}${path}`, {waitUntil: 'load'});
        await setModePreference(page, mode);
        await page.reload({waitUntil: 'load'});

        if (who === 'none' || !page.url().includes('/login/index.php')) {
            return loggedInAs;
        }
    }
    throw new Error(`session died: still on login page after re-login (${path})`);
}

/**
 * Interactions needed to reach a UI state that a plain page load doesn't
 * show (a popover, a modal, edit mode). Each handler is best-effort: a
 * selector that doesn't match on a given page/mode is swallowed so one
 * missing interaction doesn't fail the whole spec — the capture just runs
 * against whatever state was actually reached.
 *
 * IMPORTANT: 'confirmmodal' opens a delete confirmation dialog and then
 * presses Escape — it must never click the dialog's own confirm/"Excluir"
 * button, since that would actually delete the demo activity.
 */
const INTERACTION_HANDLERS = {
    async usermenu(page) {
        await page.click('[data-region="usermenu"] .dropdown-toggle, [data-region="usermenu"] a[role="button"]', {timeout: 5000}).catch(() => {});
        await page.waitForTimeout(300);
    },
    async notifications(page) {
        await page.click('[data-region="popover-region-notifications"] a, .popover-region-toggle', {timeout: 5000}).catch(() => {});
        await page.waitForTimeout(300);
    },
    async editmode(page) {
        await page.click('#editmodeswitch, [id^="editmodeswitch"], .editmode-switch-form input[type="checkbox"]', {timeout: 5000}).catch(() => {});
        await page.waitForTimeout(500);
    },
    async coursetabs(page) {
        // Tabs are visible on plain load; also open the "more" overflow
        // dropdown if the viewport is narrow enough to show one.
        await page.click('.nav-tabs .dropdown-toggle', {timeout: 3000}).catch(() => {});
        await page.waitForTimeout(200);
    },
    async messagedrawer(page) {
        await page.click('[data-region="popover-region-messages"] a, [data-region="message-drawer-toggle"]', {timeout: 5000}).catch(() => {});
        await page.waitForTimeout(500);
        // Open the first conversation, if any, so bubble colors render too.
        await page.click('[data-region="message-drawer"] [data-conversation-id]', {timeout: 3000}).catch(() => {});
        await page.waitForTimeout(300);
    },
    async filepicker(page) {
        await page.click('a:has-text("Adicionar..."), .fp-btn-add a, a.fp-btn-choose', {timeout: 5000}).catch(() => {});
        await page.waitForTimeout(500);
        await page.waitForSelector('.moodle-dialogue-base .moodle-dialogue', {timeout: 5000}).catch(() => {});
    },
    async tinymce(page) {
        await page.waitForSelector('.tox-tinymce', {timeout: 8000}).catch(() => {});
        // Open a toolbar dropdown (e.g. paragraph formats) so the tox-menu
        // popup itself is part of the screenshot, not just the toolbar.
        await page.click('.tox-toolbar button:has-text("Parágrafo"), .tox-toolbar button[aria-label*="format" i]', {timeout: 3000}).catch(() => {});
        await page.waitForTimeout(300);
    },
    async datefield(page) {
        await page.waitForSelector('.fdate_selector, .fdate_time_selector', {timeout: 5000}).catch(() => {});
    },
    async confirmmodal(page) {
        await page.click('#editmodeswitch, [id^="editmodeswitch"], .editmode-switch-form input[type="checkbox"]', {timeout: 5000}).catch(() => {});
        await page.waitForTimeout(500);
        await page.click('[data-region="activity-actions"] .dropdown-toggle, .activity-item .dropdown-toggle', {timeout: 5000}).catch(() => {});
        await page.waitForTimeout(200);
        await page.click('[data-action="cmDelete"], a:has-text("Excluir")', {timeout: 5000}).catch(() => {});
        await page.waitForSelector('.modal.show .modal-body', {timeout: 5000}).catch(() => {});
        // Let the fade-in finish: axe otherwise measures the half-transparent text.
        await page.waitForTimeout(800);
        // Never confirm: the dialog stays open for the screenshot and the
        // context is discarded afterwards, so nothing gets deleted.
    },
};

/**
 * Scans the page for visible elements whose own resolved background color
 * (not inherited) is light (WCAG relative luminance > 0.6) — the kind of
 * "light block on a dark page" mismatch that a same-element color-contrast
 * check doesn't catch (the text on it can still individually pass AA).
 * Only meaningful in dark mode; callers gate on that.
 *
 * @param {import('playwright').Page} page
 * @returns {Promise<Array<{selector: string, luminance: number, bg: string}>>}
 */
async function detectLightBlocks(page) {
    return page.evaluate(() => {
        function relativeLuminance(r, g, b) {
            const lin = (c) => {
                const cs = c / 255;
                return cs <= 0.03928 ? cs / 12.92 : ((cs + 0.055) / 1.055) ** 2.4;
            };
            return 0.2126 * lin(r) + 0.7152 * lin(g) + 0.0722 * lin(b);
        }

        function describe(el) {
            const id = el.id ? `#${el.id}` : '';
            const cls = el.className && typeof el.className === 'string'
                ? `.${el.className.trim().split(/\s+/).slice(0, 3).join('.')}`
                : '';
            return `${el.tagName.toLowerCase()}${id}${cls}`;
        }

        const skip = /avatar|userpicture|userinitials|badge|categoriaboard-card__image|courseimage|coursebox.*image/i;
        const found = [];
        const seen = new Set();

        for (const el of document.querySelectorAll('body *')) {
            const rect = el.getBoundingClientRect();
            if (rect.width <= 40 || rect.height <= 20) {
                continue;
            }
            // IFRAME: TinyMCE's editable area is a separate document (a
            // WYSIWYG preview of the saved HTML), like an image from this
            // page's point of view; its surrounding chrome is still scanned.
            if (['IMG', 'SVG', 'IFRAME'].includes(el.tagName) || skip.test(el.className || '')) {
                continue;
            }

            const style = getComputedStyle(el);
            if (style.visibility === 'hidden' || style.display === 'none' || Number(style.opacity) === 0) {
                continue;
            }
            if (style.backgroundImage && style.backgroundImage !== 'none') {
                continue;
            }

            const m = style.backgroundColor.match(/rgba?\(([\d.]+),\s*([\d.]+),\s*([\d.]+)(?:,\s*([\d.]+))?\)/);
            if (!m) {
                continue;
            }
            const alpha = m[4] === undefined ? 1 : Number(m[4]);
            if (alpha < 0.5) {
                continue;
            }
            const [r, g, b] = [Number(m[1]), Number(m[2]), Number(m[3])];
            const luminance = relativeLuminance(r, g, b);
            if (luminance <= 0.6) {
                continue;
            }

            const selector = describe(el);
            const key = `${selector}|${style.backgroundColor}`;
            if (seen.has(key)) {
                continue;
            }
            seen.add(key);
            found.push({selector, luminance: Math.round(luminance * 100) / 100, bg: style.backgroundColor});
        }

        return found.slice(0, 30);
    });
}

async function run() {
    const browser = await chromium.launch();
    const results = [];
    const failures = [];

    for (const mode of MODES) {
        for (const viewport of VIEWPORTS) {
            log(`== modo=${mode} viewport=${viewport.name} ==`);
            const context = await browser.newContext({
                viewport: {width: viewport.width, height: viewport.height},
            });
            // Runs before any page script on every navigation. Setting the
            // preference with page.evaluate() after load raced with something
            // on the login page that clears localStorage (seen as the
            // preference reading back null and the page rendering light).
            await context.addInitScript((m) => {
                try {
                    localStorage.setItem('theme_categoriaboard_preference', m);
                } catch (e) {
                    // Storage unavailable: nothing to do.
                }
            }, mode);
            const page = await context.newPage();
            page.setDefaultTimeout(PAGE_TIMEOUT_MS);
            page.setDefaultNavigationTimeout(PAGE_TIMEOUT_MS);

            await page.goto(`${BASE_URL}/login/index.php`, {waitUntil: 'load'});
            await setModePreference(page, mode);

            let loggedInAs = null;

            for (const spec of [...PAGES, ...INTERACTIVE_PAGES]) {
                log(`  -> ${spec.name} (${spec.path}${spec.open ? `, abre ${spec.open}` : ''})`);
                try {
                    await withTimeout(async () => {
                        loggedInAs = await gotoAsUser(page, spec.path, spec.as, mode, loggedInAs);

                        if (spec.open && INTERACTION_HANDLERS[spec.open]) {
                            await INTERACTION_HANDLERS[spec.open](page);
                        }

                        await capture(page, spec.name, mode, viewport.name, results);
                    }, OUTER_TIMEOUT_MS, spec.name);
                } catch (error) {
                    log(`     FALHOU: ${error.message}`);
                    failures.push({name: spec.name, mode, viewport: viewport.name, error: error.message});
                    results.push({name: spec.name, mode, viewport: viewport.name, violations: null, error: error.message});
                }
            }

            await context.close();
        }
    }

    await browser.close();

    const summaryPath = path.join(OUT_DIR, 'summary.json');
    fs.writeFileSync(summaryPath, JSON.stringify(results, null, 2));

    console.log('\n=== Resumo (color-contrast + blocos claros no modo escuro) ===');
    let total = 0;
    let totalLightBlocks = 0;
    for (const r of results) {
        if (r.error) {
            console.log(`${r.mode.padEnd(6)} ${r.viewport.padEnd(8)} ${r.name.padEnd(26)} ERRO: ${r.error}`);
            continue;
        }
        total += r.violations;
        const lb = r.lightBlocks ? r.lightBlocks.length : 0;
        totalLightBlocks += lb;
        const lbLabel = r.mode === 'dark' ? `, ${lb} bloco(s) claro(s)` : '';
        console.log(`${r.mode.padEnd(6)} ${r.viewport.padEnd(8)} ${r.name.padEnd(26)} ${r.violations} violação(ões)${lbLabel}`);
        if (lb > 0) {
            for (const b of r.lightBlocks) {
                console.log(`         -> ${b.selector} (luminância ${b.luminance}, ${b.bg})`);
            }
        }
    }
    console.log(`\nTotal: ${total} violação(ões) de color-contrast em ${results.length} páginas x modo x largura.`);
    console.log(`Total: ${totalLightBlocks} bloco(s) claro(s) detectado(s) no modo escuro.`);
    if (failures.length > 0) {
        console.log(`Falhas (páginas que não carregaram a tempo): ${failures.length}`);
    }
    console.log(`Screenshots e relatório completo em: ${OUT_DIR}`);

    process.exitCode = (total > 0 || totalLightBlocks > 0 || failures.length > 0) ? 1 : 0;
}

/**
 * @param {import('playwright').Page} page
 * @param {string} name
 * @param {string} mode
 * @param {string} viewportName
 * @param {Array} results
 */
async function capture(page, name, mode, viewportName, results) {
    const filename = `${mode}-${viewportName}-${name}.png`;
    await page.screenshot({path: path.join(OUT_DIR, filename), fullPage: true, timeout: 10000});

    const axeResults = await new AxeBuilder({page}).withRules(['color-contrast']).analyze();
    const violations = axeResults.violations.reduce((sum, v) => sum + v.nodes.length, 0);

    const lightBlocks = mode === 'dark' ? await detectLightBlocks(page).catch(() => []) : [];

    results.push({
        name,
        mode,
        viewport: viewportName,
        violations,
        lightBlocks,
        details: axeResults.violations.map((v) => ({
            id: v.id,
            nodes: v.nodes.map((n) => ({html: n.html, target: n.target})),
        })),
    });
}

run().catch((error) => {
    console.error(error);
    process.exitCode = 1;
});
