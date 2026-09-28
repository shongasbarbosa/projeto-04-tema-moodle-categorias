// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Sistema / Claro / Escuro theme switcher, persisted in localStorage.
 *
 * The attribute that drives the color scheme (data-categoriaboard-theme) is
 * already applied to <html> by an inline, synchronous script rendered in
 * <head> (see theme/categoriaboard/templates/theme_boost/columns2.mustache
 * and .../login.mustache) before the page paints, so there is no
 * light-then-dark flash. This module only wires up the switcher buttons and
 * keeps the choice in sync across tabs.
 *
 * @module     theme_categoriaboard/thememode
 * @copyright  2026 Dhyego Barbosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const STORAGE_KEY = 'theme_categoriaboard_preference';
const ATTRIBUTE = 'data-categoriaboard-theme';
const MODES = ['system', 'light', 'dark'];

/**
 * @param {string} mode 'system' | 'light' | 'dark'
 * @returns {string} 'light' | 'dark'
 */
const resolveMode = (mode) => {
    if (mode === 'light' || mode === 'dark') {
        return mode;
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
};

/**
 * @param {string} mode
 */
const applyMode = (mode) => {
    document.documentElement.setAttribute(ATTRIBUTE, resolveMode(mode));
};

/**
 * @param {NodeListOf<HTMLElement>} buttons
 * @param {string} mode
 */
const updatePressedState = (buttons, mode) => {
    buttons.forEach((button) => {
        const isCurrent = button.dataset.mode === mode;
        button.setAttribute('aria-pressed', isCurrent ? 'true' : 'false');
        if (isCurrent) {
            // The mobile variant collapses to a single icon button, so the
            // current mode needs to be announced through its own label
            // instead of a visible "pressed" state on a hidden control.
            const toggle = document.querySelector('.categoriaboard-themeswitcher-mobile__toggle');
            if (toggle && toggle.dataset.baseLabel) {
                toggle.setAttribute('aria-label', `${toggle.dataset.baseLabel}: ${button.textContent.trim()}`);
            }
        }
    });
};

/**
 * @param {string} mode
 */
const savePreference = (mode) => {
    try {
        localStorage.setItem(STORAGE_KEY, mode);
    } catch (e) {
        // Storage may be unavailable (private browsing, quota). The switch
        // still works for the current page load, it just won't persist.
    }
};

/**
 * @returns {string}
 */
const loadPreference = () => {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        return MODES.includes(stored) ? stored : 'system';
    } catch (e) {
        return 'system';
    }
};

export const init = () => {
    const buttons = document.querySelectorAll('[data-categoriaboard-theme-button]');
    if (!buttons.length) {
        return;
    }

    let currentMode = loadPreference();
    updatePressedState(buttons, currentMode);

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            currentMode = button.dataset.mode;
            applyMode(currentMode);
            updatePressedState(buttons, currentMode);
            savePreference(currentMode);
        });
    });

    // Keep multiple open tabs in sync when the preference changes elsewhere.
    window.addEventListener('storage', (event) => {
        if (event.key !== STORAGE_KEY) {
            return;
        }
        currentMode = MODES.includes(event.newValue) ? event.newValue : 'system';
        applyMode(currentMode);
        updatePressedState(buttons, currentMode);
    });

    // Keep "system" mode in sync with OS-level changes while the page is open.
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (currentMode === 'system') {
            applyMode('system');
        }
    });
};
