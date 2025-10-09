document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.article-card');
    const tabs = document.querySelectorAll('.tab-btn');

    cards.forEach(card => {
        card.addEventListener('click', () => {
            card.classList.toggle('open');
        });
    });

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.article-card.open').forEach(openCard => {
                openCard.classList.remove('open');
            });
        });
    });
});
