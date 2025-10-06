document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.tab-btn');
    const lists = document.querySelectorAll('.articles-list');

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            // cache tout
            lists.forEach(list => list.style.display = 'none');
            buttons.forEach(b => b.classList.remove('active'));

            // montre la catégorie sélectionnée
            const id = btn.dataset.id;
            document.getElementById('cat-' + id).style.display = 'block';
            btn.classList.add('active');
        });
    });

    // active la première catégorie par défaut
    if (buttons.length > 0) {
        buttons[0].classList.add('active');
        document.getElementById('cat-' + buttons[0].dataset.id).style.display = 'block';
    }
});
