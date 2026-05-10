// Sauvegarde/restaure la position de scroll lors d'un submit de formulaire
// Active uniquement sur les forms avec l'attribut [data-preserve-scroll]

(function () {
    const KEY = 'preserve-scroll-y';

    // Restauration : appliquée le plus tôt possible pour éviter le "flash" en haut de page
    const saved = sessionStorage.getItem(KEY);
    if (saved !== null) {
        sessionStorage.removeItem(KEY);
        const y = parseInt(saved, 10);

        // Empêche le navigateur de gérer lui-même la restauration
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }

        const restore = () => window.scrollTo(0, y);
        restore();
        document.addEventListener('DOMContentLoaded', restore);
        window.addEventListener('load', restore);
    }

    // Capture : on enregistre la position juste avant l'envoi du form
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (form && form.matches && form.matches('form[data-preserve-scroll]')) {
            sessionStorage.setItem(KEY, String(window.scrollY));
        }
    });
})();
