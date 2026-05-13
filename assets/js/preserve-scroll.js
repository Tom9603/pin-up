
(function () {
    const KEY = 'preserve-scroll-y';

    const saved = sessionStorage.getItem(KEY);
    if (saved !== null) {
        sessionStorage.removeItem(KEY);
        const y = parseInt(saved, 10);

        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }

        const restore = () => window.scrollTo(0, y);
        restore();
        document.addEventListener('DOMContentLoaded', restore);
        window.addEventListener('load', restore);
    }

    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (form && form.matches && form.matches('form[data-preserve-scroll]')) {
            sessionStorage.setItem(KEY, String(window.scrollY));
        }
    });
})();
