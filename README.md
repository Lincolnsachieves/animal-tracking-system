# Animal Tracking System MVP

A simple starter project for tracking animals individually.

## Stack
- Frontend: HTML, CSS, JavaScript, Leaflet map
- Backend: PHP (PDO)
- Database: MySQL

## Features in this MVP
- Register animals
- List all animals
- Add animal locations
- View latest location on a map
- Simple dashboard cards

## Project Structure
```
animal-tracking-system/
├── backend/
│   ├── api/
│   │   ├── animals.php
│   │   └── locations.php
│   ├── config/
│   │   └── database.php
│   └── db/
│       └── schema.sql
├── frontend/
│   ├── assets/
│   │   ├── app.js
│   │   └── style.css
│   └── index.html
└── README.md
```

## 1) Create the database
Import `backend/db/schema.sql` into MySQL.

## 2) Update DB settings
Edit `backend/config/database.php`.

Default values:
- host: localhost
- dbname: animal_tracking
- username: root
- password: ""

## 3) Run locally
### Backend (XAMPP / WAMP / Laragon / PHP server)
Place the `backend` folder inside your web server root, or run:
```bash
php -S localhost:8000 -t backend
```

### Frontend
Open `frontend/index.html` in your browser, or serve it with a simple server.

If the backend runs on another port/domain, edit the `API_BASE_URL` in `frontend/assets/app.js`.

## API Endpoints
### Animals
- `GET /api/animals.php` → list animals
- `POST /api/animals.php` → create animal

### Locations
- `GET /api/locations.php?animal_id=1` → list animal locations
- `POST /api/locations.php` → create location record

## Example POST body for animal
```json
{
  "tag_number": "UG-COW-001",
  "name": "Amina",
  "species": "Cow",
  "breed": "Ankole",
  "sex": "Female",
  "date_of_birth": "2023-02-12",
  "owner_name": "Adrine Farm"
}
```

## Example POST body for location
```json
{
  "animal_id": 1,
  "latitude": -0.6072,
  "longitude": 30.6582,
  "status": "Moving"
}
```

## Startup idea for next version
- Login/authentication
- SMS alerts
- Geofencing
- Animal health records
- Ear-tag device integration
- Admin dashboard
- Mobile app
