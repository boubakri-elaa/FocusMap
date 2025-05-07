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

    .progress-wrapper {
    width: 100%;
    background-color: #e9ecef;
    border-radius: 30px;
    overflow: hidden;
    height: 30px;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
}

.progress-bar-modern {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, #6f42c1, #b583f2);
    color: white;
    font-weight: bold;
    text-align: center;
    line-height: 30px;
    transition: width 1.5s ease-in-out;
}

#progress-text {
    position: relative;
    z-index: 2;
}
/* Custom light green for card when all steps are completed */
.card.bg-success {
    background-color: #e6f4ea !important; /* Light, soft green */
    color: #212529 !important; /* Dark text for readability */
}

/* Ensure progress bar keeps the default Bootstrap green */
.progress-bar.bg-success {
    background-color: #28a745 !important; /* Default Bootstrap green */
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

            <!-- Formularire d'ajout d'objectifs -->
            <div class="mb-4 p-4 border rounded-3" style="border: 2px solid #007bff;">
                <h3 class="text-center mb-4" style="font-weight: bold; color: #007bff;">Ajouter un Objectif</h3>

                <div class="row">
                    <!-- Ajout d'Objectifs (à gauche)-->
                    <div class="col-md-6">
                        <!-- Informations Objectif -->
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

                            <!-- Ajout d'étapes dynamique -->
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
                            <div class="mb-3">
                                <label for="visibility" class="form-label">Visibilité de l’objectif</label>
                                <select name="visibility" class="form-select" required>
                                    <option value="private" {{ old('visibility') == 'private' ? 'selected' : '' }}>Privé</option>
                                    <option value="friends" {{ old('visibility') == 'friends' ? 'selected' : '' }}>Amis</option>
                                    <option value="public" {{ old('visibility') == 'public' ? 'selected' : '' }}>Public</option>
                                </select>
                            </div>
                            <button type="submit" id="submit-button" class="btn btn-primary w-100">Ajouter
                                l’objectif</button>
                        </form>

                        <!-- Integration de l'API Gemini pour la suggestion d'étapes -->
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

                    <!-- Mindmap (à droite) -->
                    <div class="col-md-6">
                        <h4 class="mt-5 title-color">🧠 Vue en carte mentale</h4>
                        <div id="jsmind_container"></div>
                    </div>
                </div>
            </div>
            
            <!-- Objectifs listés -->
            <h4 class="mt-5 title-color">📋 Mes Objectifs</h4>
            <div class="container mt-4" id="objectifs">
                <div class="row">
    @forelse($objectifs as $objectif)
        @php
            $user = auth()->user();
            $total = $objectif->etapes ? $objectif->etapes->count() : 0;
            $completed = $objectif->etapes && $user->completedEtapes ? $objectif->etapes->whereIn('id', $user->completedEtapes->pluck('id'))->count() : 0;
            $objectifProgress = $total > 0 ? ($completed / $total) * 100 : 0;
        @endphp
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm text-dark {{ $objectifProgress == 100 ? 'bg-success' : 'bg-light' }}">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title d-flex justify-content-between align-items-center">
                        {{ $objectif->titre }}
                        @if ($completed === 0)
                            <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Non commencé</span>
                        @elseif ($completed < $total)
                            <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> En cours</span>
                        @else
                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Complété</span>
                        @endif
                    </h5>
                    <div class="progress mt-2" style="height: 10px;">
                        <div class="progress-bar {{ $objectifProgress == 100 ? 'bg-success' : ($objectifProgress > 0 ? 'bg-warning' : 'bg-danger') }}"
                             role="progressbar"
                             style="width: {{ $objectifProgress }}%;"
                             aria-valuenow="{{ $objectifProgress }}"
                             aria-valuemin="0"
                             aria-valuemax="100"></div>
                    </div>
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
                                    <form action="{{ route('etape.complete', $etape->id) }}" method="POST" class="complete-etape-form">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">✅</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    @endif
                    <!-- Modification/suppression carte -->
                    <form action="{{ route('objectifs.destroy', $objectif->id) }}" method="POST" class="mt-auto">
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
                            data-type="{{ $objectif->type }}"
                            data-etapes="{{ json_encode($objectif->etapes->map(function ($etape) {
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

            <!-- Section Progression gloable -->
    <div class="mb-4 p-4 border rounded-3" style="border: 2px solid #6f42c1; background-color: #f8f9fa;">
        <h3 class="text-center mb-4" style="font-weight: bold; color: #6f42c1;">Ta Progression</h3>
        <div class="progress-container">
            <div class="progress-wrapper">
                <div class="progress-bar-modern" id="user-progress-bar">
                    <span id="progress-text">{{ auth()->user()->getCompletionPercentage() }}%</span>
                </div>
            </div>
        </div>
                    <!-- Section Badges -->
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
        
            <!-- Carte du monde -->
            <h4 class="mt-5 title-color">🌍 Localisation de mes objectifs</h4>
            <div id="map"></div>

            <!-- Calendrier -->
            <h4 class="mt-5 text-center title-color">📅 Calendrier de mes objectifs</h4>
            <div id="calendar"></div>
        </div>


        <!-- Objectifs partagés -->
<div class="shared-objectifs mt-5 d-flex justify-content-center">
    <div class="p-4 shadow border border-3 rounded-4" style="border-color: #ff6f61; max-width: 1100px; width: 100%;">
        <h3 class="text-center mb-4" style="color: #ff6f61;">🎯 Objectifs Partagés</h3>
        <div id="objectif-cards" class="d-flex flex-wrap justify-content-center gap-4"></div>
    </div>
</div>
<style>
    .objectif-card {
        width: 320px;
        height: 220px;
        perspective: 1000px;
    }

    .card-inner {
        position: relative;
        width: 100%;
        height: 100%;
        transition: transform 0.6s;
        transform-style: preserve-3d;
    }

    .objectif-card:hover .card-inner {
        transform: rotateY(180deg);
    }

    .card-front, .card-back {
        position: absolute;
        width: 100%;
        height: 100%;
        backface-visibility: hidden;
        border-radius: 15px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .card-front {
        background-color: #fdfdfd;
    }

    .card-back {
        background-color: #fbeeee;
        transform: rotateY(180deg);
        overflow-y: auto;
        font-size: 0.95rem;
    }

    .like-btn {
        cursor: pointer;
        font-size: 1.5rem;
        margin-bottom: 10px;
        transition: transform 0.2s;
    }

    .like-btn:hover {
        transform: scale(1.2);
    }

    .like-btn.liked {
        color: #dc3545 !important;
    }

    .comment-form {
        width: 100%;
    }

    .comment-input {
        width: 100%;
        height: 35px;
        font-size: 0.85rem;
        padding: 6px 8px;
        margin-bottom: 5px;
    }

    .comment-btn {
        font-size: 0.85rem;
        padding: 6px 12px;
    }

    .comment-list {
        width: 100%;
        max-height: 80px;
        overflow-y: auto;
    }

    .comment-item {
        font-size: 0.8rem;
        margin-bottom: 6px;
        word-wrap: break-word;
    }
</style>

        <!-- Section de Gestion d'amis -->
<div id="amis" class="friends-section mt-5 d-flex justify-content-center">
    <div class="card shadow-lg rounded-4 p-4" style="max-width: 500px; width: 100%; border: none;">
        <h4 class="text-center mb-4" style="color: #ff6f61;">Gérer les Amis</h4>
        <form id="add-friend-form" class="mb-3">
            <div class="input-group">
                <input type="number" class="form-control" name="friend_id" placeholder="ID de l'utilisateur" required>
                <button type="submit" class="btn btn-coral">Ajouter</button>
            </div>
        </form>
        <ul id="friends-list" class="list-group">
            @foreach (auth()->user()->friends as $friend)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>{{ $friend->name }}</span>
                    <button class="btn btn-sm btn-coral" onclick="removeFriend({{ $friend->id }})">
                        Supprimer
                    </button>
                </li>
            @endforeach
        </ul>
    </div>
    <style>.btn-coral {
        background-color: #ff6f61;
        color: white;
        border: none;
    }
    .btn-coral:hover {
        background-color: #e85b50;
    }
</style>    
</div>


<script>

    //Ajout d'amis via ID
    document.getElementById('add-friend-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('/friends/add', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    })
    .then(data => {
        alert(data.message);
        // Add friend to list dynamically
        const friendsList = document.getElementById('friends-list');
        const friendId = formData.get('friend_id');
        fetch(`/users/${friendId}`)
            .then(res => res.json())
            .then(friend => {
                friendsList.innerHTML += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        ${friend.name}
                        <button class="btn btn-danger btn-sm" onclick="removeFriend(${friend.id})">Supprimer</button>
                    </li>`;
            });
        this.reset();
    })
    .catch(error => alert('Erreur : ' + (error.message || 'Impossible d\'ajouter l\'ami')));
});
    </script>

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

        //Ajot  d'Étapes dynamique
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

        //Bouton Modification de l'Objectif
        document.querySelectorAll('.edit-objectif').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault(); // Prevent any default behavior
              
                // Récupérer les informations de l'objectif
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

                //Remplir le formulaire avec ces informations
                const form = document.getElementById('objectif-form');
                const titreInput = document.getElementById('objectif-titre');
                const descriptionTextarea = form.querySelector('textarea[name="description"]');
                const deadlineInput = form.querySelector('input[name="deadline"]');
                const lieuInput = form.querySelector('input[name="lieu"]');
                const typeSelect = form.querySelector('select[name="type"]');
                const submitButton = document.getElementById('submit-button');


                if (titreInput) titreInput.value = titre;
                if (descriptionTextarea) descriptionTextarea.value = description;
                if (deadlineInput) deadlineInput.value = deadline;
                if (lieuInput) lieuInput.value = lieu;
                if (typeSelect) typeSelect.value = type;
                if (form.querySelector('#objectif-id')) form.querySelector('#objectif-id').value = id;

                if (!titreInput || !descriptionTextarea || !deadlineInput || !lieuInput || !typeSelect || !submitButton) {
                    console.error('One or more form elements not found');
                }

                // Vider les champs d'étapes
                const etapesContainer = document.getElementById('etapes');
                etapesContainer.innerHTML = '';

                // Ajouter étapes d'un objectif
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
            });
        });

        document.getElementById('objectif-form').addEventListener('submit', function (event) {
            event.preventDefault();

            // Create FormData
            const formData = new FormData(this);
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: "${value}"`);
            }

            // Debug: Log specific fields
            const titre = formData.get('titre');
            const description = formData.get('description');
            const type = formData.get('type');

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
    <!--Ticking steps after completion-->
        <script src="{{ asset('js/goal-progress.js') }}"></script>
    <!--Global progress-->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const progressBar = document.getElementById('user-progress-bar');
            const percentage = {{ auth()->user()->getCompletionPercentage() }};
            updateGlobalProgressBar(percentage);
        });
        function updateGlobalProgressBar(percentage) {
    const progressBar = document.getElementById('user-progress-bar');
    const progressText = document.getElementById('progress-text');
    if (progressBar && progressText) {
        progressBar.style.width = percentage + '%';
        progressText.textContent = percentage + '%';
    }
}

    </script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.complete-etape-form');
    if (forms.length === 0) {
        console.warn('No forms with class .complete-etape-form found. Check HTML.');
    }

    forms.forEach((form, index) => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const etapeCard = this.closest('.etape-card');
            const etapeId = etapeCard.dataset.etapeId;
            const objectifId = etapeCard.dataset.objectifId;
            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (data.completed) {
                        etapeCard.classList.add('bg-success', 'text-white');
                        etapeCard.classList.remove('bg-light');
                    } else {
                    }

                    const card = etapeCard.closest('.card');
                    const progressBar = card.querySelector('.progress-bar');
                    const badge = card.querySelector('.badge');
                    const totalEtapes = card.querySelectorAll('.etape-card').length;
                    const completedEtapes = card.querySelectorAll('.etape-card.bg-success').length;
                    const objectifProgress = totalEtapes > 0 ? (completedEtapes / totalEtapes) * 100 : 0;

                    // Update progress bar
                    progressBar.style.width = `${objectifProgress}%`;
                    progressBar.setAttribute('aria-valuenow', objectifProgress);
                    progressBar.classList.remove('bg-success', 'bg-warning', 'bg-danger');
                    if (objectifProgress === 100) {
                        progressBar.classList.add('bg-success');
                    } else if (objectifProgress > 0) {
                        progressBar.classList.add('bg-warning');
                    } else {
                        progressBar.classList.add('bg-danger');
                    }

                    // Update badge
                    badge.classList.remove('bg-success', 'bg-warning', 'bg-danger', 'text-dark');
                    if (objectifProgress === 100) {
                        badge.classList.add('badge', 'bg-success');
                        badge.innerHTML = '<i class="bi bi-check-circle"></i> Complété';
                    } else if (objectifProgress > 0) {
                        badge.classList.add('badge', 'bg-warning', 'text-dark');
                        badge.innerHTML = '<i class="bi bi-hourglass-split"></i> En cours';
                    } else {
                        badge.classList.add('badge', 'bg-danger');
                        badge.innerHTML = '<i class="bi bi-x-circle"></i> Non commencé';
                    }

                    // Update card
                    card.classList.remove('bg-success', 'bg-warning', 'bg-danger', 'text-white', 'text-dark');
                    card.classList.add('card', 'h-100', 'shadow-sm', 'text-dark');
                    if (objectifProgress === 100) {
                        card.classList.add('bg-success');
                    } else {
                        card.classList.add('bg-light');
                    }

                    updateGlobalProgressBar(data.user_completion);
                    location.reload();

                } else {
                    alert('Erreur : ' + data.message);
                    console.error('Server error:', data.message);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Une erreur s’est produite : ' + error.message);
            });
        });
    });

    // Fallback for button clicks
    document.addEventListener('click', function (e) {
        const button = e.target.closest('.complete-etape-form button[type="submit"]');
        if (button) {
            e.preventDefault();
            const form = button.closest('form');
            form.dispatchEvent(new Event('submit', { cancelable: true }));
        }
    });
});

