<?php
session_start();
require "connessione.php";

// CONTROLLO LOGIN
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}

$utente_id = $_SESSION["id"];
$utente_nome = $_SESSION["nome"];

// -----------------------------
// ATTIVITÀ DI BASE
// -----------------------------
$attivita_base = ["Calcio", "Pallavolo", "DJ Set"];

// -----------------------------
// ATTIVITÀ APPROVATE DAL DB
// -----------------------------
$sql = "SELECT titolo FROM attivita WHERE stato='approvata'";
$result = $conn->query($sql);

$attivita_extra = [];
while ($row = $result->fetch_assoc()) {
    $attivita_extra[] = $row["titolo"];
}

// UNISCO TUTTE LE ATTIVITÀ
$attivita_totali = array_merge($attivita_base, $attivita_extra);

// -----------------------------
// SALVATAGGIO SCELTE STUDENTE
// -----------------------------
if (isset($_POST["salva"])) {
    $turni = [
        "1_giorno_mattina",
        "1_giorno_pomeriggio",
        "2_giorno_mattina",
        "2_giorno_pomeriggio"
    ];

    $scelte = [];

    foreach ($turni as $t) {
        if (isset($_POST[$t])) {
            $scelte[$t] = $_POST[$t];
        }
    }

    $json = json_encode($scelte);

    $sql = "UPDATE studenti SET attivita_iscritte=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $json, $utente_id);
    $stmt->execute();

    $messaggio = "Scelte salvate con successo!";
}

// -----------------------------
// PROPOSTA NUOVA ATTIVITÀ
// -----------------------------
if (isset($_POST["proponi"])) {
    $titolo = $_POST["titolo"];
    $descrizione = $_POST["descrizione"];
    $tipo = $_POST["tipo"];
    $turno = $_POST["turno"];

    $sql = "INSERT INTO attivita 
            (titolo, descrizione, tipo, max_partecipanti, stato, proponente_id, turno)
            VALUES (?, ?, ?, 20, 'in_attesa', ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssds", $titolo, $descrizione, $tipo, $utente_id, $turno);
    $stmt->execute();

    $messaggio2 = "Attività proposta! In attesa di approvazione.";
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Attività</title>
<style>
body { font-family: Arial; background:#f4f4f4; margin:0; }
header { background:#4CAF50; color:white; padding:15px; text-align:center; }
.container { max-width:700px; margin:20px auto; background:white; padding:20px; border-radius:10px; }
label { display:block; margin-top:10px; }
select, input, textarea { width:100%; padding:8px; margin-top:5px; }
button { margin-top:15px; padding:10px; background:#2196F3; color:white; border:none; border-radius:5px; cursor:pointer; }
button:hover { background:#1976D2; }
.messaggio { color:green; }
</style>
</head>

<body>

<header>
    Benvenuto <?php echo htmlspecialchars($utente_nome); ?> |
    <a href="logout.php" style="color:white;">Logout</a>
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

<button type="submit" name="salva">Salva scelte</button>
</form>

<hr>

<h2>Proponi una nuova attività</h2>

<?php if(isset($messaggio2)) echo "<p class='messaggio'>$messaggio2</p>"; ?>

<form method="POST">

<input type="text" name="titolo" placeholder="Titolo attività" required>

<textarea name="descrizione" placeholder="Descrizione" required></textarea>

<select name="tipo" required>
<option value="">Tipo</option>
<option value="individuale">Individuale</option>
<option value="squadra">Squadra</option>
</select>

<select name="turno" required>
<option value="">Turno</option>
<option value="1_giorno_mattina">1° giorno mattina</option>
<option value="1_giorno_pomeriggio">1° giorno pomeriggio</option>
<option value="2_giorno_mattina">2° giorno mattina</option>
<option value="2_giorno_pomeriggio">2° giorno pomeriggio</option>
</select>

<button type="submit" name="proponi">Proponi attività</button>
</form>

</div>
</body>
</html>
