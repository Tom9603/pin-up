const track = document.querySelector('.carousel-track');
const items = document.querySelectorAll('.carousel-item');
const prev = document.querySelector('.prev');
const next = document.querySelector('.next');
let index = 0;

function updateCarousel() {
    track.style.transform = `translateX(-${index * 100}%)`;
}

next.addEventListener('click', () => {
    index = (index + 1) % items.length;
    updateCarousel();
});

prev.addEventListener('click', () => {
    index = (index - 1 + items.length) % items.length;
    updateCarousel();
});
