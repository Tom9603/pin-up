document.addEventListener('DOMContentLoaded', function() {
    let calendarEl = document.getElementById('calendar');

    if (calendarEl) {
        let calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'fr',
            events: '/api/events',

            headerToolbar: {
                left: 'title',
                center: '',
                right: 'prev,next today'
            },

            buttonText:{
                today: "Aujourd'hui",
                prev: "Préc",
                next: "Suiv",
            },
            eventClick: function(info) {
                let eventId = info.event.id;

                // On stocke l'ID de l'événement dans un data-attribute
                document.getElementById('modal').dataset.eventId = eventId;

                // On récupère la liste des réservations
                fetch(`/api/event/${eventId}/reservations`)
                    .then(res => res.json())
                    .then(data => {
                        let list = document.getElementById('reservationList');
                        list.innerHTML = "";

                        if (data.length === 0) {
                            list.innerHTML = "<li>Aucune réservation</li>";
                        } else {
                            data.forEach(user => {
                                list.innerHTML += `<li>${user}</li>`;
                            });
                        }

                        // Efface les anciens messages
                        document.getElementById('reservationMessage').innerText = "";

                        // Ouvre la modale
                        document.getElementById('modal').style.display = "flex";
                    });
            }
        });

        calendar.render();

        // Bouton Réserver
        document.getElementById('reserveBtn').addEventListener('click', function() {
            let eventId = document.getElementById('modal').dataset.eventId;

            fetch(`/api/reserve/${eventId}`, { method: "POST" })
                .then(res => res.json())
                .then(data => {
                    let msg = document.getElementById('reservationMessage');
                    if (data.success) {
                        msg.innerText = "Réservation réussie !";
                        msg.style.color = "green";
                    } else {
                        msg.innerText = "Erreur : " + data.error;
                        msg.style.color = "red";
                    }
                });
        });

        // Bouton Annuler
        document.getElementById('unreserveBtn').addEventListener('click', function() {
            let eventId = document.getElementById('modal').dataset.eventId;

            fetch(`/api/unreserve/${eventId}`, { method: "DELETE" })
                .then(res => res.json())
                .then(data => {
                    let msg = document.getElementById('reservationMessage');
                    if (data.success) {
                        msg.innerText = "🗑Réservation annulée.";
                        msg.style.color = "orange";
                    } else {
                        msg.innerText = "Erreur : " + data.error;
                        msg.style.color = "red";
                    }
                });
        });

        // Bouton Fermer
        document.getElementById('closeModal').addEventListener('click', function () {
            document.getElementById('modal').style.display = "none";
        });
    }
});
