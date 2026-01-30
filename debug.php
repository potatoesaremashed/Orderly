<<<<<<< HEAD
<?php
require_once "include/conn.php";

$tables = ['manager', 'cuochi', 'menu', 'tavoli', 'categorie', 'alimenti', 'ordini'];

foreach ($tables as $table) {
    echo "<h3>Table: $table</h3>";
    $result = $conn->query("SELECT * FROM $table");
    
    echo "<table border='1' cellpadding='5'>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<td><strong>$key:</strong> $value</td>";
        }
        echo "</tr>";
    }
    echo "</table><hr>";
}
=======
<?php
require_once "include/conn.php";

$tables = ['manager', 'cuochi', 'menu', 'tavoli', 'categorie', 'alimenti', 'ordini'];

foreach ($tables as $table) {
    echo "<h3>Table: $table</h3>";
    $result = $conn->query("SELECT * FROM $table");
    
    echo "<table border='1' cellpadding='5'>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<td><strong>$key:</strong> $value</td>";
        }
        echo "</tr>";
    }
    echo "</table><hr>";
}
>>>>>>> 121f0d26a10928199208fbcca6213dd712628ac8
?>