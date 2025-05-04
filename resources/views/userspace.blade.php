@extends('layouts.app')

@section('content')

    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <style>
            body {
                background-color: #fffaf0;
            }

            .title-color {
                color: #ff6f61;
            }

            .btn-primary {
                background-color: #ff6f61;
                border: none;
            }

            .btn-primary:hover {
                background-color: #ff3e3e;
            }

            .card {
                border-left: 5px solid #ff6f61;
                background-color: #fff;
                border-radius: 10px;
            }

            .form-control,
            .form-select {
                border-radius: 10px;
                border: 2px solid #ffa07a;
            }

            .form-control:focus,
            .form-select:focus {
                border-color: #ff69b4;
                box-shadow: 0 0 0 0.2rem rgba(255, 105, 180, 0.25);
            }

            #jsmind_container {
                width: 100%;
                height: 600px;
                border: 2px dashed #ccc;
                background-color: #fefefe;
                overflow: auto;
                border-radius: 12px;
                padding: 10px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                margin-top: 20px;
                transition: all 0.3s ease-in-out;
            }

            @media (max-width: 768px) {
                #jsmind_container {
                    height: 400px;
                }
            }

            .jsmind-node:hover {
                transform: scale(1.05);
                transition: transform 0.2s;
                z-index: 10;
            }

            #map {
                height: 400px;
                width: 100%;
                border-radius: 12px;
                margin-top: 20px;
            }

            #calendar {
                width: 90%;
                max-width: 700px;
                margin: 30px auto;
                background-color: #fff5f0;
                border: 2px solid #ffd4c4;
                border-radius: 12px;
                padding: 15px;
            }

            .fc {
                border: none;
            }

            /* Sliding list for generated steps */
            .generated-steps-container {
                max-height: 200px;
                overflow-y: auto;
                margin-top: 10px;
                padding: 10px;
                background-color: #fefefe;
                border: 2px solid #ffd4c4;
                border-radius: 10px;
                transition: transform 0.3s ease-in-out;
                transform: translateX(-100%);
            }

            .generated-steps-container.show {
                transform: translateX(0);
            }

            .generated-step {
                cursor: pointer;
                padding: 8px;
                margin: 5px 0;
                background-color: #fff;
                border-left: 3px solid #ff6f61;
                border-radius: 5px;
                transition: background-color 0.2s;
            }

            .generated-step:hover {
                background-color: #f0f0f0;
            }

            .card.objectif-done {
        border-left: 5px solid #28a745 !important; /* Vert */
        background-color: #e9fbe9 !important;
    }

    .card.objectif-progress {
        border-left: 5px solid #ffc107 !important; /* Orange */
        background-color: #fff9e6 !important;
    }

    .card.objectif-pending {
        border-left: 5px solid #dc3545 !important; /* Rouge */
        background-color: #ffe5e5 !important;
    }

    .objectif-pending {
        border-left: 5px solid red;
        background-color: #ffe6e6; /* rouge clair */
    }

    .objectif-inprogress {
        border-left: 5px solid orange;
        background-color: #fff3cd; /* jaune clair */
    }

    .objectif-done {
        border-left: 5px solid green;
        background-color: #d4edda; /* vert clair */
    }
        </style>

        <link type="text/css" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsmind/style/jsmind.css" />
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jsmind/js/jsmind.js"></script>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    </head>

    <body>
        <div class="container">
            <h2 class="title-color">🎯 Bienvenue dans ton espace personnel, {{ Auth::user()->name }} !</h2>

            <!-- Large rectangle for adding an objectif -->
            <div class="mb-4 p-4 border rounded-3" style="border: 2px solid #007bff;">
                <h3 class="text-center mb-4" style="font-weight: bold; color: #007bff;">Ajouter un Objectif</h3>

                <!-- Row for form and mindmap -->
                <div class="row">
                    <!-- Formulary for objective (left side) -->
                    <div class="col-md-6">
                        <!-- Main form for adding objectif -->
                        <form action="{{ route('objectifs.store') }}" method="POST" class="mb-4" id="objectif-form">
                            @csrf
                            <input type="hidden" name="id" id="objectif-id">
                            <div class="mb-3">
                                <input type="text" name="titre" class="form-control" id="objectif-titre"
                                    placeholder="Titre de l’objectif" value="{{ old('titre', session('titre')) }}" required>
                                @error('titre')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <textarea name="description" class="form-control" placeholder="Décris ton objectif"
                                    required>{{ old('description') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <input type="date" name="deadline" class="form-control" value="{{ old('deadline') }}">
                            </div>
                            <div class="mb-3">
                                <input type="text" name="lieu" class="form-control" placeholder="Lieu (facultatif)"
                                    value="{{ old('lieu') }}">
                            </div>
                            <div class="mb-3">
                                <select name="type" class="form-select" required>
                                    <option value="">Type</option>
                                    <option value="Études" {{ old('type') == 'Études' ? 'selected' : '' }}>Études</option>
                                    <option value="Sport" {{ old('type') == 'Sport' ? 'selected' : '' }}>Sport</option>
                                    <option value="Lecture" {{ old('type') == 'Lecture' ? 'selected' : '' }}>Lecture</option>
                                    <option value="Projets" {{ old('type') == 'Projets' ? 'selected' : '' }}>Projets</option>
                                    <option value="Santé" {{ old('type') == 'Santé' ? 'selected' : '' }}>Santé</option>
                                </select>
                            </div>

                            <!-- Étapes dynamiques -->
                            <div class="mb-4 p-4 border rounded-3"
                                style="border: 2px solid #28a745; background-color: #f9f9f9;">
                                <h4 class="text-center" style="color: #28a745;">Étapes à suivre (facultatif)</h4>
                                <div id="etapes">
                                    @if (session('suggested_steps'))
                                        @foreach (session('suggested_steps') as $index => $step)
                                            <div class="etape mb-3">
                                                <div class="mb-3">
                                                    <input type="text" name="etapes[{{$index}}][titre]" class="form-control"
                                                        value="{{ $step }}" placeholder="Titre de l'étape">
                                                </div>
                                                <div class="mb-3">
                                                    <textarea name="etapes[{{$index}}][description]" class="form-control"
                                                        placeholder="Décris cette étape"></textarea>
                                                </div>
                                                <button type="button" class="btn btn-danger remove-etape">Supprimer cette
                                                    étape</button>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="etape mb-3">
                                            <div class="mb-3">
                                                <input type="text" name="etapes[0][titre]" class="form-control"
                                                    placeholder="Titre de l'étape">
                                            </div>
                                            <div class="mb-3">
                                                <textarea name="etapes[0][description]" class="form-control"
                                                    placeholder="Décris cette étape"></textarea>
                                            </div>
                                            <button type="button" class="btn btn-danger remove-etape">Supprimer cette
                                                étape</button>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-success" id="add-etape">Ajouter une étape</button>
                            </div>

                            <button type="submit" id="submit-button" class="btn btn-primary w-100">Ajouter
                                l’objectif</button>
                        </form>

                        <!-- Separate form for suggesting steps -->
                        <form action="{{ route('objectifs.suggest-steps') }}" method="POST" class="mt-3" id="suggest-form"
                            onsubmit="document.getElementById('suggest-btn').innerText='Chargement...';">
                            @csrf
                            <input type="hidden" name="titre" id="suggest-titre"
                                value="{{ old('titre', session('titre')) }}">
                            <button type="submit" id="suggest-btn" class="btn btn-primary">Suggérer des étapes</button>
                        </form>
                    </div>

                    <!-- Affichage des suggestions (si erreur ou message) -->
                    @if (session('suggestion_error'))
                        <div class="alert alert-danger mt-3">{{ session('suggestion_error') }}</div>
                    @endif

                    <!-- Mindmap (right side) -->
                    <div class="col-md-6">
                        <h4 class="mt-5 title-color">🧠 Vue en carte mentale</h4>
                        <div id="jsmind_container"></div>
                    </div>
                </div>
            </div>
            
            <!-- Objectifs listés -->
            <h4 class="mt-5 title-color">📋 Mes Objectifs</h4>
            <div class="container mt-4">
                <div class="row">
                    @forelse($objectifs as $objectif)
                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100 shadow-sm {{ $objectif->getProgressColor(auth()->user()) }}">
                                            <div class="card-body d-flex flex-column">
                                                <h5 class="card-title d-flex justify-content-between align-items-center">{{ $objectif->titre }}
                                                    @php
                                                    $user = auth()->user();
                                                    $total = $objectif->etapes->count();
                                                    $completed = $objectif->etapes->whereIn('id', $user->completedEtapes->pluck('id'))->count();
                                                    @endphp
                                                    @if ($completed === 0)
                                                        <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Non commencé</span>
                                                    @elseif ($completed < $total)
                                                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> En cours</span>
                                                    @else
                                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Complété</span>
                                                    @endif
                                                </h5>
                                                <p class="card-text"><strong>Description :</strong> {{ $objectif->description }}</p>
                                                <p><strong>Type :</strong> {{ $objectif->type }}</p>
                                                <p><strong>Deadline :</strong> {{ $objectif->deadline ?? 'Non spécifiée' }}</p>
                                                <p><strong>Lieu :</strong> {{ $objectif->lieu ?? 'Non spécifié' }}</p>
                                                @if ($objectif->etapes->isNotEmpty())
                                                    <h6 class="mt-3">Étapes :</h6>
                                                    @foreach($objectif->etapes as $etape)
                                                    <div class="etape-card mb-2 p-2 rounded {{ auth()->user()->completedEtapes->contains($etape->id) ? 'bg-success text-white' : 'bg-light' }}"
                                                        data-etape-id="{{ $etape->id }}"
                                                        data-objectif-id="{{ $objectif->id }}">
                                                       <div class="d-flex justify-content-between align-items-center">
                                                           <div>
                                                               <h6 class="mb-1">{{ $etape->titre }}</h6>
                                                               <p class="mb-0 small">{{ $etape->description }}</p>
                                                           </div>
                                                           <form action="{{ route('etape.complete', $etape->id) }}" method="POST">
                                                               @csrf
                                                               <button type="submit" class="btn btn-success btn-sm">✅</button>
                                                           </form>
                                                       </div>
                                                   </div>
                                                    @endforeach
                                                @endif
                                                <form action="{{ route('objectifs.destroy', $objectif->id) }}" method="POST"
                                                    class="mt-auto">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm w-100 mt-3">Supprimer</button>
                                                </form>
                                                <button class="btn btn-warning btn-sm w-100 mt-2 edit-objectif"
                                                    data-id="{{ $objectif->id }}"
                                                    data-titre="{{ htmlspecialchars($objectif->titre, ENT_QUOTES, 'UTF-8') }}"
                                                    data-description="{{ htmlspecialchars($objectif->description, ENT_QUOTES, 'UTF-8') }}"
                                                    data-deadline="{{ $objectif->deadline }}"
                                                    data-lieu="{{ htmlspecialchars($objectif->lieu ?? '', ENT_QUOTES, 'UTF-8') }}"
                                                    data-type="{{ $objectif->type }}" data-etapes="{{ json_encode($objectif->etapes->map(function ($etape) {
                            return [
                                'titre' => htmlspecialchars($etape->titre ?? '', ENT_QUOTES, 'UTF-8'),
                                'description' => htmlspecialchars($etape->description ?? '', ENT_QUOTES, 'UTF-8')
                            ];
                        })->toArray()) }}">Modifier</button>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="col-12">
                                        <div class="alert alert-info">Aucun objectif trouvé. Commencez par en créer un !</div>
                                    </div>
                    @endforelse
                </div>
            </div> 

            <!-- Progression Section -->
    <div class="mb-4 p-4 border rounded-3" style="border: 2px solid #6f42c1; background-color: #f8f9fa;">
        <h3 class="text-center mb-4" style="font-weight: bold; color: #6f42c1;">Ta Progression</h3>
        <div class="progress-container">
            <div class="progress-bar" id="user-progress" style="width: {{ auth()->user()->getCompletionPercentage() }}%">
                {{ auth()->user()->getCompletionPercentage() }}%
            </div>
        </div>
        <h4 class="mt-4 text-center">🏆 Tes Badges</h4>
        @isset($allBadges)
            <div class="badge-container">
                @forelse($allBadges as $badge)
                    <div class="badge {{ in_array($badge['id'], $userBadges ?? []) ? 'unlocked' : 'locked' }}"
                         title="{{ $badge['description'] }}">
                        <i class="{{ $badge['icon'] }}"></i>
                        <span class="badge-name">{{ $badge['name'] }}</span>
                    </div>
                @empty
                    <p>Aucun badge disponible pour le moment</p>
                @endforelse
            </div>
        @endisset
    </div>
        </div>
        
            <!-- Carte Leaflet -->
            <h4 class="mt-5 title-color">🌍 Localisation de mes objectifs</h4>
            <div id="map"></div>

            <!-- Calendrier -->
            <h4 class="mt-5 text-center title-color">📅 Calendrier de mes objectifs</h4>
            <div id="calendar"></div>
        </div>


        @php
            $calendarEvents = [];
            foreach ($objectifs as $objectif) {
                $calendarEvents[] = [
                    'title' => $objectif->titre,
                    'start' => $objectif->deadline,
                    'description' => $objectif->description,
                    'type' => $objectif->type
                ];
            }
        @endphp
    </body>

    <script>
        // Copier le titre de #objectif-titre dans #suggest-titre
        document.getElementById('objectif-titre').addEventListener('input', function () {
            document.getElementById('suggest-titre').value = this.value;
        });

        // Étapes dynamiques
        document.getElementById('add-etape').addEventListener('click', function () {
            const index = document.querySelectorAll('.etape').length;
            const newEtape = `
                                        <div class="etape mb-3">
                                            <div class="mb-3">
                                                <input type="text" name="etapes[${index}][titre]" class="form-control" placeholder="Titre de l'étape">
                                            </div>
                                            <div class="mb-3">
                                                <textarea name="etapes[${index}][description]" class="form-control" placeholder="Décris cette étape"></textarea>
                                            </div>
                                            <button type="button" class="btn btn-danger remove-etape">Supprimer cette étape</button>
                                        </div>`;
            document.getElementById('etapes').insertAdjacentHTML('beforeend', newEtape);
        });

        document.body.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-etape')) {
                e.target.closest('.etape').remove();
            }
        });

        // jsMind
        @php
            $oid = 1;
            $mindData = [
                ["id" => "root", "isroot" => true, "topic" => "🎯 Mes Objectifs"]
            ];
            foreach (Auth::user()->objectifs as $objectif) {
                $mindData[] = [
                    "id" => "o$oid",
                    "parentid" => "root",
                    "topic" => $objectif->titre
                ];
                $oid++;
            }
        @endphp
        const mind = {
            meta: { name: "objectif_mindmap", author: "user", version: "1.0" },
            format: "node_array",
            data: @json($mindData)
        };
        const jm = new jsMind({
            container: 'jsmind_container',
            theme: 'primary',
            editable: false
        });
        jm.show(mind);

        // FullCalendar
        $(document).ready(function () {
            $('#calendar').fullCalendar({
                events: @json($calendarEvents)
            });
        });

        // Leaflet Map
        $(document).ready(function () {
            const map = L.map('map').setView([20, 0], 2);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            const objectifs = @json($objectifs);
            objectifs.forEach(obj => {
                if (obj.latitude && obj.longitude) {
                    let popupContent = `<b>${obj.titre}</b><br>${obj.lieu ?? 'No lieu'}`;
                    L.marker([obj.latitude, obj.longitude])
                        .addTo(map)
                        .bindPopup(popupContent);
                }
            });
        });
        /* document.getElementById('suggest-form').addEventListener('submit', function (e) {
     const titre = document.getElementById('objectif-titre').value.trim();
     if (titre.length < 4) {
         e.preventDefault();
         alert('Veuillez entrer un titre d’objectif d’au moins 4 caractères.');
     } else {
         document.getElementById('suggest-titre').value = titre;
     }
    }); */
        document.getElementById('suggest-btn').addEventListener('click', function (e) {
            e.preventDefault();
            const titre = document.getElementById('objectif-titre').value.trim();
            if (!titre || titre.length < 4) {
                alert("Veuillez saisir un titre d'objectif d’au moins 4 caractères.");
                return;
            }

            const button = this;
            button.disabled = true;
            button.textContent = "Chargement...";

            fetch('{{ route('objectifs.suggest-steps') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ titre: titre })
            })
                .then(response => response.json())
                .then(data => {
                    button.disabled = false;
                    button.textContent = "Suggérer des étapes";
                    if (data.success) {
                        const etapesContainer = document.getElementById('etapes');
                        etapesContainer.innerHTML = '';
                        data.steps.forEach((step, index) => {
                            const etapeDiv = document.createElement('div');
                            etapeDiv.className = 'etape mb-3';
                            etapeDiv.innerHTML = `
                                            <div class="mb-3">
                                                <input type="text" name="etapes[${index}][titre]" class="form-control" value="${step}" placeholder="Titre de l'étape">
                                            </div>
                                            <div class="mb-3">
                                                <textarea name="etapes[${index}][description]" class="form-control" placeholder="Décris cette étape"></textarea>
                                            </div>
                                            <button type="button" class="btn btn-danger remove-etape">Supprimer cette étape</button>
                                        `;
                            etapesContainer.appendChild(etapeDiv);
                        });
                        document.querySelectorAll('.remove-etape').forEach(btn => {
                            btn.addEventListener('click', function () {
                                this.closest('.etape').remove();
                            });
                        });
                    } else {
                        alert("Erreur : " + data.error);
                    }
                })
                .catch(error => {
                    button.disabled = false;
                    button.textContent = "Suggérer des étapes";
                    alert("Erreur : " + error.message);
                    console.error(error);
                });
        });

        // Handle Edit Button Click
        document.querySelectorAll('.edit-objectif').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault(); // Prevent any default behavior
                console.log('Edit button clicked for objective ID:', this.getAttribute('data-id')); // Debug
                console.log('Data attributes:', {
                    id: this.getAttribute('data-id'),
                    titre: this.getAttribute('data-titre'),
                    description: this.getAttribute('data-description'),
                    deadline: this.getAttribute('data-deadline'),
                    lieu: this.getAttribute('data-lieu'),
                    type: this.getAttribute('data-type'),
                    etapes: this.getAttribute('data-etapes')
                }); // Debug

                // Get objective data from button attributes
                const id = this.getAttribute('data-id');
                const titre = this.getAttribute('data-titre') || '';
                const description = this.getAttribute('data-description') || '';
                const deadline = this.getAttribute('data-deadline') || '';
                const lieu = this.getAttribute('data-lieu') || '';
                const type = this.getAttribute('data-type') || '';
                let etapes;
                try {
                    etapes = JSON.parse(this.getAttribute('data-etapes')) || [];
                } catch (e) {
                    console.error('Error parsing data-etapes:', e);
                    etapes = [];
                }

                // Fill form with objective data
                const form = document.getElementById('objectif-form');
                const titreInput = document.getElementById('objectif-titre');
                const descriptionTextarea = form.querySelector('textarea[name="description"]');
                const deadlineInput = form.querySelector('input[name="deadline"]');
                const lieuInput = form.querySelector('input[name="lieu"]');
                const typeSelect = form.querySelector('select[name="type"]');
                const submitButton = document.getElementById('submit-button');

                console.log('Form elements:', {
                    titreInput: !!titreInput,
                    descriptionTextarea: !!descriptionTextarea,
                    deadlineInput: !!deadlineInput,
                    lieuInput: !!lieuInput,
                    typeSelect: !!typeSelect,
                    submitButton: !!submitButton
                }); // Debug

                if (titreInput) titreInput.value = titre;
                if (descriptionTextarea) descriptionTextarea.value = description;
                if (deadlineInput) deadlineInput.value = deadline;
                if (lieuInput) lieuInput.value = lieu;
                if (typeSelect) typeSelect.value = type;
                if (form.querySelector('#objectif-id')) form.querySelector('#objectif-id').value = id;

                if (!titreInput || !descriptionTextarea || !deadlineInput || !lieuInput || !typeSelect || !submitButton) {
                    console.error('One or more form elements not found');
                }

                // Clear existing steps
                const etapesContainer = document.getElementById('etapes');
                etapesContainer.innerHTML = '';

                // Add steps from objective
                etapes.forEach((etape, index) => {
                    const etapeDiv = document.createElement('div');
                    etapeDiv.className = 'etape mb-3';
                    etapeDiv.innerHTML = `
                                        <div class="mb-3">
                                            <input type="text" name="etapes[${index}][titre]" class="form-control"
                                                value="${etape.titre || ''}" placeholder="Titre de l'étape">
                                        </div>
                                        <div class="mb-3">
                                            <textarea name="etapes[${index}][description]" class="form-control"
                                                placeholder="Décris cette étape">${etape.description || ''}</textarea>
                                        </div>
                                        <button type="button" class="btn btn-danger remove-etape">Supprimer cette étape</button>
                                    `;
                    etapesContainer.appendChild(etapeDiv);
                });

                // Change form action to update route
                form.action = "{{ route('objectifs.update', '__ID__') }}".replace('__ID__', id);
                // Remove any existing _method input
                const existingMethodInput = form.querySelector('input[name="_method"]');
                if (existingMethodInput) existingMethodInput.remove();
                // Add PUT method
                form.insertAdjacentHTML('afterbegin', '<input type="hidden" name="_method" value="PUT">');

                // Change button text
                if (submitButton) {
                    submitButton.textContent = 'Appliquer les modifications';
                    console.log('Button text changed to: Appliquer les modifications'); // Debug
                } else {
                    console.error('Submit button not found');
                    alert('Erreur: Bouton de soumission introuvable');
                }

                // Update suggest form title
                const suggestTitre = document.getElementById('suggest-titre');
                if (suggestTitre) suggestTitre.value = titre;
                // Scroll to the top of the form
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                console.log('Form action set to:', form.action); // Debug
            });
        });

        document.getElementById('objectif-form').addEventListener('submit', function (event) {
            event.preventDefault();
            console.log('Form submitted with action:', this.action, 'Method:', this.querySelector('input[name="_method"]') ? 'PUT' : 'POST');

            // Create FormData
            const formData = new FormData(this);

            // Debug: Log FormData contents
            console.log('FormData contents:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: "${value}"`);
            }

            // Debug: Log specific fields
            const titre = formData.get('titre');
            const description = formData.get('description');
            const type = formData.get('type');
            console.log('Required fields check:', {
                titre: titre || 'MISSING',
                description: description || 'MISSING',
                type: type || 'MISSING'
            });

            // Validate required fields
            if (!titre || !description || !type) {
                alert('Erreur : Veuillez remplir tous les champs obligatoires (Titre, Description, Type).');
                return;
            }
            if (!['Études', 'Sport', 'Lecture', 'Projets', 'Santé'].includes(type)) {
                alert('Erreur : Veuillez sélectionner un type valide (Études, Sport, Lecture, Projets, Santé).');
                return;
            }

            // Submit form via fetch
            fetch(this.action, {
                method: 'POST', // Use POST since _method=PUT handles the spoofing
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json'
                    // Note: Do NOT set Content-Type; FormData sets it automatically to multipart/form-data
                }
            }).then(response => {
                console.log('Fetch response status:', response.status, 'OK:', response.ok);
                if (!response.ok) {
                    return response.json().then(error => {
                        console.error('Server error response:', error);
                        throw new Error(`HTTP error ${response.status}: ${JSON.stringify(error)}`);
                    });
                }
                return response.json();
            }).then(data => {
                console.log('Fetch response data:', data);
                if (data && data.success) {
                    // Reset form
                    this.reset();
                    this.action = "{{ route('objectifs.store') }}";
                    const methodInput = this.querySelector('input[name="_method"]');
                    if (methodInput) methodInput.remove();
                    const submitButton = document.getElementById('submit-button');
                    if (submitButton) submitButton.textContent = 'Ajouter l’objectif';

                    // Reset steps to one empty step
                    const etapesContainer = document.getElementById('etapes');
                    etapesContainer.innerHTML = `
                            <div class="etape mb-3">
                                <div class="mb-3">
                                    <input type="text" name="etapes[0][titre]" class="form-control" placeholder="Titre de l'étape">
                                </div>
                                <div class="mb-3">
                                    <textarea name="etapes[0][description]" class="form-control" placeholder="Décris cette étape"></textarea>
                                </div>
                                <button type="button" class="btn btn-danger remove-etape">Supprimer cette étape</button>
                            </div>
                        `;

                    // Reset suggest form title
                    const suggestTitre = document.getElementById('suggest-titre');
                    if (suggestTitre) suggestTitre.value = '';

                    // Reload page
                    alert('Objectif ajouté avec succès !');
                    window.location.reload();
                } else {
                    alert('Erreur lors de la sauvegarde : ' + (data?.error || 'Réponse invalide'));
                }
            }).catch(error => {
                console.error('Fetch error:', error.message, 'URL:', this.action);
                alert('Une erreur est survenue : ' + error.message);
            });
        });

        
    </script>
    <script src="{{ asset('js/goal-progress.js') }}"></script>

@endsection