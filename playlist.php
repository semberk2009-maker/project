<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: accountlinks.php');
    exit;
}
require_once('conn.php');

$playlistNaam = $_GET['naam'] ?? '';
$playlistNaam = preg_replace('/[^a-zA-Z0-9_]/', '', $playlistNaam);
$melding = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['maak_playlist'])) {
    $nieuweNaam = $_POST['playlist_naam'] ?? '';
    $nieuweNaam = preg_replace('/[^a-zA-Z0-9_]/', '', $nieuweNaam);
    if ($nieuweNaam === '') {
        $melding = 'Vul een geldige playlistnaam in.';
    } else {
        $check = $pdo->prepare('SHOW TABLES LIKE ?');
        $check->execute([$nieuweNaam]);
        if ($check->fetch()) {
            $melding = 'Deze playlist bestaat al.';
        } else {
            $sql = "CREATE TABLE `$nieuweNaam` (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nummer VARCHAR(255) NOT NULL,
                artiestid INT NULL
            )";
            $pdo->exec($sql);
            header('Location: playlist.php?naam=' . urlencode($nieuweNaam));
            exit();
        }
    }
}

if ($playlistNaam !== '') {
    $check = $pdo->prepare('SHOW TABLES LIKE ?');
    $check->execute([$playlistNaam]);
    if (!$check->fetch()) {
        die('Playlist niet gevonden.');
    }
}

if (!isset($_SESSION['favorieten'])) {
    $_SESSION['favorieten'] = [];
}

if ($playlistNaam !== '' && isset($_POST['toggle_favoriet'])) {
    if (in_array($playlistNaam, $_SESSION['favorieten'])) {
        $_SESSION['favorieten'] = array_values(array_diff($_SESSION['favorieten'], [$playlistNaam]));
    } else {
        $_SESSION['favorieten'][] = $playlistNaam;
    }
    header('Location: playlist.php?naam=' . urlencode($playlistNaam));
    exit();
}

if ($playlistNaam !== '' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verwijder_nummer'])) {
    $nummerId = (int)($_POST['nummer_id'] ?? 0);
    if ($nummerId > 0) {
        $stmt = $pdo->prepare("DELETE FROM `$playlistNaam` WHERE id = ?");
        $stmt->execute([$nummerId]);
    }
    header('Location: playlist.php?naam=' . urlencode($playlistNaam));
    exit();
}

$isFavoriet = false;
if ($playlistNaam !== '') {
    $isFavoriet = in_array($playlistNaam, $_SESSION['favorieten']);
}

$favPlaylists = [];
foreach ($_SESSION['favorieten'] as $fav) {
    $favFoto = null;
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        $pad = 'playlist_covers/' . $fav . '.' . $ext;
        if (file_exists($pad)) {
            $favFoto = $pad;
            break;
        }
    }
    $favPlaylists[] = ['naam' => $fav, 'foto' => $favFoto];
}

$coverMap = 'playlist_covers/';
if (!is_dir($coverMap)) {
    mkdir($coverMap, 0755, true);
}

if ($playlistNaam !== '' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['cover'])) {
    $bestand = $_FILES['cover'];
    if ($bestand['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($bestand['name'], PATHINFO_EXTENSION));
        $toegestaan = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $toegestaan)) {
            foreach ($toegestaan as $oudeExt) {
                $oudPad = $coverMap . $playlistNaam . '.' . $oudeExt;
                if (file_exists($oudPad)) {
                    unlink($oudPad);
                }
            }
            $nieuwPad = $coverMap . $playlistNaam . '.' . $ext;
            move_uploaded_file($bestand['tmp_name'], $nieuwPad);
            header('Location: playlist.php?naam=' . urlencode($playlistNaam));
            exit();
        } else {
            $melding = 'Alleen JPG, PNG of WEBP toegestaan.';
        }
    } else {
        $melding = 'Upload mislukt.';
    }
}

$foto = null;
if ($playlistNaam !== '') {
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        $pad = $coverMap . $playlistNaam . '.' . $ext;
        if (file_exists($pad)) {
            $foto = $pad;
            break;
        }
    }
}

