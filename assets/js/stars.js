document.addEventListener("DOMContentLoaded", () => {
    const container = document.querySelector(".stars");
    const images = ["star6.png"];

    for (let i = 0; i < 40; i++) {
        const star = document.createElement("img");
        star.src = "/images/stars/" + images[Math.floor(Math.random() * images.length)];
        star.className = "star";

        star.style.left = Math.random() * 100 + "%";
        star.style.top = Math.random() * 100 + "%";

        const size = 10 + Math.random() * 20;
        star.style.width = size + "px";

        container.appendChild(star);
    }
});
