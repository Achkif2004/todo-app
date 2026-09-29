<?php
session_start();
require_once __DIR__ . '/includes/db.php';


if (isset($_POST['register'])) {
    try {
        // Ruwe waarden opslaan; escapen gebeurt pas bij het tonen
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $email === '' || $password === '') {
            throw new Exception("Vul alle velden in.");
        }
        $usernameLength = preg_match_all('/./us', $username);
        if ($usernameLength < 3 || $usernameLength > 30) {
            throw new Exception("Gebruikersnaam moet 3 tot 30 tekens lang zijn.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Vul een geldig e-mailadres in.");
        }
        if (strlen($password) < 8) {
            throw new Exception("Wachtwoord moet minstens 8 tekens lang zijn.");
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

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $conn->lastInsertId();
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
<?php $pageTitle = 'Registreren'; require __DIR__ . '/includes/head.php'; ?>
</head>
<body>

<div class="auth">
    <aside class="auth-aside">
        <div class="brand">
            <span class="brand-logo">
                <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/></svg>
            </span>
            <span>PlanYour<span class="brand-accent">Trip</span></span>
        </div>

        <svg class="flight-path" viewBox="0 0 400 300" aria-hidden="true">
            <path d="M10 280 C 120 60, 260 40, 390 120" fill="none" stroke="#fff" stroke-width="3" stroke-dasharray="4 12" stroke-linecap="round"/>
            <circle cx="10" cy="280" r="7" fill="#fff"/>
            <circle cx="390" cy="120" r="7" fill="none" stroke="#fff" stroke-width="3"/>
        </svg>

        <div>
            <h2>Plan je volgende reis, taak per taak.</h2>
            <p>Maak een lijst per reis, geef taken een prioriteit en hou notities en bestanden netjes bij elkaar.</p>
            <ul class="feature-list">
                <li><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>Eén lijst per reis</li>
                <li><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>Taken met prioriteit en status</li>
                <li><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>Commentaar en bijlagen per taak</li>
            </ul>
        </div>

        <div class="auth-aside-footer">&copy; <?= date('Y') ?> PlanYourTrip</div>
    </aside>

    <main class="auth-main">
        <div class="auth-card">
            <a class="brand" href="index.php">
                <span class="brand-logo">
                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/></svg>
                </span>
                <span>PlanYour<span class="brand-accent">Trip</span></span>
            </a>

            <h1>Account aanmaken</h1>
            <p class="subtitle">Start gratis met het plannen van je reizen.</p>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <form action="register.php" method="post">
                <div class="field">
                    <label for="username">Gebruikersnaam</label>
                    <input type="text" id="username" name="username" placeholder="bv. soulaimane" autocomplete="username" required>
                </div>
                <div class="field">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" placeholder="jij@voorbeeld.be" autocomplete="email" required>
                </div>
                <div class="field">
                    <label for="password">Wachtwoord</label>
                    <input type="password" id="password" name="password" placeholder="Minstens 8 tekens" autocomplete="new-password" minlength="8" required>
                </div>
                <button type="submit" name="register" class="btn btn-primary btn-block">Registreer</button>
            </form>

            <p class="auth-switch">Al een account? <a href="index.php">Log hier in</a></p>
        </div>
    </main>
</div>

</body>
</html>
