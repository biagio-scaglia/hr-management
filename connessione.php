<?php
// Connessione al database MySQL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestione_risorse_umane";

$conn = new mysqli($servername, $username, $password, $dbname);

// Controlla la connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}
?>
