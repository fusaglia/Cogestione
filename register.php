<?php
require_once "connessione.php";

// Controllo che i dati arrivino dal form
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Recupero dati dal form
    $nome = trim($_POST["nome"]);
    $cognome = trim($_POST["cognome"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Controllo campi vuoti
    if (empty($nome) || empty($cognome) || empty($email) || empty($password)) {
        die("Errore: tutti i campi sono obbligatori.");
    }

    // Controllo se email già esistente
    $query = "SELECT id FROM studenti WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        die("Errore: email già registrata.");
    }

    $stmt->close();

    // Hash della password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Inserimento nuovo studente
    $query = "INSERT INTO studenti (nome, cognome, email, password, ruolo)
              VALUES (?, ?, ?, ?, 'studente')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $nome, $cognome, $email, $password_hash);

    if ($stmt->execute()) {
        // Registrazione riuscita → vai al login
        header("Location: login.php");
        exit();
    } else {
        echo "Errore durante la registrazione.";
    }

    $stmt->close();
    $conn->close();
}
?>