//flipping cards for shared objectives
// Fetch shared objectives
fetch('/objectifs/shared', {
    headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
    }
})
.then(response => response.json())
.then(objectifs => {
    const cardsContainer = document.getElementById('objectif-cards');
    objectifs.forEach(obj => {
        const card = document.createElement('div');
        card.className = 'objectif-card';
        card.innerHTML = `
            <div class="card-inner">
                <div class="card-front">
                    <h5>${obj.titre}</h5>
                    <p>Par : ${obj.user.name}</p>
                </div>
                <div class="card-back">
                    <span class="like-btn" onclick="toggleLike(this)">❤️</span>
                    <form class="comment-form" onsubmit="submitComment(event, ${obj.id}, this)">
                        <input type="text" class="comment-input" name="content" placeholder="Ajouter un commentaire" required>
                        <button type="submit" class="btn btn-primary btn-sm comment-btn">Envoyer</button>
                    </form>
                    <div class="comment-list" id="comments-${obj.id}"></div>
                </div>
            </div>`;
        cardsContainer.appendChild(card);
        // Fetch comments for this objective
        fetchComments(obj.id);
    });
})
.catch(error => console.error('Erreur fetching objectives:', error));

// Toggle like button color and show alert
function toggleLike(btn) {
    btn.classList.toggle('liked');
    if (btn.classList.contains('liked')) {
        alert('Objectif aimé');
    } else {
        alert('Like supprimé');
    }
}

// Fetch comments for an objective
function fetchComments(objectifId) {
    fetch(`/objectifs/${objectifId}/comments`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(comments => {
        const commentList = document.getElementById(`comments-${objectifId}`);
        commentList.innerHTML = '';
        comments.forEach(comment => {
            commentList.innerHTML += `
                <div class="comment-item">
                    <strong>${comment.user.name}</strong>: ${comment.content}
                    <small>(${comment.created_at})</small>
                </div>`;
        });
    })
    .catch(error => console.error(`Erreur fetching comments for objectif ${objectifId}:`, error));
}

// Submit a comment
function submitComment(event, objectifId, form) {
    event.preventDefault();
    const formData = new FormData(form);
    fetch(`/objectifs/${objectifId}/comments`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    })
    .then(data => {
        alert(data.message);
        fetchComments(objectifId); // Refresh comments
        form.reset();
    })
    .catch(error => {
        console.error('Erreur adding comment:', error);
        alert('Erreur : ' + (error.message || 'Impossible d\'ajouter le commentaire'));
    });
}
</script>
    
@endsection