<?php
session_start();
include 'config.php';

if (!isset($_SESSION['id_utente'])) {
    header("Location: index.php");
    exit;
}

// Eliminazione veicolo
if (isset($_GET['elimina'])) {
    $id_elimina = $_GET['elimina'];
    $conn->query("DELETE FROM veicoli WHERE id = $id_elimina");
}

// Inserimento veicolo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $marca = $_POST['marca'];
    $modello = $_POST['modello'];
    $targa = $_POST['targa'];

    $scadenza_bollo = $_POST['scadenza_bollo'];
    $scadenza_assicurazione = $_POST['scadenza_assicurazione'];
    $scadenza_revisione = $_POST['scadenza_revisione'];

    if ($marca !== "" && $modello !== "" && $targa !== "") {

        $sql = "INSERT INTO veicoli 
                (marca, modello, targa, stato, scadenza_bollo, scadenza_assicurazione, scadenza_revisione)
                VALUES 
                ('$marca', '$modello', '$targa', 'disponibile',
                 '$scadenza_bollo', '$scadenza_assicurazione', '$scadenza_revisione')";

        $conn->query($sql);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Gestione Veicoli</title>
</head>
<body>

<h2>Gestione Veicoli</h2>

<h3>Aggiungi Veicolo</h3>

<form method="POST">
    <label>Marca:</label><br>
    <input type="text" name="marca" required><br><br>

    <label>Modello:</label><br>
    <input type="text" name="modello" required><br><br>

    <label>Targa:</label><br>
    <input type="text" name="targa" required><br><br>

    <label>Scadenza Bollo:</label><br>
    <input type="date" name="scadenza_bollo"><br><br>

    <label>Scadenza Assicurazione:</label><br>
    <input type="date" name="scadenza_assicurazione"><br><br>

    <label>Scadenza Revisione:</label><br>
    <input type="date" name="scadenza_revisione"><br><br>

    <button type="submit">Aggiungi</button>
</form>

<hr>

<h3>Elenco Veicoli</h3>

<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Marca</th>
        <th>Modello</th>
        <th>Targa</th>
        <th>Stato</th>
        <th>Bollo</th>
        <th>Assicurazione</th>
        <th>Revisione</th>
        <th>Azioni</th>
    </tr>

    <?php
    $sql = "SELECT * FROM veicoli ORDER BY id DESC";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>".$row['id']."</td>";
        echo "<td>".$row['marca']."</td>";
        echo "<td>".$row['modello']."</td>";
        echo "<td>".$row['targa']."</td>";
        echo "<td>".$row['stato']."</td>";
        echo "<td>".$row['scadenza_bollo']."</td>";
        echo "<td>".$row['scadenza_assicurazione']."</td>";
        echo "<td>".$row['scadenza_revisione']."</td>";
        echo "<td><a href='veicoli.php?elimina=".$row['id']."'>Elimina</a></td>";
        echo "</tr>";
    }
    ?>
</table>

<br>
<a href="dashboard.php">Torna alla Dashboard</a>

</body>
</html>
