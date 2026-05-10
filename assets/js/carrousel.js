document.addEventListener('DOMContentLoaded', () => {

    const carousel = document.querySelector('.carousel');
    const track = document.querySelector('.carousel-track');
    const items = document.querySelectorAll('.carousel-item');
    if (!carousel || !track || items.length === 0) return;

    let index = 0;

    function goTo(i) {
        const n = items.length;
        index = ((i % n) + n) % n;
        track.style.transform = `translateX(-${100 * index}%)`;
    }

    function next() { goTo(index + 1); }
    function prev() { goTo(index - 1); }

    const nextBtn = document.querySelector('.next');
    const prevBtn = document.querySelector('.prev');
    if (nextBtn) nextBtn.onclick = next;
    if (prevBtn) prevBtn.onclick = prev;

    // ─── Support tactile : swipe gauche/droite ───
    const SWIPE_THRESHOLD = 40;
    let startX = 0;
    let startY = 0;
    let lastX = 0;
    let isTouching = false;
    let isHorizontal = false;

    carousel.addEventListener('touchstart', (e) => {
        if (e.touches.length !== 1) return;
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        lastX = startX;
        isTouching = true;
        isHorizontal = false;
    }, { passive: true });

    carousel.addEventListener('touchmove', (e) => {
        if (!isTouching) return;
        const x = e.touches[0].clientX;
        const y = e.touches[0].clientY;
        const dx = x - startX;
        const dy = y - startY;

        // Détermine si le geste est horizontal dès qu'on a bougé d'au moins 10px
        if (!isHorizontal && (Math.abs(dx) > 10 || Math.abs(dy) > 10)) {
            isHorizontal = Math.abs(dx) > Math.abs(dy);
        }

        if (isHorizontal) {
            // Empêche le swipe-back du navigateur quand le geste est clairement horizontal
            if (e.cancelable) e.preventDefault();
            lastX = x;
        }
    }, { passive: false });

    carousel.addEventListener('touchend', () => {
        if (!isTouching) return;
        isTouching = false;

        if (!isHorizontal) return;

        const dx = lastX - startX;
        if (Math.abs(dx) < SWIPE_THRESHOLD) return;

        if (dx < 0) next();
        else        prev();
    });

    carousel.addEventListener('touchcancel', () => {
        isTouching = false;
        isHorizontal = false;
    });

    // ─── Support souris (desktop) : drag ───
    let mouseDown = false;
    let mouseStartX = 0;

    track.addEventListener('mousedown', (e) => {
        mouseDown = true;
        mouseStartX = e.clientX;
        track.style.cursor = 'grabbing';
        e.preventDefault();
    });

    document.addEventListener('mouseup', (e) => {
        if (!mouseDown) return;
        mouseDown = false;
        track.style.cursor = '';

        const dx = e.clientX - mouseStartX;
        if (Math.abs(dx) < SWIPE_THRESHOLD) return;

        if (dx < 0) next();
        else        prev();
    });

    // ─── Clavier : flèches gauche/droite quand le carrousel est focus ───
    carousel.tabIndex = 0;
    carousel.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') prev();
        else if (e.key === 'ArrowRight') next();
    });
});
