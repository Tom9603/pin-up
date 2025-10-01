document.addEventListener("DOMContentLoaded", function () {
    const openBtn = document.getElementById("open-menu");
    const closeBtn = document.getElementById("close-menu"); // si tu veux garder ton bouton "fermer"
    const menu = document.getElementById("side-menu");
    const overlay = document.querySelector(".overlay");
    const body = document.body;

    function openMenu() {
        menu.classList.add("open");
        overlay.classList.add("active");
        body.classList.add("no-scroll");
        body.style.overflow = "hidden"; // bloque le scroll
        openBtn.classList.add("active"); // transforme burger → croix
    }

    function closeMenu() {
        menu.classList.add("closing");
        overlay.classList.remove("active");
        body.classList.remove("no-scroll");
        body.style.overflow = "";
        openBtn.classList.remove("active"); // croix → burger

        menu.addEventListener("transitionend", () => {
            menu.classList.remove("open", "closing");
        }, { once: true });
    }

    openBtn.addEventListener("click", () => {
        if (menu.classList.contains("open")) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    overlay.addEventListener("click", closeMenu);
});
