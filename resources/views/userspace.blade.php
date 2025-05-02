@extends('layouts.app')

@section('content')

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

    .form-control, .form-select {
        border-radius: 10px;
        border: 2px solid #ffa07a;
    }

    .form-control:focus, .form-select:focus {
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

    /* ✨ Style du calendrier simplifié ✨ */
#calendar {
    width: 90%;
    max-width: 700px;
    margin: 30px auto;
    background-color: #fff5f0; /* Original background */
    border: 2px solid #ffd4c4; /* Border for the entire calendar */
    border-radius: 12px;
    padding: 15px; /* Keep some padding */
}

/* Enlève la grande bordure autour du calendrier */
.fc {
    border: none;
}
</style>

<!-- Intégration jsMind -->
<link type="text/css" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsmind/style/jsmind.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jsmind/js/jsmind.js"></script>

<!-- Intégration LeafletJS pour la carte -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>


<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- moment.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<!-- fullCalendar v3 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>

<div class="container">
    <h2>🎯 Bienvenue dans ton espace personnel, {{ Auth::user()->name }} !</h2>

    <!-- Large rectangle for adding an objectif -->
    <div class="mb-4 p-4 border rounded-3" style="border: 2px solid #007bff;">
        <h3 class="text-center mb-4" style="font-weight: bold; color: #007bff;">Ajouter un Objectif</h3>

        <!-- Row for form and mindmap -->
        <div class="row">
            <!-- Formulary for objective (left side) -->
            <div class="col-md-6">
                <form action="{{ route('objectifs.store') }}" method="POST" class="mb-4">
                    @csrf
                    <div class="mb-3">
                        <input type="text" name="titre" class="form-control" placeholder="Titre de l’objectif" required>
                    </div>
                    <div class="mb-3">
                        <textarea name="description" class="form-control" placeholder="Décris ton objectif" required></textarea>
                    </div>
                    <div class="mb-3">
                        <input type="date" name="deadline" class="form-control">
                    </div>
                    <div class="mb-3">
                        <input type="text" name="lieu" class="form-control" placeholder="Lieu (facultatif)">
                    </div>
                    <div class="mb-3">
                        <select name="type" class="form-select" required>
                            <option value="">Type</option>
                            <option value="Études">Études</option>
                            <option value="Sport">Sport</option>
                            <option value="Lecture">Lecture</option>
                            <option value="Projets">Projets</option>
                            <option value="Santé">Santé</option>
                        </select>
                    </div>

                    <!-- Étapes dynamiques inside a rectangle -->
                    <div class="mb-4 p-4 border rounded-3" style="border: 2px solid #28a745; background-color: #f9f9f9;">
                        <h4 class="text-center" style="color: #28a745;">Étapes à suivre</h4>
                        <div id="etapes">
                            <div class="etape mb-3">
                                <div class="mb-3">
                                    <input type="text" name="etapes[0][titre]" class="form-control" placeholder="Titre de l'étape" required>
                                </div>
                                <div class="mb-3">
                                    <textarea name="etapes[0][description]" class="form-control" placeholder="Décris cette étape" required></textarea>
                                </div>
                                <button type="button" class="btn btn-danger remove-etape">Supprimer cette étape</button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-success" id="add-etape">Ajouter une étape</button>
                    </div>

                    <button class="btn btn-primary w-100">Ajouter l’objectif</button>
                </form>
            </div>

            <!-- Mindmap (right side) -->
            <div class="col-md-6">
                <h4 class="mt-5">🧠 Vue en carte mentale</h4>
                <div id="jsmind_container"></div>
            </div>
        </div>
    </div>
