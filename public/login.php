<?php
session_start();
require_once '../includes/db.php';

if (isset($_POST['login'])) {
    try {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception("Login mislukt: foutieve gegevens.");
        }

        $_SESSION['user_id'] = $user['id'];
        header("Location: dashboard.php");
        exit;
    } catch (Exception $e) {
        echo "Fout: " . $e->getMessage();
    }
}
?>


<form action="login.php" method="post">
    <input type="email" name="email" placeholder="E-mail" required>
    <input type="password" name="password" placeholder="Wachtwoord" required>
    <button type="submit" name="login">Login</button>
</form>
