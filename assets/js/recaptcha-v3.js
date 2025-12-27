(function () {
    function ensureTokenField(form) {
        let input = form.querySelector('input[name="g-recaptcha-response"]');
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'g-recaptcha-response';
            form.appendChild(input);
        }
        return input;
    }

    function onSubmit(e) {
        e.preventDefault();

        const form = e.currentTarget;
        const siteKey = form.dataset.recaptchaSitekey;
        const action = form.dataset.recaptchaAction || 'submit';

        if (!window.grecaptcha || !siteKey) {
            console.error('reCAPTCHA not loaded or missing site key');
            return;
        }

        grecaptcha.ready(async () => {
            const token = await grecaptcha.execute(siteKey, { action });
            ensureTokenField(form).value = token;

            // évite boucle
            form.removeEventListener('submit', onSubmit);
            form.submit();
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        document
            .querySelectorAll('form[data-recaptcha="v3"]')
            .forEach((form) => {
                form.addEventListener('submit', onSubmit);
            });
    });
})();
