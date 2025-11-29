document.addEventListener('DOMContentLoaded', () => {

    const track = document.querySelector('.carousel-track');
    const items = document.querySelectorAll('.carousel-item');
    let index = 0;

    document.querySelector('.next').onclick = () => track.style.transform = `translateX(-${100 * (++index % items.length)}%)`;

    document.querySelector('.prev').onclick = () => track.style.transform = `translateX(-${100 * ((--index + items.length) % items.length)}%)`;
});


