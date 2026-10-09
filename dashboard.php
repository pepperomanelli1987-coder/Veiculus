<?php
session_start();
if (!isset($_SESSION['id_utente'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Veiculus - Dashboard</title>
</head>
<body>
    <h2>Benvenuto nella Dashboard</h2>

    <p><a href="veicoli.php">Gestione Veicoli</a></p>
    <p><a href="prenotazioni.php">Prenotazioni</a></p>
    <p><a href="notifiche.php">Notifiche</a></p>
    <p><a href="log.php">Log Attività</a></p>
    <p><a href="logout.php">Logout</a></p>
</body>
</html>
