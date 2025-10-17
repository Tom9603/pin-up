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
                const scrollY = window.scrollY; // garde la position
                card.classList.toggle('open');
                window.scrollTo({ top: scrollY }); // empêche le saut de page
            };
        });
    }

    // Quand on clique sur un onglet
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const id = tab.dataset.id;

            // 1️⃣ Enlève la classe "active" sur tous les onglets
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            // 2️⃣ Ferme tous les articles ouverts
            document.querySelectorAll('.article-card.open').forEach(openCard => {
                openCard.classList.remove('open');
            });

            // 3️⃣ Affiche uniquement la catégorie sélectionnée
            lists.forEach(list => {
                list.style.display = list.id === `cat-${id}` ? 'block' : 'none';
            });

            // 4️⃣ Réactive les événements sur les nouvelles cartes
            initCards();
        });
    });

    // Active la première catégorie par défaut
    if (tabs.length > 0) tabs[0].click();
});
