<?php
function registra_log($conn, $id_utente, $azione) {
    $sql = "INSERT INTO log_attivita (id_utente, azione) VALUES ('$id_utente', '$azione')";
    $conn->query($sql);
}
?>
