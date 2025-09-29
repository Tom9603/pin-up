document.addEventListener("DOMContentLoaded", function () {
    const openBtn = document.getElementById("open-menu");
    const closeBtn = document.getElementById("close-menu");
    const menu = document.getElementById("side-menu");
    const overlay = document.querySelector(".overlay");
    const body = document.body;

    function openMenu() {
        menu.classList.add("open");
        overlay.classList.add("active");
        body.classList.add("no-scroll");
    }

    function closeMenu() {
        menu.classList.add("closing");
        overlay.classList.remove("active");
        body.classList.remove("no-scroll");

        menu.addEventListener("transitionend", () => {
            menu.classList.remove("open", "closing");
        }, { once: true });
    }

    openBtn.addEventListener("click", openMenu);
    closeBtn.addEventListener("click", closeMenu);
    overlay.addEventListener("click", closeMenu);
});
