<?php
session_start();
include 'config.php';

if (!isset($_SESSION['id_utente'])) {
    header("Location: index.php");
    exit;
}

$lista = $conn->query("SELECT l.*, u.nome, u.cognome
                       FROM log_attivita l
                       JOIN utenti u ON l.id_utente = u.id
                       ORDER BY l.id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Veiculus - Log Attività</title>
</head>
<body>
    <h2>Log Attività</h2>

    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th>
            <th>Utente</th>
            <th>Azione</th>
            <th>Data</th>
        </tr>

        <?php while($l = $lista->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $l['id']; ?></td>
                <td><?php echo $l['nome'] . " " . $l['cognome']; ?></td>
                <td><?php echo $l['azione']; ?></td>
                <td><?php echo $l['data_azione']; ?></td>
            </tr>
        <?php } ?>
    </table>

    <br>
    <a href="dashboard.php">Torna alla Dashboard</a>
</body>
</html>
