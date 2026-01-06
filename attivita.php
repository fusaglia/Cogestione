<?php
session_start();
require "connessione.php";

// Controllo login
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}

$utente_id = $_SESSION["id"];
$utente_nome = $_SESSION["nome"];

// --- Attività di base ---
$attivita_base = ["Calcio", "Pallavolo", "DJ Set"];

// --- Recupero attività proposte dagli studenti approvate ---
$sql = "SELECT titolo FROM attivita WHERE stato='approvata'";
$result = $conn->query($sql);
$attivita_extra = [];
while ($row = $result->fetch_assoc()) {
    $attivita_extra[] = $row['titolo'];
}

// --- Combino le due liste per i menu a tendina ---
$attivita_totali = array_merge($attivita_base, $attivita_extra);

// --- Salvataggio selezione (se clicca submit) ---
if (isset($_POST['salva'])) {
    $turni = ["1_giorno_mattina","1_giorno_pomeriggio","2_giorno_mattina","2_giorno_pomeriggio"];
    $scelte = [];
    foreach ($turni as $t) {
        if (isset($_POST[$t])) {
            $scelte[$t] = $_POST[$t];
        }
    }
    // Salvo le scelte nel DB nel campo attivita_iscritte come stringa JSON
    $json = json_encode($scelte);
    $sql = "UPDATE studenti SET attivita_iscritte=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $json, $utente_id);
    $stmt->execute();
    $messaggio = "Scelte salvate con successo!";
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Attività</title>
<style>
body { font-family: Arial; background: #f4f4f4; margin:0; padding:0;}
header { background:#4CAF50; color:white; padding:15px; text-align:center;}
.container { max-width:600px; margin:20px auto; background:white; padding:20px; border-radius:10px; }
label { display:block; margin-top:10px; }
select { width:100%; padding:8px; margin:5px 0 15px 0; border-radius:5px; border:1px solid #ccc; }
button { padding:10px 15px; background:#2196F3; color:white; border:none; border-radius:5px; cursor:pointer; }
button:hover { background:#1976D2; }
.messaggio { color: green; }
</style>
</head>
<body>

<header>
Benvenuto <?php echo htmlspecialchars($utente_nome); ?> | <a href="logout.php" style="color:white;">Logout</a>
</header>

<div class="container">
    <h2>Scegli le attività</h2>

    <?php if(isset($messaggio)) echo "<p class='messaggio'>$messaggio</p>"; ?>

    <form method="POST">
        <label>1° giorno mattina</label>
        <select name="1_giorno_mattina" required>
            <option value="">-- Seleziona --</option>
            <?php foreach($attivita_totali as $a) echo "<option>".htmlspecialchars($a)."</option>"; ?>
        </select>

        <label>1° giorno pomeriggio</label>
        <select name="1_giorno_pomeriggio" required>
            <option value="">-- Seleziona --</option>
            <?php foreach($attivita_totali as $a) echo "<option>".htmlspecialchars($a)."</option>"; ?>
        </select>

        <label>2° giorno mattina</label>
        <select name="2_giorno_mattina" required>
            <option value="">-- Seleziona --</option>
            <?php foreach($attivita_totali as $a) echo "<option>".htmlspecialchars($a)."</option>"; ?>
        </select>

        <label>2° giorno pomeriggio</label>
        <select name="2_giorno_pomeriggio" required>
            <option value="">-- Seleziona --</option>
            <?php foreach($attivita_totali as $a) echo "<option>".htmlspecialchars($a)."</option>"; ?>
        </select>

        <button type="submit" name="salva">Salva le scelte</button>
    </form>
</div>

</body>
</html>
