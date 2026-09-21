<?php
// Sessie starten helemaal bovenaan zodat header() later werkt
session_start();

// Connectie naar database opzetten
require_once('conn.php');

$username = "";
$error_user = "";
$error_pass = "";
$error_algemeen = "";

// Checken of de gebruiker al ingelogd is
$is_ingelogd = isset($_SESSION['user_id']);

// Als de gebruiker al ingelogd is: NIETS in de database doen
if (!$is_ingelogd && $_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Checken of de gebruikersnaam leeg is
    if ($username === '') {
        $error_user = "Vul je gebruikersnaam in.";
    }

    // Checken of het wachtwoord leeg is
    if ($password === '') {
        $error_pass = "Vul je wachtwoord in.";
    }

    // Alleen in de database kijken als beide velden gevuld zijn
    if ($error_user === '' && $error_pass === '') {
        $stmt = $conn->prepare("SELECT id, naam, wachtwoord FROM accounts WHERE Naam = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        $ingelogd = false;

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            if (password_verify($password, $row['wachtwoord'])) {
                // Sessie aanmaken
                session_regenerate_id(true);
                $_SESSION['user_id']  = $row['id'];
                $_SESSION['username'] = $row['naam'];

                // Doorsturen naar index.php
                header('Location: index.php');
                exit;
            }
        }

        $error_algemeen = "Onjuiste gebruikersnaam of wachtwoord.";
        $error_user = "fout";
        $error_pass = "fout";
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen</title>
    <link rel="stylesheet" href="account.css">
    <style>
        .error-msg {
            color: red;
            margin-bottom: 10px;
        }

        input.error {
            border: 1px solid red;
            color: red;
        }

        input.error::placeholder {
            color: red;
            opacity: 1;
        }
    </style>
</head>

<body>
    <a href="accountlinks.php" class="back-arrow" aria-label="Terug">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
    </a>

    <form method="post">
        <h2>Inloggen</h2>

        <?php if ($error_algemeen): ?>
            <div class="error-msg"><?= htmlspecialchars($error_algemeen) ?></div>
        <?php endif; ?>

        <input id="username"
               class="username <?= $error_user ? 'error' : '' ?>"
               placeholder="Username"
               name="username"
               type="text"
               value="<?= htmlspecialchars($username) ?>">

        <input id="ww"
               class="password <?= $error_pass ? 'error' : '' ?>"
               placeholder="Password"
               name="password"
               type="password">

        <input class="button" type="submit" value="Login">
        <a href="account.php">Nog geen account? Maak er een aan</a>
    </form>
</body>

</html>