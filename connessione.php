<?php
$host = "localhost";
$user = "root";
$password = "";          // su XAMPP di solito è vuota
$database = "cogestione";

// Creazione connessione
$conn = new mysqli($host, $user, $password, $database);

// Controllo connessione
if ($conn->connect_error) {
    die("Errore di connessione al database: " . $conn->connect_error);
}
?>
