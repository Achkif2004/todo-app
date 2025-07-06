<?php
session_start();
require_once '../includes/db.php';

if (isset($_POST['register'])) {
    try {
        $username = htmlspecialchars($_POST['username']);
        $email = htmlspecialchars($_POST['email']);
        $password = $_POST['password'];

        if (empty($username) || empty($email) || empty($password)) {
            throw new Exception("Vul alle velden in.");
        }

        // Check of email of username al bestaat
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Gebruiker met deze e-mail of gebruikersnaam bestaat al.");
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hash]);

        $_SESSION['user_id'] = $conn->lastInsertId();
        $_SESSION['message'] = "Registratie gelukt!";
        header("Location: dashboard.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: register.php");
        exit;
    }
}
?>

<?php if (isset($_SESSION['error'])): ?>
    <p style="color:red;"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
<?php endif; ?>

<form action="register.php" method="post">
    <input type="text" name="username" placeholder="Gebruikersnaam" required>
    <input type="email" name="email" placeholder="E-mail" required>
    <input type="password" name="password" placeholder="Wachtwoord" required>
    <button type="submit" name="register">Registreer</button>
</form>
