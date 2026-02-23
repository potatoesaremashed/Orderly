DROP DATABASE IF EXISTS ristorante_db;
CREATE DATABASE ristorante_db;
USE ristorante_db;

-- TABELLA MANAGER
CREATE TABLE manager (
    id_manager INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- TABELLA CUOCHI
CREATE TABLE cuochi (
    id_cuoco INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- TABELLA MENU
CREATE TABLE menu (
    id_menu INT AUTO_INCREMENT PRIMARY KEY,
    nome_menu VARCHAR(50),
    id_manager INT,
    FOREIGN KEY (id_manager) REFERENCES manager(id_manager)
);

-- TABELLA TAVOLI
CREATE TABLE tavoli (
    id_tavolo INT AUTO_INCREMENT PRIMARY KEY,
    nome_tavolo VARCHAR(50) UNIQUE, 
    password VARCHAR(50),
    stato ENUM('libero','occupato','riservato') DEFAULT 'libero',
    posti INT DEFAULT 4,
    id_menu INT,
    FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
);

-- TABELLA CATEGORIE
CREATE TABLE categorie (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome_categoria VARCHAR(50) NOT NULL, 
    id_menu INT,
    FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
);

CREATE TABLE alimenti (
    id_alimento INT AUTO_INCREMENT PRIMARY KEY,
    nome_piatto VARCHAR(100) NOT NULL,
    prezzo DECIMAL(10,2) NOT NULL,
    descrizione TEXT,
    lista_allergeni TEXT, 
    immagine MEDIUMBLOB, 
    id_categoria INT,
    FOREIGN KEY (id_categoria) REFERENCES categorie(id_categoria)
);

-- TABELLA ORDINI
CREATE TABLE ordini (
    id_ordine INT AUTO_INCREMENT PRIMARY KEY,
    stato ENUM('in_attesa', 'in_preparazione', 'pronto') DEFAULT 'in_attesa', 
    data_ora DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_tavolo INT,
    FOREIGN KEY (id_tavolo) REFERENCES tavoli(id_tavolo)
);

-- TABELLA DETTAGLIO ORDINI
CREATE TABLE dettaglio_ordini (
    id_ordine INT,
    id_alimento INT,
    quantita INT DEFAULT 1,
    note VARCHAR(255),
    PRIMARY KEY (id_ordine, id_alimento),
    FOREIGN KEY (id_ordine) REFERENCES ordini(id_ordine) ON DELETE CASCADE,
    FOREIGN KEY (id_alimento) REFERENCES alimenti(id_alimento) ON DELETE CASCADE
);


-- --- DATI DI TEST ---

-- Admin e Chef
INSERT INTO manager (username, password) VALUES ('admin', 'admin');
INSERT INTO cuochi (username, password) VALUES ('cheftest', 'test'); 

-- Creazione Menu base
INSERT INTO menu (nome_menu, id_manager) VALUES ('Menu Test', 1);

-- Categorie
INSERT INTO categorie (nome_categoria, id_menu) VALUES 
('Antipasti', 1),
('Primi', 1),
('Secondi', 1),
('Dolci', 1);

-- Piatto di test (Il campo immagine è NULL di default nel test script)
INSERT INTO alimenti (nome_piatto, prezzo, descrizione, lista_allergeni, immagine, id_categoria) 
VALUES ('Carbonara', 12.50, 'Classica pasta alla carbonara con guanciale croccante, uova fresche, pecorino romano DOP e pepe nero macinato al momento.', 'Glutine,Uova,Lattosio', NULL, 2); 
   
-- Tavoli di test
INSERT INTO tavoli (nome_tavolo, password, stato, posti, id_menu) VALUES ('tavolotest', 'test', 'libero', 4, 1);
INSERT INTO tavoli (nome_tavolo, password, stato, posti, id_menu) VALUES 
('tavolo1', '1234', 'libero', 2, 1),
('tavolo2', '1234', 'occupato', 4, 1),
('tavolo3', '1234', 'riservato', 6, 1),
('tavolo4', '1234', 'libero', 4, 1),
('tavolo5', '1234', 'occupato', 2, 1),
('tavolo6', '1234', 'libero', 8, 1),
('tavolo7', '1234', 'riservato', 4, 1),
('tavolo8', '1234', 'libero', 4, 1);