<?php
session_start();
require "connessione.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = $_POST["nome"];
    $cognome = $_POST["cognome"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $sql = "INSERT INTO studenti (nome, cognome, email, password, ruolo)
            VALUES (?, ?, ?, ?, 'studente')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nome, $cognome, $email, $password);

    if ($stmt->execute()) {

        // CREAZIONE SESSIONE (login automatico)
        $_SESSION["id"] = $stmt->insert_id;
        $_SESSION["nome"] = $nome;
        $_SESSION["ruolo"] = "studente";

        // VAI ALLA DASHBOARD
        header("Location: attivita.php");
        exit();
    } else {
        echo "Errore durante la registrazione";
    }
}
?>