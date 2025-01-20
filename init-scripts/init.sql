-- init-scripts/init.sql
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    imie VARCHAR(255) NOT NULL,
    nazwisko VARCHAR(255) NOT NULL,
    login VARCHAR(255) NOT NULL UNIQUE,
    haslo VARCHAR(255) NOT NULL
);

-- Opcjonalnie wstaw przykładowe dane
INSERT INTO users (imie, nazwisko, login, haslo)
VALUES 
('Admin', 'Adminowski', 'admin', 'admin123'),
('Jan', 'Kowalski', 'jkowalski', 'haslo123');
