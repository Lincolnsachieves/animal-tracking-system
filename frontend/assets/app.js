const API_BASE_URL = 'http://localhost:8000/api';

const animalForm = document.getElementById('animalForm');
const locationForm = document.getElementById('locationForm');
const animalTableBody = document.getElementById('animalTableBody');
const animalSelect = document.getElementById('animal_id');
const refreshBtn = document.getElementById('refreshBtn');
const totalAnimalsEl = document.getElementById('totalAnimals');
const lastActiveEl = document.getElementById('lastActive');
const trackedOwnersEl = document.getElementById('trackedOwners');

const animalMessage = document.getElementById('animalMessage');
const locationMessage = document.getElementById('locationMessage');

let map;
let markersLayer;

function initMap() {
    map = L.map('map').setView([-0.6072, 30.6582], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    markersLayer = L.layerGroup().addTo(map);
}

function showMessage(element, message, type = 'success') {
    element.textContent = message;
    element.className = `message ${type}`;
    setTimeout(() => {
        element.textContent = '';
        element.className = 'message';
    }, 3000);
}

async function fetchAnimals() {
    const response = await fetch(`${API_BASE_URL}/animals.php`);
    const result = await response.json();
    if (!result.success) throw new Error(result.message || 'Failed to fetch animals.');
    return result.data;
}

function renderAnimalTable(animals) {
    animalTableBody.innerHTML = '';
    animalSelect.innerHTML = '<option value="">Select Animal</option>';

    const owners = new Set();
    let lastActive = '-';

    animals.forEach((animal) => {
        owners.add(animal.owner_name);
        if (animal.last_seen && (lastActive === '-' || new Date(animal.last_seen) > new Date(lastActive))) {
            lastActive = animal.last_seen;
        }

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${animal.id}</td>
            <td>${animal.tag_number}</td>
            <td>${animal.name}</td>
            <td>${animal.species}</td>
            <td>${animal.owner_name}</td>
            <td>${animal.last_status || 'No update'}</td>
            <td>${animal.last_seen || 'No data'}</td>
        `;
        animalTableBody.appendChild(row);

        const option = document.createElement('option');
        option.value = animal.id;
        option.textContent = `${animal.name} (${animal.tag_number})`;
        animalSelect.appendChild(option);
    });

    totalAnimalsEl.textContent = animals.length;
    trackedOwnersEl.textContent = owners.size;
    lastActiveEl.textContent = lastActive === '-' ? '-' : formatDate(lastActive);
}

function renderMap(animals) {
    markersLayer.clearLayers();
    const validAnimals = animals.filter(a => a.last_latitude && a.last_longitude);

    validAnimals.forEach((animal) => {
        const marker = L.marker([Number(animal.last_latitude), Number(animal.last_longitude)]);
        marker.bindPopup(`
            <strong>${animal.name}</strong><br>
            Tag: ${animal.tag_number}<br>
            Species: ${animal.species}<br>
            Status: ${animal.last_status || 'Unknown'}<br>
            Last seen: ${animal.last_seen || 'No data'}
        `);
        marker.addTo(markersLayer);
    });

    if (validAnimals.length > 0) {
        const bounds = L.latLngBounds(validAnimals.map(a => [Number(a.last_latitude), Number(a.last_longitude)]));
        map.fitBounds(bounds, { padding: [30, 30] });
    }
}

function formatDate(value) {
    const date = new Date(value);
    return date.toLocaleString();
}

async function loadDashboard() {
    try {
        const animals = await fetchAnimals();
        renderAnimalTable(animals);
        renderMap(animals);
    } catch (error) {
        console.error(error);
    }
}

animalForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    const payload = {
        tag_number: document.getElementById('tag_number').value,
        name: document.getElementById('name').value,
        species: document.getElementById('species').value,
        breed: document.getElementById('breed').value,
        sex: document.getElementById('sex').value,
        date_of_birth: document.getElementById('date_of_birth').value,
        owner_name: document.getElementById('owner_name').value,
    };

    try {
        const response = await fetch(`${API_BASE_URL}/animals.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message || 'Failed to save animal.');
        showMessage(animalMessage, result.message, 'success');
        animalForm.reset();
        loadDashboard();
    } catch (error) {
        showMessage(animalMessage, error.message, 'error');
    }
});

locationForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    const payload = {
        animal_id: document.getElementById('animal_id').value,
        latitude: document.getElementById('latitude').value,
        longitude: document.getElementById('longitude').value,
        status: document.getElementById('status').value,
    };

    try {
        const response = await fetch(`${API_BASE_URL}/locations.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message || 'Failed to save location.');
        showMessage(locationMessage, result.message, 'success');
        locationForm.reset();
        loadDashboard();
    } catch (error) {
        showMessage(locationMessage, error.message, 'error');
    }
});

refreshBtn.addEventListener('click', loadDashboard);

initMap();
loadDashboard();
