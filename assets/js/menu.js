document.addEventListener("DOMContentLoaded", function () {
    const openBtn = document.getElementById("open-menu");
    const closeBtn = document.getElementById("close-menu");
    const menu = document.getElementById("side-menu");
    const overlay = document.querySelector(".overlay");

    function openMenu() {
        menu.classList.add("open");
        overlay.classList.add("active");
    }

    function closeMenu() {
        menu.classList.add("closing");
        overlay.classList.remove("active");

        menu.addEventListener("transitionend", () => {
            menu.classList.remove("open", "closing");
        }, { once: true });
    }

    openBtn.addEventListener("click", openMenu);
    closeBtn.addEventListener("click", closeMenu);
    overlay.addEventListener("click", closeMenu);
});
