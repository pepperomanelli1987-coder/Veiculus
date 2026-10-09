<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM utenti WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['id_utente'] = $user['id'];
        $_SESSION['ruolo'] = $user['ruolo'];
        header("Location: dashboard.php");
        exit;
    } else {
        $errore = "Credenziali errate";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Veiculus - Login</title>
</head>
<body>
    <h2>Login Veiculus</h2>

    <?php if(isset($errore)) echo "<p style='color:red;'>$errore</p>"; ?>

    <form method="POST">
        <input type="text" name="email" placeholder="Email"><br><br>
        <input type="password" name="password" placeholder="Password"><br><br>
        <button type="submit">Accedi</button>
    </form>
</body>
</html>
