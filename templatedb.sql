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

-- TABELLA ALIMENTI (Aggiornata con le colonne nuove)
CREATE TABLE alimenti (
    id_alimento INT AUTO_INCREMENT PRIMARY KEY,
    nome_piatto VARCHAR(100) NOT NULL,
    prezzo DECIMAL(10,2) NOT NULL,
    descrizione TEXT,
    lista_allergeni TEXT, 
    immagine VARCHAR(255) DEFAULT 'default.jpg',
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

-- Piatto di test (Completo con descrizione e allergeni formattati)
INSERT INTO alimenti (nome_piatto, prezzo, descrizione, lista_allergeni, immagine, id_categoria) 
VALUES ('Carbonara', 12.50, 'Classica pasta alla carbonara con guanciale croccante, uova fresche, pecorino romano DOP e pepe nero macinato al momento.', 'Glutine,Uova,Lattosio', 'default.jpg', 2); 
   
-- Tavolo di test
INSERT INTO tavoli (nome_tavolo, password, id_menu) VALUES ('tavolotest', 'test', 1);