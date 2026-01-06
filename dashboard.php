<?php
session_start();
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<body>
    <h1>Benvenuto nella cogestione</h1>
    <p>Qui ci saranno le attività</p>
</body>
</html>
