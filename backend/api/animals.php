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
        $sql = "
            SELECT a.*, l.latitude AS last_latitude, l.longitude AS last_longitude,
                   l.recorded_at AS last_seen, l.status AS last_status
            FROM animals a
            LEFT JOIN (
                SELECT l1.*
                FROM animal_locations l1
                INNER JOIN (
                    SELECT animal_id, MAX(recorded_at) AS latest_record
                    FROM animal_locations
                    GROUP BY animal_id
                ) l2
                ON l1.animal_id = l2.animal_id AND l1.recorded_at = l2.latest_record
            ) l ON a.id = l.animal_id
            ORDER BY a.created_at DESC
        ";
        $stmt = $db->query($sql);
        echo json_encode([
            'success' => true,
            'data' => $stmt->fetchAll(),
        ]);
        exit;
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        $requiredFields = ['tag_number', 'name', 'species', 'owner_name'];
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                http_response_code(422);
                echo json_encode([
                    'success' => false,
                    'message' => "Field '{$field}' is required.",
                ]);
                exit;
            }
        }

        $sql = "INSERT INTO animals (tag_number, name, species, breed, sex, date_of_birth, owner_name)
                VALUES (:tag_number, :name, :species, :breed, :sex, :date_of_birth, :owner_name)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':tag_number' => trim($input['tag_number']),
            ':name' => trim($input['name']),
            ':species' => trim($input['species']),
            ':breed' => $input['breed'] ?? null,
            ':sex' => $input['sex'] ?? null,
            ':date_of_birth' => $input['date_of_birth'] ?? null,
            ':owner_name' => trim($input['owner_name']),
        ]);

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Animal added successfully.',
            'animal_id' => (int)$db->lastInsertId(),
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
