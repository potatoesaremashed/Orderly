DROP DATABASE IF EXISTS ristorante_db;
CREATE DATABASE ristorante_db;
USE ristorante_db;

CREATE TABLE manager (
    id_manager INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE cuochi (
    id_cuoco INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);


CREATE TABLE menu (
    id_menu INT AUTO_INCREMENT PRIMARY KEY,
    nome_menu VARCHAR(50),
    id_manager INT,
    FOREIGN KEY (id_manager) REFERENCES manager(id_manager)
);

CREATE TABLE tavoli (
    id_tavolo INT AUTO_INCREMENT PRIMARY KEY,
    nome_tavolo VARCHAR(50) UNIQUE, 
    password VARCHAR(50),
    id_menu INT,
    FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
);


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
    id_categoria INT,
    FOREIGN KEY (id_categoria) REFERENCES categorie(id_categoria)
);


CREATE TABLE ordini (
    id_ordine INT AUTO_INCREMENT PRIMARY KEY,
    stato ENUM('in_attesa', 'in_preparazione', 'pronto') DEFAULT 'in_attesa', 
    data_ora DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_tavolo INT,
    FOREIGN KEY (id_tavolo) REFERENCES tavoli(id_tavolo)
);

CREATE TABLE dettaglio_ordini (
    id_ordine INT,
    id_alimento INT,
    quantita INT DEFAULT 1,
    note VARCHAR(255),
    PRIMARY KEY (id_ordine, id_alimento),
    FOREIGN KEY (id_ordine) REFERENCES ordini(id_ordine),
    FOREIGN KEY (id_alimento) REFERENCES alimenti(id_alimento)
);


-- DATI TEST
INSERT INTO manager (username, password) VALUES ('admin', 'admin');
INSERT INTO menu (nome_menu, id_manager) VALUES ('Menu Test', 1);

INSERT INTO categorie (nome_categoria, id_menu) VALUES 
('categoriatest', 1),


INSERT INTO alimenti (nome_piatto, prezzo, id_categoria) VALUES 
('alimentotest', 12.00, 1),      
   
INSERT INTO tavoli (nome_tavolo, password, id_menu) VALUES ('tavolotest', 'test', 1);
INSERT INTO cuochi (username, password) VALUES ('cheftest', 'test'); 