$nummers = [];
$aantal = 0;
if ($playlistNaam !== '') {
    $stmt = $pdo->prepare("SELECT p.id, p.nummer, n.duur, a.naam AS artiest_naam
                           FROM `$playlistNaam` p
                           LEFT JOIN artiesten a ON p.artiestid = a.artiest_id
                           LEFT JOIN nummers n ON n.naam = p.nummer AND n.artiest_id = p.artiestid
                           ORDER BY p.id");
    $stmt->execute();
    $nummers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $aantal = count($nummers);
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($playlistNaam !== '' ? $playlistNaam : 'Nieuwe playlist'); ?></title>
    <link rel="stylesheet" href="stylehome.css?v=<?php echo filemtime('stylehome.css'); ?>">
    <style>
        .nummer-verwijder {
            background: none;
            border: none;
            color: #b3b3b3;
            font-size: 20px;
            cursor: pointer;
            padding: 0 10px;
            line-height: 1;
        }
        .nummer-verwijder:hover {
            color: #fff;
        }
    </style>
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
            <a href="playlist.php?naam=<?php echo urlencode($fav['naam']); ?>" class="sidebar-fav <?php echo $fav['naam'] === $playlistNaam ? 'actief' : ''; ?>" title="<?php echo htmlspecialchars($fav['naam']); ?>">
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

<?php if ($playlistNaam === ''): ?>
    <div class="playlist-header">
        <div class="playlist-header-info">
            <span class="playlist-type">Nieuw</span>
            <h1>Playlist aanmaken</h1>
            <form method="POST" class="playlist-form">
                <input type="text" name="playlist_naam" placeholder="Playlist naam">
                <button type="submit" name="maak_playlist" class="favoriet-knop">Aanmaken</button>
            </form>
            <?php if ($melding): ?>
                <span class="upload-melding"><?php echo htmlspecialchars($melding); ?></span>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="playlist-header">
        <form method="POST" enctype="multipart/form-data" class="cover-form">
            <label for="cover-input" class="playlist-header-foto">
                <?php if ($foto): ?>
                    <img src="<?php echo htmlspecialchars($foto); ?>?v=<?php echo filemtime($foto); ?>" alt="">
                <?php else: ?>
                    <div class="playlist-foto-placeholder groot">♪</div>
                <?php endif; ?>
                <div class="cover-hint">Kies foto</div>
            </label>
            <input type="file" id="cover-input" name="cover" accept="image/*" onchange="this.form.submit()" hidden>
        </form>
        <div class="playlist-header-info">
            <span class="playlist-type">Playlist</span>
            <h1><?php echo htmlspecialchars($playlistNaam); ?></h1>
            <span class="playlist-aantal">
                <?php echo $aantal . ($aantal === 1 ? ' nummer' : ' nummers'); ?>
            </span>
            <?php if ($melding): ?>
                <span class="upload-melding"><?php echo htmlspecialchars($melding); ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="playlist-acties">
        <form method="POST" class="favoriet-form">
            <button type="submit" name="toggle_favoriet" class="favoriet-knop <?php echo $isFavoriet ? 'actief' : ''; ?>" aria-label="Favoriet">
                <svg viewBox="0 0 24 24" fill="<?php echo $isFavoriet ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="1.8" width="22" height="22">
                    <path d="M12 21s-8-5-8-11a5 5 0 0 1 9-3 5 5 0 0 1 9 3c0 6-8 11-8 11z" />
                </svg>
            </button>
        </form>
    </div>

    <?php if (empty($nummers)): ?>
        <p class="leeg-melding">Nog geen nummers in deze playlist.</p>
    <?php else: ?>
        <div class="nummer-lijst">
            <?php $i = 1; foreach ($nummers as $n): ?>
                <div class="nummer-rij">
                    <span class="nummer-index"><?php echo $i++; ?></span>
                    <button class="nummer-play" aria-label="Play">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                            <path d="M8 5v14l11-7z" />
                        </svg>
                    </button>
                    <div class="nummer-info">
                        <span class="nummer-titel"><?php echo htmlspecialchars($n['nummer']); ?></span>
                        <span class="nummer-artiest"><?php echo htmlspecialchars($n['artiest_naam'] ?? 'Onbekend'); ?></span>
                    </div>
                    <?php if (!empty($n['duur'])): ?>
                        <span class="nummer-duur"><?php echo htmlspecialchars($n['duur']); ?></span>
                    <?php endif; ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="nummer_id" value="<?php echo (int)$n['id']; ?>">
                        <button type="submit" name="verwijder_nummer" class="nummer-verwijder" aria-label="Verwijder nummer">×</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

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