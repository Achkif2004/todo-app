<?php
session_start();
require_once __DIR__ . '/includes/db.php';


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
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Registreren</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fdf6e3;
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 60px;
        }

        form {
            background-color: #fff;
            padding: 24px;
            border: 2px solid #eee;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
            width: 320px;
        }

        input, button {
            display: block;
            width: 100%;
            margin-bottom: 12px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }

        h2 {
            color: #2a2a2a;
        }
    </style>
</head>
<body>

    <h2>Registreren</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <p class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <form action="register.php" method="post">
        <input type="text" name="username" placeholder="Gebruikersnaam" required>
        <input type="email" name="email" placeholder="E-mail" required>
        <input type="password" name="password" placeholder="Wachtwoord" required>
        <button type="submit" name="register">Registreer</button>
    </form>

    <p>Al een account? <a href="index.php">Login hier</a></p>

</body>
</html>
