<script>
(function () {
    var preference = 'auto';

    try {
        var stored = window.localStorage.getItem('g01_theme');

        if (
            stored === 'ivory'
            || stored === 'midnight'
            || stored === 'auto'
        ) {
            preference = stored;
        }
    } catch (error) {
        preference = 'auto';
    }

    var dark = false;

    try {
        dark = window.matchMedia(
            '(prefers-color-scheme: dark)'
        ).matches;
    } catch (error) {
        dark = false;
    }

    var resolved = preference === 'auto'
        ? (dark ? 'midnight' : 'ivory')
        : preference;

    document.documentElement.setAttribute(
        'data-g01-theme-preference',
        preference
    );
    document.documentElement.setAttribute(
        'data-g01-theme',
        resolved
    );
    document.documentElement.style.colorScheme =
        resolved === 'midnight' ? 'dark' : 'light';
})();
</script>
