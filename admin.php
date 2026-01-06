<?php
session_start();
require "connessione.php";

// Controllo login
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}

// Controllo ruolo admin
if ($_SESSION["ruolo"] != "admin") {
    echo "Accesso negato";
    exit();
}

// --- APPROVA / RIFIUTA ---
if (isset($_POST["azione"], $_POST["attivita_id"])) {
    $azione = $_POST["azione"];
    $attivita_id = $_POST["attivita_id"];

    if ($azione == "approva") {
        $sql = "UPDATE attivita SET stato='approvata' WHERE id=?";
    } else {
        $sql = "UPDATE attivita SET stato='rifiutata' WHERE id=?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $attivita_id);
    $stmt->execute();
}

// --- RECUPERO ATTIVITÀ IN ATTESA ---
$sql = "SELECT attivita.id, titolo, descrizione, turno, nome, cognome
        FROM attivita
        JOIN studenti ON attivita.proponente_id = studenti.id
        WHERE stato='in_attesa'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Admin - Gestione Attività</title>
<style>
body { font-family: Arial; background:#f4f4f4; }
.container { max-width:800px; margin:30px auto; background:white; padding:20px; border-radius:10px; }
.attivita { border:1px solid #ccc; padding:10px; margin-bottom:15px; border-radius:5px; }
button { padding:5px 10px; margin-right:5px; border:none; border-radius:5px; cursor:pointer; }
.approva { background:#4CAF50; color:white; }
.rifiuta { background:#f44336; color:white; }
</style>
</head>
<body>

<div class="container">
    <h2>Attività da approvare</h2>

    <?php if ($result->num_rows == 0): ?>
        <p>Nessuna attività in attesa.</p>
    <?php endif; ?>

    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="attivita">
            <h3><?php echo htmlspecialchars($row["titolo"]); ?></h3>
            <p><?php echo htmlspecialchars($row["descrizione"]); ?></p>
            <p>
                <strong>Turno:</strong> <?php echo $row["turno"]; ?><br>
                <strong>Proposta da:</strong> <?php echo $row["nome"]." ".$row["cognome"]; ?>
            </p>

            <form method="POST">
                <input type="hidden" name="attivita_id" value="<?php echo $row["id"]; ?>">
                <button class="approva" name="azione" value="approva">Approva</button>
                <button class="rifiuta" name="azione" value="rifiuta">Rifiuta</button>
            </form>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>
