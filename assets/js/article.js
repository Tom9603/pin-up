document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.tab-btn');
    const lists = document.querySelectorAll('.articles-list');

    function initCards() {
        const cards = document.querySelectorAll('.articles-list:not([style*="display: none"]) .article-card');

        cards.forEach(card => {
            const toggleText = card.querySelector('.toggle-text');

            toggleText.onclick = (e) => {
                e.stopPropagation();
                const wasOpen = card.classList.contains('open');
                card.classList.toggle('open');

                if (!wasOpen) {
                    requestAnimationFrame(() => {
                        const top = card.getBoundingClientRect().top + window.scrollY - 80;
                        window.scrollTo({ top, behavior: 'smooth' });
                    });
                }
            };
        });
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const id = tab.dataset.id;

            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            document.querySelectorAll('.article-card.open').forEach(openCard => {
                openCard.classList.remove('open');
            });

            lists.forEach(list => {
                list.style.display = list.id === `cat-${id}` ? 'block' : 'none';
            });

            initCards();
        });
    });

    if (tabs.length > 0) tabs[0].click();
});
