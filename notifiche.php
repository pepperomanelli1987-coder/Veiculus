<?php
session_start();
include 'config.php';

if (!isset($_SESSION['id_utente'])) {
    header("Location: index.php");
    exit;
}

/* ============================
   NOTIFICHE PRENOTAZIONI
   - Prenotazioni che finiscono oggi
   - Prenotazioni che finiscono entro 3 giorni
   - Prenotazioni scadute ma non chiuse
============================ */

$oggi = date('Y-m-d');
$tra_3_giorni = date('Y-m-d', strtotime('+3 days'));

$sql = "
SELECT p.*, v.targa 
FROM prenotazioni p
JOIN veicoli v ON p.id_veicolo = v.id
WHERE 
    (p.fine = '$oggi' AND p.stato = 'attiva')
    OR (p.fine <= '$tra_3_giorni' AND p.fine > '$oggi' AND p.stato = 'attiva')
    OR (p.fine < '$oggi' AND p.stato = 'attiva')
ORDER BY p.fine ASC
";

$notifiche = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifiche Prenotazioni</title>

    <!-- Tema grafico professionale -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

<h2>Notifiche Prenotazioni</h2>

<div class="box">
    <h3>Situazioni da controllare</h3>

    <?php
    if ($notifiche->num_rows == 0) {
        echo "<p>Nessuna notifica da mostrare.</p>";
    } else {

        echo "<table>";
        echo "<tr>
                <th>Veicolo</th>
                <th>Utente</th>
                <th>Inizio</th>
                <th>Fine</th>
                <th>Km Inizio</th>
                <th>Stato</th>
                <th>Messaggio</th>
              </tr>";

        while ($n = $notifiche->fetch_assoc()) {

            // Messaggio dinamico
            if ($n['fine'] == $oggi) {
                $msg = "La prenotazione termina oggi";
            } elseif ($n['fine'] < $oggi) {
                $msg = "Prenotazione scaduta: va chiusa";
            } else {
                $msg = "Prenotazione in scadenza entro 3 giorni";
            }

            echo "<tr>
                    <td>".$n['targa']."</td>
                    <td>".$n['utente']."</td>
                    <td>".$n['inizio']."</td>
                    <td>".$n['fine']."</td>
                    <td>".$n['km_inizio']."</td>
                    <td>".$n['stato']."</td>
                    <td><strong>$msg</strong></td>
                  </tr>";
        }

        echo "</table>";
    }
    ?>
</div>

<br>
<a href="dashboard.php"><button>Torna alla Dashboard</button></a>

</div>

</body>
</html>