</div>



    <!-- Objectifs listés -->
    <h4 class="mt-5">📋 Mes Objectifs</h4>
    <div class="container mt-4">
        <div class="row">
            @foreach($objectifs as $objectif)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $objectif->titre }}</h5>
                            <p><strong>Description :</strong> {{ $objectif->description }}</p>
                            <p><strong>Type :</strong> {{ $objectif->type }}</p>
                            <p><strong>Deadline :</strong> {{ $objectif->deadline }}</p>
                            <p><strong>Lieu :</strong> {{ $objectif->lieu }}</p>

                            <h6 class="mt-3">Étapes :</h6>
                            @foreach($objectif->etapes as $etape)
                                <div class="card mb-2 border-0 bg-light">
                                    <div class="card-body p-2">
                                        <h6 class="mb-1">{{ $etape->titre }}</h6>
                                        <p class="mb-0">{{ $etape->description }}</p>
                                    </div>
                                </div>
                            @endforeach

                            <form action="{{ route('objectifs.destroy', $objectif->id) }}" method="POST" class="mt-auto">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm w-100 mt-3">Supprimer</button>
                            </form>
                            <a href="{{ route('objectifs.update', $objectif->id) }}" class="btn btn-warning btn-sm w-100 mt-2">Modifier</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Carte Leaflet -->
    <h4 class="mt-5">🌍 Localisation de mes objectifs</h4>
    <div id="map" style="height: 400px; width: 100%;"></div>
</div>

<!-- Calendrier -->
<h4 class="mt-5 text-center">📅 Calendrier de mes objectifs</h4>
<div id="calendar"></div>

@php
    // Mindmap data
    $oid = 1;
    $mindData = [
        [ "id" => "root", "isroot" => true, "topic" => "🎯 Mes Objectifs" ]
    ];
    foreach (Auth::user()->objectifs as $objectif) {
        $mindData[] = [
            "id" => "o$oid",
            "parentid" => "root",
            "topic" => $objectif->titre
        ];
        $oid++;
    }

    // Calendar events data
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

<script>
    // Étapes dynamiques
    document.getElementById('add-etape').addEventListener('click', function () {
        const index = document.querySelectorAll('.etape').length;
        const newEtape = `
            <div class="etape">
                <div class="mb-3">
                    <input type="text" name="etapes[${index}][titre]" class="form-control" placeholder="Titre de l'étape" required>
                </div>
                <div class="mb-3">
                    <textarea name="etapes[${index}][description]" class="form-control" placeholder="Décris cette étape" required></textarea>
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
    const mind = {
        meta: { name: "objectif_mindmap", author: "user", version: "1.0" },
        format: "node_array",
        data: @json($mindData)
    };
    const jm = new jsMind({ container: 'jsmind_container', theme: 'primary' });
    jm.show(mind);

    // FullCalendar
    $(document).ready(function () {
        $('#calendar').fullCalendar({
            events: @json($calendarEvents)
        });
    });

    // Leaflet Map with Markers
$(document).ready(function () {
    // Initialize Leaflet map with default view (Tunis, Tunisia)
    const map = L.map('map').setView([36.8028, 10.1797], 13);

    // Add OpenStreetMap tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Fetch objectives from API
    $.get('/api/objectifs', function (response) {
        if (response.status === 'success') {
            response.data.forEach(function (objectif) {
                // Check for valid latitude and longitude
                if (objectif.latitude && objectif.longitude) {
                    // Add marker with popup showing the objective title
                    L.marker([objectif.latitude, objectif.longitude])
                        .addTo(map)
                        .bindPopup(objectif.titre);
                } else {
                    console.log('No coordinates for objective: ' + objectif.titre);
                }
            });
        } else {
            console.error('Failed to fetch objectives: ', response);
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.error('AJAX error: ' + textStatus + ': ' + errorThrown);
    });
});

// Initialiser la carte
var map = L.map('map').setView([20, 0], 2); // vue mondiale

// Ajouter le fond de carte OpenStreetMap
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Données PHP des objectifs avec lieux
const objectifs = @json($objectifs);

// Géocoder les lieux (simple version avec fetch à Nominatim)
objectifs.forEach(obj => {
    if (obj.lieu) {
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(obj.lieu)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    const lat = data[0].lat;
                    const lon = data[0].lon;
                    L.marker([lat, lon]).addTo(map)
                        .bindPopup(`<strong>${obj.titre}</strong><br>${obj.lieu}`);
                }
            })
            .catch(error => console.error('Erreur de géocodage :', error));
    }
});
</script>
