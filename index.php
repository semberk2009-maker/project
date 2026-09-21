<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: accountlinks.php');
    exit;
}
require_once("conn.php");

$alle_tabellen = [];
$result = $pdo->query("SHOW TABLES");
while ($row = $result->fetch(PDO::FETCH_NUM)) {
    $alle_tabellen[] = $row[0];
}

$playlists = [];
foreach ($alle_tabellen as $tabel) {
    $kolommen = $pdo->query("SHOW COLUMNS FROM `$tabel`")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('nummer', $kolommen) && in_array('artiestid', $kolommen)) {
        $foto = null;
        foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
            $pad = "playlist_covers/" . $tabel . "." . $ext;
            if (file_exists($pad)) {
                $foto = $pad;
                break;
            }
        }
        $playlists[] = [
            'naam' => $tabel,
            'foto' => $foto
        ];
    }
}

$favorieten = $_SESSION['favorieten'] ?? [];

$favPlaylists = [];
foreach ($favorieten as $fav) {
    foreach ($playlists as $p) {
        if ($p['naam'] === $fav) {
            $favPlaylists[] = $p;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="stylehome.css?v=<?php echo filemtime('stylehome.css'); ?>">
</head>

<body>

    <input type="checkbox" id="menu-toggle" hidden>

    <a href="index.php" class="home-knop" aria-label="Home">
        <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22">
            <path d="M12 3l9 8h-3v10h-5v-6h-2v6H6V11H3z" />
        </svg>
    </a>
    <a href="accountlinks.php" class="profiel-knop" aria-label="Profiel">
        <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22">
            <path d="M12 12c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm0 2c-3.33 0-10 1.67-10 5v3h20v-3c0-3.33-6.67-5-10-5z" />
        </svg>
    </a>
    <aside id="sidebar">
        <label for="menu-toggle" id="sidebar-toggle" aria-label="Menu">
            <div class="disc">
                <div class="disc-label"></div>
                <div class="disc-hole"></div>
            </div>
        </label>

        <div class="sidebar-favorieten">
            <?php foreach ($favPlaylists as $fav): ?>
                <a href="playlist.php?naam=<?php echo urlencode($fav['naam']); ?>" class="sidebar-fav" title="<?php echo htmlspecialchars($fav['naam']); ?>">
                    <div class="sidebar-fav-foto">
                        <?php if ($fav['foto']): ?>
                            <img src="<?php echo htmlspecialchars($fav['foto']); ?>" alt="">
                        <?php else: ?>
                            <span>♪</span>
                        <?php endif; ?>
                    </div>
                    <span class="sidebar-fav-naam"><?php echo htmlspecialchars($fav['naam']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </aside>

    <header></header>

    <h2>Mijn Playlists</h2>

    <?php if (empty($playlists)): ?>
        <p class="leeg-melding">Je hebt nog geen playlists.</p>
    <?php else: ?>
        <div class="playlist-container">
            <?php foreach ($playlists as $playlist): ?>
                <a href="playlist.php?naam=<?php echo urlencode($playlist['naam']); ?>" class="playlist-blok">
                    <div class="playlist-foto">
                        <?php if ($playlist['foto']): ?>
                            <img src="<?php echo htmlspecialchars($playlist['foto']); ?>" alt="">
                        <?php else: ?>
                            <div class="playlist-foto-placeholder">♪</div>
                        <?php endif; ?>
                    </div>
                    <div class="playlist-naam">
                        <?php echo htmlspecialchars($playlist['naam']); ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- ===== VASTE PLAYBAR (leeg) ===== -->
    <div class="playbar">
        <div class="playbar-foto"></div>
        <div class="playbar-info">
            <span class="playbar-titel">—</span>
            <span class="playbar-artiest"></span>
        </div>

        <div class="playbar-progress">
            <span class="playbar-tijd">0:00</span>
            <div class="playbar-bar">
                <div class="playbar-bar-fill"></div>
            </div>
            <span class="playbar-tijd">0:00</span>
        </div>

        <div class="playbar-knoppen">
            <button class="playbar-knop" aria-label="Play">▶</button>
            <button class="playbar-knop" aria-label="Pause">❚❚</button>
        </div>
    </div>

</body>

</html>