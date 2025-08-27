<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


session_start();
require_once(__DIR__ . '/includes/db.php');


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
        $_SESSION['message'] = "Welkom terug!";
        header("Location: dashboard.php");
        exit;
    } catch (Exception $e) {
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

          @media (max-width: 1200px) {
            html { font-size: 22px; }
            body {
                margin-top: 0;
                min-height: 100svh;
                justify-content: center;
                padding: 28px;
            }
            h2 {
                font-size: 2.6rem;
                margin: 0 0 24px 0;
                text-align: center;
            }
            form {
                width: 100%;
                max-width: 760px;
                padding: 40px;
                border-radius: 18px;
                box-shadow: 0 10px 32px rgba(0,0,0,0.14);
            }
            input, button {
                font-size: 1.375rem;
                padding: 20px;
                border-radius: 12px;
            }
            button {
                min-height: 68px;
                letter-spacing: .4px;
            }
            p, a { font-size: 1.125rem; }
        }

        @media (max-width: 380px) {
            html { font-size: 24px; }
            form { padding: 44px; border-radius: 20px; }
            input, button { padding: 22px; border-radius: 14px; }
            h2 { font-size: 2.8rem; }
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

