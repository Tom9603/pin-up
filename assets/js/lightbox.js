(function () {
    let overlay = null;

    function ensureOverlay() {
        if (overlay) return overlay;
        overlay = document.createElement('div');
        overlay.className = 'lightbox';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.innerHTML = `
            <button class="lightbox__close" aria-label="Fermer (Échap)">&times;</button>
            <img class="lightbox__img" alt="">
        `;
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay || e.target.classList.contains('lightbox__close')) {
                close();
            }
        });
        document.body.appendChild(overlay);
        return overlay;
    }

    function open(src, alt) {
        const ov = ensureOverlay();
        ov.querySelector('.lightbox__img').src = src;
        ov.querySelector('.lightbox__img').alt = alt || '';
        ov.classList.add('lightbox--open');
        document.body.style.overflow = 'hidden';
    }

    function close() {
        if (overlay) overlay.classList.remove('lightbox--open');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') close();
    });

    document.addEventListener('click', (e) => {
        const img = e.target.closest('.article-images img, .event-thumb, .zoomable');
        if (!img || img.tagName !== 'IMG') return;

        e.preventDefault();
        e.stopPropagation();
        open(img.src, img.alt);
    });
})();
