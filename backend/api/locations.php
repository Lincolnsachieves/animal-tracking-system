<?php
require_once __DIR__ . '/../config/database.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$db = (new Database())->connect();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $animalId = isset($_GET['animal_id']) ? (int)$_GET['animal_id'] : 0;

        if ($animalId <= 0) {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'message' => 'animal_id is required.',
            ]);
            exit;
        }

        $stmt = $db->prepare("SELECT * FROM animal_locations WHERE animal_id = :animal_id ORDER BY recorded_at DESC LIMIT 50");
        $stmt->execute([':animal_id' => $animalId]);

        echo json_encode([
            'success' => true,
            'data' => $stmt->fetchAll(),
        ]);
        exit;
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $requiredFields = ['animal_id', 'latitude', 'longitude'];

        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                http_response_code(422);
                echo json_encode([
                    'success' => false,
                    'message' => "Field '{$field}' is required.",
                ]);
                exit;
            }
        }

        $animalId = (int)$input['animal_id'];
        $latitude = (float)$input['latitude'];
        $longitude = (float)$input['longitude'];
        $status = $input['status'] ?? 'Normal';

        if ($animalId <= 0 || $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid animal_id or coordinates.',
            ]);
            exit;
        }

        $animalCheck = $db->prepare('SELECT id FROM animals WHERE id = :id LIMIT 1');
        $animalCheck->execute([':id' => $animalId]);
        if (!$animalCheck->fetch()) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Animal not found.',
            ]);
            exit;
        }

        $stmt = $db->prepare(
            'INSERT INTO animal_locations (animal_id, latitude, longitude, status) VALUES (:animal_id, :latitude, :longitude, :status)'
        );
        $stmt->execute([
            ':animal_id' => $animalId,
            ':latitude' => $latitude,
            ':longitude' => $longitude,
            ':status' => $status,
        ]);

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Location saved successfully.',
            'location_id' => (int)$db->lastInsertId(),
        ]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Something went wrong.',
        'error' => $e->getMessage(),
    ]);
}
