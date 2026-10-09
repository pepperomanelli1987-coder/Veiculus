<?php
session_start();
include 'config.php';

if (!isset($_SESSION['id_utente'])) {
    header("Location: index.php");
    exit;
}

// CHIUSURA PRENOTAZIONE
if (isset($_POST['chiudi'])) {
    $id_pren = $_POST['id_pren'];
    $km_fine = $_POST['km_fine'];

    $conn->query("UPDATE prenotazioni 
                  SET km_fine = '$km_fine', stato = 'chiusa' 
                  WHERE id = $id_pren");

    $conn->query("UPDATE veicoli 
                  SET stato = 'disponibile' 
                  WHERE id = (SELECT id_veicolo FROM prenotazioni WHERE id = $id_pren)");
}

// INSERIMENTO PRENOTAZIONE
if (isset($_POST['prenota'])) {

    $id_veicolo = $_POST['id_veicolo'];
    $utente = $_POST['utente'];
    $inizio = $_POST['inizio'];
    $fine = $_POST['fine'];
    $km_inizio = $_POST['km_inizio'];

    if ($id_veicolo && $utente && $inizio && $fine && $km_inizio) {

        $conn->query("INSERT INTO prenotazioni 
                      (id_veicolo, utente, inizio, fine, km_inizio, stato)
                      VALUES 
                      ('$id_veicolo', '$utente', '$inizio', '$fine', '$km_inizio', 'attiva')");

        $conn->query("UPDATE veicoli SET stato = 'occupato' WHERE id = $id_veicolo");
    }
}

// PREZZIARIO
if (isset($_POST['calcola_prezzo'])) {

    $id_veicolo = $_POST['id_veicolo'];
    $giorni = $_POST['giorni'];
    $prezzo_giornaliero = $_POST['prezzo_giornaliero'];
    $prezzo_km = $_POST['prezzo_km'];
    $km_previsti = $_POST['km_previsti'];

    $totale_giorni = $giorni * $prezzo_giornaliero;
    $totale_km = $km_previsti * $prezzo_km;

    $totale = $totale_giorni + $totale_km;

    $prezziario_risultato = "Totale prenotazione veicolo $id_veicolo: € $totale";
}

// CALENDARIO MULTI-MESE + FILTRO VEICOLO
$prenotazioni_cal = [];

$cal_sql = "SELECT id_veicolo, inizio, fine FROM prenotazioni WHERE stato = 'attiva'";
$cal_res = $conn->query($cal_sql);

while ($p = $cal_res->fetch_assoc()) {
    $prenotazioni_cal[] = $p;
}

$veicolo_filtro = isset($_GET['veicolo']) ? $_GET['veicolo'] : null;

function giornoOccupato($data, $prenotazioni_cal, $veicolo_filtro) {
    foreach ($prenotazioni_cal as $p) {

        if ($veicolo_filtro && $p['id_veicolo'] != $veicolo_filtro) {
            continue;
        }

        if ($data >= $p['inizio'] && $data <= $p['fine']) {
            return true;
        }
    }
    return false;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestione Prenotazioni</title>
   <style>
    body {
        font-family: Arial, sans-serif;
        background: #f2f4f7;
        margin: 0;
        padding: 20px;
        color: #333;
    }

    h2, h3 {
        color: #1a3d6f;
        margin-bottom: 10px;
    }

    .row-flex {
        display: flex;
        gap: 40px;
        align-items: flex-start;
    }

    .box, .right-box {
        background: #ffffff;
        border: 1px solid #d0d4db;
        padding: 15px;
        width: 300px;
        border-radius: 8px;
        box-shadow: 0px 2px 6px rgba(0,0,0,0.1);
    }

    .right-box {
        position: absolute;
        right: 20px;
        top: 120px;
    }

    .box h3, .right-box h3 {
        margin-top: 0;
        color: #1a3d6f;
        border-bottom: 2px solid #1a3d6f;
        padding-bottom: 5px;
    }

    input, select {
        width: 100%;
        padding: 8px;
        border: 1px solid #c5c9d0;
        border-radius: 5px;
        margin-top: 5px;
        margin-bottom: 10px;
        background: #f9fafc;
    }

    button {
        background: #1a3d6f;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
        width: 100%;
    }

    button:hover {
        background: #244b85;
    }

    .cal-container { 
        display: flex; 
        gap: 20px; 
        margin-top: 20px;
    }

    .calendario { 
        display: grid; 
        grid-template-columns: repeat(7, 1fr); 
        width: 280px; 
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0px 2px 6px rgba(0,0,0,0.1);
        padding: 10px;
    }

    .giorno { 
        padding: 8px; 
        border: 1px solid #e0e3e8; 
        text-align: center; 
        border-radius: 4px;
        font-weight: bold;
    }

    .oggi { 
        background-color: #1a3d6f; 
        color: white; 
    }

    .occupato { 
        background-color: #d9534f; 
        color: white; 
    }

    .libero { 
        background-color: #5cb85c; 
        color: white; 
    }

    .mese-titolo { 
        text-align:center; 
        font-weight:bold; 
        margin-bottom:10px; 
        color: #1a3d6f;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background: #ffffff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0px 2px 6px rgba(0,0,0,0.1);
    }

    th {
        background: #1a3d6f;
        color: white;
        padding: 10px;
        text-align: left;
    }

    td {
        padding: 10px;
        border-bottom: 1px solid #e0e3e8;
    }

    tr:hover {
        background: #f5f7fa;
    }
</style>
</head>
<body>

<h2>Gestione Prenotazioni</h2>

<div class="row-flex">

    <!-- PREZZIARIO -->
    <div class="box">
        <h3>Calcolo Prezzo</h3>

        <form method="POST">
            <label>ID Veicolo:</label><br>
            <input type="number" name="id_veicolo" required><br><br>

            <label>Giorni prenotati:</label><br>
            <input type="number" name="giorni" required><br><br>

            <label>Prezzo giornaliero (€):</label><br>
            <input type="number" name="prezzo_giornaliero" required><br><br>

            <label>Prezzo per chilometro (€):</label><br>
            <input type="number" name="prezzo_km" required><br><br>

            <label>Km previsti:</label><br>
            <input type="number" name="km_previsti" required><br><br>

            <button type="submit" name="calcola_prezzo">Calcola Totale</button>
        </form>

        <?php
        if (isset($prezziario_risultato)) {
            echo "<h4>$prezziario_risultato</h4>";
        }
        ?>
    </div>

</div>

<!-- NUOVA PRENOTAZIONE A DESTRA -->
<div class="right-box">
    <h3>Nuova Prenotazione</h3>

    <form method="POST">
        <label>ID Veicolo:</label><br>
        <input type="number" name="id_veicolo" required><br><br>

        <label>Utente:</label><br>
        <input type="text" name="utente" required><br><br>

        <label>Data Inizio:</label><br>
        <input type="date" name="inizio" required><br><br>

        <label>Data Fine:</label><br>
        <input type="date" name="fine" required><br><br>

        <label>Km Inizio:</label><br>
        <input type="number" name="km_inizio" required><br><br>

        <button type="submit" name="prenota">Prenota</button>
    </form>
</div>

<hr>

<!-- FILTRO VEICOLO -->
<h3>Calendario Occupazione Veicoli</h3>

<form method="GET" style="margin-bottom:20px;">
    <label>Seleziona veicolo:</label>
    <select name="veicolo" onchange="this.form.submit()">
        <option value="">Tutti</option>
        <?php
        $v_sql = "SELECT id, targa FROM veicoli ORDER BY targa";
        $v_res = $conn->query($v_sql);

        while ($v = $v_res->fetch_assoc()) {
            $sel = ($veicolo_filtro == $v['id']) ? "selected" : "";
            echo "<option value='".$v['id']."' $sel>".$v['targa']."</option>";
        }
        ?>
    </select>
</form>

<!-- CALENDARIO MULTI-MESE -->
<div class="cal-container">
<?php
$oggi = date('Y-m-d');

$mesi = [
    date('Y-m', strtotime('-1 month')),
    date('Y-m'),
    date('Y-m', strtotime('+1 month'))
];

foreach ($mesi as $m) {

    list($anno, $mese) = explode('-', $m);
    $giorni_mese = cal_days_in_month(CAL_GREGORIAN, $mese, $anno);

    echo "<div>";
    echo "<div class='mese-titolo'>".date('F Y', strtotime($m.'-01'))."</div>";
    echo "<div class='calendario'>";

    for ($g = 1; $g <= $giorni_mese; $g++) {

        $data_corrente = "$anno-$mese-" . str_pad($g, 2, '0', STR_PAD_LEFT);

        if ($data_corrente == $oggi) {
            $classe = "oggi";
        } elseif (giornoOccupato($data_corrente, $prenotazioni_cal, $veicolo_filtro)) {
            $classe = "occupato";
        } else {
            $classe = "libero";
        }

        echo "<div class='giorno $classe'>$g</div>";
    }

    echo "</div></div>";
}
?>
</div>

<hr>

<!-- ELENCO PRENOTAZIONI -->
<h3>Elenco Prenotazioni</h3>

<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Veicolo</th>
        <th>Utente</th>
        <th>Inizio</th>
        <th>Fine</th>
        <th>Km Inizio</th>
        <th>Km Fine</th>
        <th>Stato</th>
        <th>Azioni</th>
    </tr>

    <?php
    $oggi = date('Y-m-d');

    $sql = "SELECT p.*, v.targa 
            FROM prenotazioni p 
            JOIN veicoli v ON p.id_veicolo = v.id
            ORDER BY p.id DESC";

if (isset($_POST['elimina'])) {

    $id_pren = $_POST['id_pren'];

    $sql_delete = "DELETE FROM prenotazioni WHERE id = $id_pren";

    if ($conn->query($sql_delete) === TRUE) {
        echo "<p>Prenotazione eliminata.</p>";
    } else {
        echo "<p>Errore durante l'eliminazione: " . $conn->error . "</p>";
    }
}

    $result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {

    echo "<tr>";
    echo "<td>".$row['id']."</td>";
    echo "<td>".$row['targa']."</td>";
    echo "<td>".$row['utente']."</td>";
    echo "<td>".$row['inizio']."</td>";
    echo "<td>".$row['fine']."</td>";
    echo "<td>".$row['km_inizio']."</td>";

    // Protezione km_fine per evitare warning
    echo "<td>".(isset($row['km_fine']) ? $row['km_fine'] : '')."</td>";

    echo "<td>".$row['stato']."</td>";
    echo "<td>";

    // FORM CHIUSURA PRENOTAZIONE
    if ($row['stato'] == 'attiva' && $row['fine'] <= $oggi) {

        echo "<form method='POST'>
                <input type='hidden' name='id_pren' value='".$row['id']."'>
                <input type='number' name='km_fine' placeholder='Km finali' required>
                <button type='submit' name='chiudi'>Chiudi</button>
              </form>";
    }

    // FORM ELIMINAZIONE PRENOTAZIONE
    echo "<form method='POST'>
            <input type='hidden' name='id_pren' value='".$row['id']."'>
            <button type='submit' name='elimina'>Elimina</button>
          </form>";

    echo "</td>";
    echo "</tr>";
}
?>
</table>

<br>
<a href="dashboard.php">Torna alla Dashboard</a>

</body>
</html>
