<?php
session_start();

// Svuota tutte le variabili di sessione
session_unset();

// Distrugge la sessione
session_destroy();

// Torna alla pagina iniziale
header("Location: index.php");
exit();
?>
