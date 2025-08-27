<?php
session_start();
require_once __DIR__ . '/includes/db.php';

// Database verbinding controleren
try {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->query('SELECT 1'); // Test query om verbinding te verifiëren
} catch(PDOException $e) {
    error_log("Database fout: " . $e->getMessage());
    $_SESSION['error'] = "Er is een technisch probleem opgetreden.";
    header("Location: index.php");
    exit;
}

if (isset($_POST['login'])) {
    try {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Email validatie
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Ongeldig e-mailadres.");
        }

        // Query uitvoeren met logging
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        // Logging voor debugging
        error_log("Login poging voor email: " . $email);
        error_log("Query uitgevoerd, rijen gevonden: " . ($stmt->rowCount() > 0 ? 'ja' : 'nee'));

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            throw new Exception("Gebruiker niet gevonden.");
        }

        if (!password_verify($password, $user['password'])) {
            throw new Exception("Ongeldig wachtwoord.");
        }

        // Succesvolle login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['message'] = "Welkom terug!";
        header("Location: dashboard.php");
        exit;

    } catch (Exception $e) {
        error_log("Login fout: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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

        p {
            text-align: center;
        }

        a {
            color: #4CAF50;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <h2>Login</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <p class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <form action="" method="post">
        <input type="email" name="email" placeholder="E-mail" required>
        <input type="password" name="password" placeholder="Wachtwoord" required>
        <button type="submit" name="login">Login</button>
    </form>

    <p>Geen account? <a href="register.php">Registreer je hier</a>.</p>

</body>
</html>
