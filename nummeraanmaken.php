<?php
require_once("conn.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $artiest = $_POST['artiest'];
    $nummer  = $_POST['nummer'];
    $duur    = $_POST['duur'] ?? '';

    $stmt = $pdo->prepare("SELECT artiest_id FROM artiesten WHERE naam = ?");
    $stmt->execute([$artiest]);
    $artiestId = $stmt->fetchColumn();

    if ($artiestId) {
        $stmt = $pdo->prepare("INSERT INTO nummers (artiest_id, naam, duur) VALUES (?, ?, ?)");
        $stmt->execute([$artiestId, $nummer, $duur]);

        header("Location: nummeraanmaken.php?succes=1");
        exit();
    } else {
        header("Location: nummeraanmaken.php?error=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Nummer aanmaken</title>
    <link rel="stylesheet" href="stylehome.css?v=<?php echo filemtime('stylehome.css'); ?>">
</head>
<body>

    <h1>Nummer aanmaken</h1>

    <?php if (isset($_GET['succes'])): ?>
        <p style="color: #1ed760;">Nummer toegevoegd!</p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <p style="color: #f87171;">Artiest niet gevonden.</p>
    <?php endif; ?>

    <form method="POST" class="nummer-form">
        <label>Artiest:</label>
        <input type="text" name="artiest" required>

        <label>Nummer:</label>
        <input type="text" name="nummer" required>

        <label>Duur (bv. 3:45):</label>
        <input type="text" name="duur" placeholder="3:45" pattern="[0-9]{1,2}:[0-9]{2}" title="Formaat: m:ss of mm:ss">

        <button type="submit">Toevoegen</button>
    </form>

</body>
</html>