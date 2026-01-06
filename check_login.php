<?php
session_start();
require "connessione.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM studenti WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user["password"])) {
            // CREAZIONE SESSIONE
            $_SESSION["id"] = $user["id"];
            $_SESSION["nome"] = $user["nome"];
            $_SESSION["ruolo"] = $user["ruolo"];

            // VAI ALLA DASHBOARD
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Password errata";
        }
    } else {
        echo "Email non trovata";
    }
}
?>