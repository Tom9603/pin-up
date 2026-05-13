document.addEventListener("DOMContentLoaded", () => {
    if (document.body.classList.contains('login-page')) {
        return;
    }

    const container = document.querySelector(".stars");
    if (!container) return;

    const images = ["star1.png", "star2.png"];
    const TOTAL_STARS = 60;
    const ATTEMPTS_PER_STAR = 12;
    const BUFFER = 28; // marge autour des zones protégées (px)

    const PROTECTED_SELECTOR = [
        'h1', 'h2', 'h3',
        '.shop-title', '.contact-subtitle', '.auth-subtitle',
        '.tab-btn', '.fc-toolbar h2'
    ].join(', ');

    function getProtectedRects() {
        const scrollY = window.scrollY;
        const scrollX = window.scrollX;
        return Array.from(document.querySelectorAll(PROTECTED_SELECTOR))
            .map(el => el.getBoundingClientRect())
            .filter(r => r.width > 0 && r.height > 0)
            .map(r => ({
                left:   r.left   + scrollX - BUFFER,
                top:    r.top    + scrollY - BUFFER,
                right:  r.right  + scrollX + BUFFER,
                bottom: r.bottom + scrollY + BUFFER,
            }));
    }

    function intersects(starLeft, starTop, starSize, rect) {
        return !(
            starLeft + starSize < rect.left ||
            starLeft > rect.right ||
            starTop + starSize < rect.top ||
            starTop > rect.bottom
        );
    }

    function placeStars() {
        container.innerHTML = '';

        const protectedRects = getProtectedRects();
        const containerRect = container.getBoundingClientRect();
        const W = containerRect.width;
        const H = containerRect.height || document.body.scrollHeight;

        for (let i = 0; i < TOTAL_STARS; i++) {
            const size = 10 + Math.random() * 20;

            for (let a = 0; a < ATTEMPTS_PER_STAR; a++) {
                const xPct = Math.random() * 100;
                const yPct = Math.random() * 100;
                const xPx = (xPct / 100) * W;
                const yPx = (yPct / 100) * H;

                const docX = containerRect.left + window.scrollX + xPx;
                const docY = containerRect.top  + window.scrollY + yPx;

                const overlaps = protectedRects.some(r => intersects(docX, docY, size, r));
                if (overlaps) continue;

                const star = document.createElement("img");
                star.src = "/images/stars/" + images[Math.floor(Math.random() * images.length)];
                star.className = "star";
                star.alt = "";
                star.style.width = size + "px";
                star.style.left = xPct + "%";
                star.style.top = yPct + "%";
                container.appendChild(star);

                break;
            }
        }
    }

    const ready = document.fonts && document.fonts.ready
        ? document.fonts.ready
        : Promise.resolve();

    ready.then(() => {
        setTimeout(placeStars, 150);
    });
});
