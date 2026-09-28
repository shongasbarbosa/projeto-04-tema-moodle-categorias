(function () {
    'use strict';

    var STORAGE_KEY = 'categoriaboard-site-theme';
    var MODES = ['system', 'light', 'dark'];

    function resolveMode(mode) {
        if (mode === 'light' || mode === 'dark') {
            return mode;
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function loadPreference() {
        try {
            var stored = localStorage.getItem(STORAGE_KEY);
            return MODES.indexOf(stored) !== -1 ? stored : 'system';
        } catch (e) {
            return 'system';
        }
    }

    function savePreference(mode) {
        try {
            localStorage.setItem(STORAGE_KEY, mode);
        } catch (e) {
            // Storage unavailable: the switch still works for this page load.
        }
    }

    function updateGalleryImages(resolved) {
        var images = document.querySelectorAll('[data-shot-light]');
        images.forEach(function (img) {
            var src = resolved === 'dark' ? img.dataset.shotDark : img.dataset.shotLight;
            if (src && img.getAttribute('src') !== src) {
                img.setAttribute('src', src);
            }
        });
    }

    function applyMode(mode) {
        var resolved = resolveMode(mode);
        document.documentElement.setAttribute('data-theme', resolved);
        updateGalleryImages(resolved);
    }

    function updatePressedState(buttons, mode) {
        buttons.forEach(function (button) {
            button.setAttribute('aria-pressed', button.dataset.mode === mode ? 'true' : 'false');
        });
    }

    function init() {
        var buttons = document.querySelectorAll('.themeswitcher__button');
        if (!buttons.length) {
            return;
        }

        var currentMode = loadPreference();
        updatePressedState(buttons, currentMode);
        applyMode(currentMode);

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                currentMode = button.dataset.mode;
                applyMode(currentMode);
                updatePressedState(buttons, currentMode);
                savePreference(currentMode);
            });
        });

        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
            if (currentMode === 'system') {
                applyMode('system');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
