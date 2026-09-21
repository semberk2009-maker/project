<?php
session_start();
$is_ingelogd = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account · Playlist Finder</title>
    <link rel="stylesheet" href="account.css">
</head>

<body>
    <a href="index.php" class="back-arrow" aria-label="Terug naar index">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
    </a>

    <div class="account-keuze">
        <h2>Playlist Finder</h2>
        <p class="subtitle">Log in of maak een account aan om playlists te bewaren en te ontdekken.</p>

        <div class="keuze-buttons">
            <a href="login.php" class="keuze-btn">Inloggen</a>
            <a href="account.php" class="keuze-btn secondary">Account aanmaken</a>
        </div>

        <?php if ($is_ingelogd): ?>
            <p class="already-logged-in">
                Je bent ingelogd als <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>.
            </p>
        <?php endif; ?>
    </div>
</body>

</html>