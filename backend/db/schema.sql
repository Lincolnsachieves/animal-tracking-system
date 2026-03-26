CREATE DATABASE IF NOT EXISTS animal_tracking;
USE animal_tracking;

DROP TABLE IF EXISTS animal_locations;
DROP TABLE IF EXISTS animals;

CREATE TABLE animals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tag_number VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    species VARCHAR(50) NOT NULL,
    breed VARCHAR(100) NULL,
    sex ENUM('Male', 'Female', 'Unknown') DEFAULT 'Unknown',
    date_of_birth DATE NULL,
    owner_name VARCHAR(120) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE animal_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT NOT NULL,
    latitude DECIMAL(10, 7) NOT NULL,
    longitude DECIMAL(10, 7) NOT NULL,
    status VARCHAR(50) DEFAULT 'Normal',
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_animal_locations_animal
        FOREIGN KEY (animal_id) REFERENCES animals(id)
        ON DELETE CASCADE
);

INSERT INTO animals (tag_number, name, species, breed, sex, date_of_birth, owner_name)
VALUES
('UG-COW-001', 'Amina', 'Cow', 'Ankole', 'Female', '2023-02-12', 'Adrine Farm'),
('UG-GOAT-002', 'Kato', 'Goat', 'Boer', 'Male', '2024-01-19', 'Adrine Farm');

INSERT INTO animal_locations (animal_id, latitude, longitude, status)
VALUES
(1, -0.6072000, 30.6582000, 'Grazing'),
(2, -0.6098000, 30.6559000, 'Resting');
