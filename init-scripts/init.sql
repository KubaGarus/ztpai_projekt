-- init-scripts/init.sql
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    imie VARCHAR(255) NOT NULL,
    nazwisko VARCHAR(255) NOT NULL,
    login VARCHAR(255) NOT NULL UNIQUE,
    haslo VARCHAR(255) NOT NULL,
    roles JSON NOT NULL
);

INSERT INTO users (imie, nazwisko, login, haslo)
VALUES 
('Admin', 'Adminowski', 'admin', 'admin123'),
('Jan', 'Kowalski', 'jkowalski', 'haslo123');

CREATE TABLE documents (
    document_id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    user_id INT NOT NULL,
    promotor_id INT NOT NULL,
    status SMALLINT NOT NULL CHECK (status BETWEEN 1 AND 4),
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_document_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_document_promotor FOREIGN KEY (promotor_id) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO documents (title, content, user_id, promotor_id, status)
VALUES ('Moja praca', 'Treść mojej pracy...', 1, 2, 4);


CREATE TABLE logs (
    log_id SERIAL PRIMARY KEY,               -- Klucz główny, automatyczna numeracja
    document_id INT NOT NULL,                -- Klucz obcy do tabeli documents
    user_id INT NOT NULL,                     -- Klucz obcy do tabeli users (kto dokonał zmiany)
    status_before SMALLINT NOT NULL CHECK (status_before BETWEEN 1 AND 4), -- Poprzedni status
    status_after SMALLINT NOT NULL CHECK (status_after BETWEEN 1 AND 4),   -- Nowy status
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE conversations (
    conversation_id SERIAL PRIMARY KEY,      -- Klucz główny, automatyczna numeracja
    document_id INT NOT NULL,                -- Klucz obcy do tabeli documents
    order_num INT NOT NULL,                  -- Kolejność wiadomości w wątku
    user_id INT NOT NULL,                    -- Klucz obcy do tabeli users (autor wiadomości)
    content TEXT NOT NULL,                   -- Treść wiadomości (textarea)
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Data wiadomości

    -- Klucze obce
    CONSTRAINT fk_conversations_document FOREIGN KEY (document_id) REFERENCES documents(document_id) ON DELETE CASCADE,
    CONSTRAINT fk_conversations_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);