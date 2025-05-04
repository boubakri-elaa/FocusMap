// goal-progress.js
document.addEventListener('DOMContentLoaded', function () {
    // === Gérer les étapes complétées (via AJAX + LocalStorage) ===
    document.querySelectorAll('.etape-card .btn-success.btn-sm').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const etapeCard = btn.closest('.etape-card');
            const objectifId = etapeCard.dataset.objectifId;
            const etapeId = etapeCard.dataset.etapeId;
            const key = `etape-${objectifId}-${etapeId}`;

            // LocalStorage toggle
            const isDone = localStorage.getItem(key) === 'done';
            if (isDone) {
                localStorage.removeItem(key);
                etapeCard.classList.remove('bg-success', 'text-white');
            } else {
                localStorage.setItem(key, 'done');
                etapeCard.classList.add('bg-success', 'text-white');
            }

            updateObjectifState(objectifId);

            // AJAX to mark step as completed
            fetch(`/objectifs/${objectifId}/etapes/${etapeId}/complete`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ completed: !isDone })
            })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        btn.disabled = true;
                        if (data.badge_unlocked) showBadgeNotification(data.badge);
                        updateUserProgress();
                    } else {
                        alert('Erreur : ' + (data.error || 'Impossible de marquer l’étape'));
                    }
                })
                .catch(error => {
                    console.error('Error completing step:', error);
                    alert('Erreur lors de la mise à jour de l’étape : ' + error.message);
                });
        });
    });

    // === Initialiser les états à l'ouverture ===
    document.querySelectorAll('.etape-card[data-objectif-id]').forEach(card => {
        const objectifId = card.dataset.objectifId;
        updateObjectifState(objectifId);
    });

    updateUserProgress(); // Barre progression initiale

    // === Fonctions ===
    function updateObjectifState(objectifId) {
        const cards = document.querySelectorAll(`.etape-card[data-objectif-id="${objectifId}"]`);
        let done = 0;

        cards.forEach(card => {
            const etapeId = card.dataset.etapeId;
            const key = `etape-${objectifId}-${etapeId}`;
            if (localStorage.getItem(key) === 'done') {
                card.classList.add('bg-success', 'text-white');
                done++;
            } else {
                card.classList.remove('bg-success', 'text-white');
            }
        });

        const objectifCard = document.querySelector(`.card:has(.etape-card[data-objectif-id="${objectifId}"])`);
        if (objectifCard) {
            objectifCard.classList.remove('objectif-done', 'objectif-progress', 'objectif-pending');
            if (done === cards.length && done > 0) {
                objectifCard.classList.add('objectif-done');
            } else if (done > 0) {
                objectifCard.classList.add('objectif-progress');
            } else {
                objectifCard.classList.add('objectif-pending');
            }
        }
    }

    function updateUserProgress() {
        fetch('/api/user/progress', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const progressBar = document.getElementById('user-progress');
                    if (progressBar) {
                        progressBar.style.width = `${data.percentage}%`;
                        progressBar.textContent = `${data.percentage}%`;
                    }
                    if (data.new_badges && data.new_badges.length > 0) {
                        data.new_badges.forEach(badge => {
                            showBadgeNotification(badge);
                            const badgeElement = document.querySelector(`.badge[data-badge-id="${badge.id}"]`);
                            if (badgeElement) {
                                badgeElement.classList.remove('locked');
                                badgeElement.classList.add('unlocked');
                            }
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error updating progress:', error);
            });
    }

    function showBadgeNotification(badge) {
        const container = document.getElementById('badge-notification-container');
        if (container) {
            container.innerHTML = `
                <div class="badge-unlocked">
                    <i class="${badge.icon} fa-3x" style="color: #ff6f61;"></i>
                    <h4>Nouveau Badge Débloqué !</h4>
                    <p>${badge.name}</p>
                    <small>${badge.description}</small>
                </div>
            `;
            setTimeout(() => container.innerHTML = '', 5000);
        }
    }
});