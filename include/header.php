<!DOCTYPE html>
<html lang="it">
<!-- 
  =========================================
  FILE: include/header.php
  =========================================
  Questo è il file "scheletro" superiore condiviso da tutte le pagine del sito.
  Invece di riscrivere le stesse righe in ogni file, usiamo 'include' per 
  inserire questo pezzo di codice ovunque serva.
  
  Per uno sviluppatore Junior:
  L'header contiene i metadati (head) che dicono al browser come comportarsi
  e caricano le librerie esterne (come Bootstrap per lo stile).
-->

<head>
    <meta charset="UTF-8"> <!-- Definisce il set di caratteri (UTF-8 supporta lettere accentate). -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Meta tag fondamentale per rendere il sito "Mobile Friendly" (Responsive). -->

    <!-- Bootstrap 5: La libreria CSS più usata al mondo per creare layout moderni velocemente. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Titolo che appare nella linguetta del browser. -->
    <title>Orderly - Gestione Ristorante</title>
</head>

<body> <!-- Da qui inizia il contenuto che l'utente vedrà effettivamente. -->