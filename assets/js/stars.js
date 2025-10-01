document.addEventListener("DOMContentLoaded", () => {
    const container = document.querySelector(".stars");
    const images = ["star2.png"];

    for (let i = 0; i < 20; i++) {
        const star = document.createElement("img");
        star.src = "/images/stars/" + images[Math.floor(Math.random() * images.length)];
        star.className = "star";

        star.style.left = Math.random() * 100 + "%";
        star.style.top = Math.random() * 100 + "%";

        const size = 20 + Math.random() * 70;
        star.style.width = size + "px";

        container.appendChild(star);
    }
});
