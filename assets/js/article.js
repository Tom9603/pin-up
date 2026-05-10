document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.tab-btn');
    const lists = document.querySelectorAll('.articles-list');

    // Fonction pour activer les "Lire plus / Fermer" sur les cartes visibles
    function initCards() {
        const cards = document.querySelectorAll('.articles-list:not([style*="display: none"]) .article-card');

        cards.forEach(card => {
            const toggleText = card.querySelector('.toggle-text');

            toggleText.onclick = (e) => {
                e.stopPropagation();
                const wasOpen = card.classList.contains('open');
                card.classList.toggle('open');

                // Quand on ouvre, on amène la carte en haut de la zone visible (smooth)
                if (!wasOpen) {
                    requestAnimationFrame(() => {
                        const top = card.getBoundingClientRect().top + window.scrollY - 80;
                        window.scrollTo({ top, behavior: 'smooth' });
                    });
                }
            };
        });
    }

    // quand on clique sur un onglet
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const id = tab.dataset.id;

            // Quitte l'article avec sa classe active
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            // Quand on veut fermer l'article
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
