@extends('layouts.app')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<div class="container py-5">
    <h1 class="text-center mb-4 fw-bold" style="font-family: Poppins, sans-serif; color: #1a73e8;">Timeline de motivation pour "{{ $objective->titre }}"</h1>

    <!-- Form to add a milestone -->
    <div class="card shadow-sm mb-5 border-0" style="border-radius: 15px;">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3" style="color: #1a73e8;">Ajouter un jalon</h5>
            <form action="{{ route('milestones.store', $objective->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea class="form-control" id="description" name="description" required rows="4" style="border-radius: 10px;"></textarea>
                </div>
                <div class="mb-3">
                    <label for="milestone_date" class="form-label fw-semibold">Date</label>
                    <input type="date" class="form-control" id="milestone_date" name="milestone_date" required style="border-radius: 10px;">
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label fw-semibold">Image (optionnel)</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*" style="border-radius: 10px;">
                </div>
                <button type="submit" class="btn btn-primary fw-bold px-4" style="border-radius: 10px; background-color: #1a73e8; border: none;">Ajouter</button>
            </form>
        </div>
    </div>

    <!-- Horizontal Timeline -->
    <div class="timeline-container">
        @forelse ($objective->milestones->sortBy('milestone_date') as $index => $milestone)
            @php
                // Calculate position as a percentage based on dates
                $firstDate = $objective->milestones->min('milestone_date');
                $lastDate = $objective->milestones->max('milestone_date');
                $totalDuration = ($firstDate && $lastDate && $firstDate != $lastDate) ? $lastDate->diffInDays($firstDate) : 1;
                $currentPosition = ($firstDate && $milestone->milestone_date) ? ($firstDate->diffInDays($milestone->milestone_date) / $totalDuration * 100) : ($index * (100 / ($objective->milestones->count() ?: 1)));
            @endphp
            <div class="timeline-item" style="left: {{ $currentPosition }}%;">
                <div class="timeline-marker" data-index="{{ $index }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $milestone->milestone_date->format('d M Y') }}">
                    <div class="marker-dot"></div>
                </div>
                <div class="timeline-content card shadow-sm border-0" id="content-{{ $index }}" style="display: none;">
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-2" style="color: #1a73e8;">{{ $milestone->milestone_date->format('d M Y') }}</h6>
                        <p class="mb-2">{{ $milestone->description }}</p>
                        @if ($milestone->image_url)
                            <img src="{{ asset('storage/' . $milestone->image_url) }}" alt="Milestone image" class="img-fluid rounded" style="max-width: 150px;">
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center text-muted">Aucun jalon ajouté pour le moment.</p>
        @endforelse
    </div>
</div>

<style>
body {
    font-family: Poppins, sans-serif;
}

.timeline-container {
    position: relative;
    height: 120px;
    margin: 50px 0;
    background: linear-gradient(90deg, #e3f0ff, #ffffff);
    border-radius: 10px;
    padding: 20px 0;
}

.timeline-container::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 5%;
    width: 90%;
    height: 6px;
    background: #1a73e8;
    transform: translateY(-50%);
    border-radius: 3px;
}

.timeline-item {
    position: absolute;
    top: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.timeline-marker {
    width: 24px;
    height: 24px;
    background: #ffffff;
    border: 3px solid #1a73e8;
    border-radius: 50%;
    cursor: pointer;
    transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
}

.timeline-marker.active {
    border-color: #ff6200;
    box-shadow: 0 0 12px rgba(255, 98, 0, 0.5);
}

.timeline-marker:hover {
    transform: scale(1.3);
}

.timeline-marker .marker-dot {
    width: 12px;
    height: 12px;
    background: #1a73e8;
    border-radius: 50%;
    margin: 3px auto;
    transition: background 0.3s ease;
}

.timeline-marker.active .marker-dot {
    background: #ff6200;
}

.timeline-content {
    position: absolute;
    top: 40px;
    left: 50%;
    transform: translateX(-50%);
    width: 280px;
    border-radius: 10px;
    background: #ffffff;
    z-index: 10;
    opacity: 0;
    transition: opacity 0.2s ease, transform 0.2s ease;
    transform: translateX(-50%) scale(0.95);
}

.timeline-content.show {
    opacity: 1;
    display: block !important;
    transform: translateX(-50%) scale(1);
}

@media (max-width: 768px) {
    .timeline-container {
        height: auto;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .timeline-container::before {
        width: 6px;
        height: 90%;
        left: 50%;
        top: 5%;
        transform: translateX(-50%);
    }

    .timeline-item {
        position: relative;
        left: auto !important;
        transform: none;
        margin-bottom: 30px;
        width: 100%;
    }

    .timeline-content {
        position: relative;
        top: auto;
        left: auto;
        transform: none;
        width: 100%;
        display: block !important;
        opacity: 1;
        transform: scale(1);
    }

    .timeline-marker {
        margin: 0 auto;
    }
}
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('Script loaded, initializing timeline markers');
    const markers = document.querySelectorAll('.timeline-marker');

    markers.forEach(marker => {
        console.log('Setting up marker with index:', marker.getAttribute('data-index'));
        
        marker.addEventListener('mouseenter', function () {
            console.log('Mouse entered marker:', marker.getAttribute('data-index'));
            // Hide all other cards and reset marker states
            markers.forEach(m => {
                m.classList.remove('active');
                const content = m.parentNode.querySelector('.timeline-content');
                if (content) {
                    content.classList.remove('show');
                    console.log('Hid content for marker:', m.getAttribute('data-index'));
                }
            });

            // Show the hovered marker's card
            this.classList.add('active');
            const content = this.parentNode.querySelector('.timeline-content');
            if (content) {
                content.classList.add('show');
                console.log('Showed content for marker:', this.getAttribute('data-index'));
            } else {
                console.log('No content found for marker:', this.getAttribute('data-index'));
            }
        });

        marker.addEventListener('mouseleave', function () {
            console.log('Mouse left marker:', marker.getAttribute('data-index'));
            // Hide the card and reset marker state
            this.classList.remove('active');
            const content = this.parentNode.querySelector('.timeline-content');
            if (content) {
                content.classList.remove('show');
                console.log('Hid content for marker:', this.getAttribute('data-index'));
            }
        });
    });

    // Initialize Bootstrap tooltips
    console.log('Initializing tooltips');
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
});
</script>
@endsection