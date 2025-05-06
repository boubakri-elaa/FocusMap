document.addEventListener('DOMContentLoaded', function () {
    console.log('JavaScript loaded and DOMContentLoaded fired'); // Debug: Check if script runs

    const forms = document.querySelectorAll('.complete-etape-form');
    console.log('Found forms:', forms.length); // Debug: Check if forms are found

    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // Prevent default form submission
            console.log('Form submission intercepted'); // Debug: Check if event is caught

            const etapeId = this.closest('.etape-card').dataset.etapeId;
            const objectifId = this.closest('.etape-card').dataset.objectifId;
            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                console.log('Fetch response received'); // Debug: Check if response is received
                return response.json();
            })
            .then(data => {
                console.log('JSON data:', data); // Debug: Log the response data
                if (data.success) {
                    const etapeCard = document.querySelector(`.etape-card[data-etape-id="${etapeId}"]`);
                    if (data.completed) {
                        etapeCard.classList.add('bg-success', 'text-white');
                        etapeCard.classList.remove('bg-light');
                    }

                    const card = etapeCard.closest('.card');
                    const progressBar = card.querySelector('.progress-bar');
                    const badge = card.querySelector('.badge');
                    const totalEtapes = card.querySelectorAll('.etape-card').length;
                    const completedEtapes = card.querySelectorAll('.etape-card.bg-success').length;
                    const objectifProgress = totalEtapes > 0 ? (completedEtapes / totalEtapes) * 100 : 0;

                    progressBar.style.width = `${objectifProgress}%`;
                    progressBar.setAttribute('aria-valuenow', objectifProgress);

                    progressBar.classList.remove('bg-success', 'bg-warning', 'bg-danger');
                    if (objectifProgress === 100) {
                        progressBar.classList.add('bg-success');
                        badge.className = 'badge bg-success';
                        badge.innerHTML = '<i class="bi bi-check-circle"></i> Complété';
                        card.className = 'card h-100 shadow-sm bg-success text-white';
                    } else if (objectifProgress > 0) {
                        progressBar.classList.add('bg-warning');
                        badge.className = 'badge bg-warning text-dark';
                        badge.innerHTML = '<i class="bi bi-hourglass-split"></i> En cours';
                        card.className = 'card h-100 shadow-sm bg-warning text-dark';
                    } else {
                        progressBar.classList.add('bg-danger');
                        badge.className = 'badge bg-danger';
                        badge.innerHTML = '<i class="bi bi-x-circle"></i> Non commencé';
                        card.className = 'card h-100 shadow-sm bg-danger text-white';
                    }
                } else {
                    alert('Erreur : ' + data.message);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error); // Debug: Log any errors
                alert('Une erreur s’est produite.');
            });
        });
    });
});