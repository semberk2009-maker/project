<?php
require_once("conn.php");

$nummer    = $_GET['nummer']    ?? '';
$artiestid = $_GET['artiestid'] ?? '';
$melding   = '';

// Haal alle tabellen op uit de database
$tabellen = [];
$result = $pdo->query("SHOW TABLES");
while ($row = $result->fetch(PDO::FETCH_NUM)) {
    $tabellen[] = $row[0];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Bepaal de tabelnaam: of de gekozen bestaande, of de nieuwe
    $tabelNaam = '';
    if (!empty($_POST['bestaande_tabel'])) {
        $tabelNaam = preg_replace("/[^a-zA-Z0-9_]/", "", $_POST['bestaande_tabel']);
    } elseif (!empty($_POST['nieuwe_tabel'])) {
        $tabelNaam = preg_replace("/[^a-zA-Z0-9_]/", "", $_POST['nieuwe_tabel']);
    }

    if ($tabelNaam === '') {
        $melding = "Kies een tabel of typ een nieuwe naam!";
    } else {
        $nummer    = $_POST['nummer'];
        $artiestid = $_POST['artiestid'];

        // Bestaat de tabel?
        $check = $pdo->prepare("SHOW TABLES LIKE ?");
        $check->execute([$tabelNaam]);

        if (!$check->fetch()) {
            // Tabel bestaat niet -> maak hem aan
            $pdo->exec("CREATE TABLE `$tabelNaam` (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nummer VARCHAR(255) NOT NULL,
                artiestid INT NOT NULL
            )");
        }

        // Voeg het nummer toe
        $stmt = $pdo->prepare("INSERT INTO `$tabelNaam` (nummer, artiestid) VALUES (?, ?)");
        $stmt->execute([$nummer, $artiestid]);

        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Nummer toevoegen</title>
    <style>
        body { font-family: sans-serif; padding: 20px; max-width: 500px; margin: 0 auto; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input[type="text"], select { padding: 5px; font-size: 16px; width: 100%; box-sizing: border-box; }
        button { padding: 6px 12px; font-size: 16px; margin-top: 10px; cursor: pointer; }
        .scheiding { margin: 20px 0; text-align: center; color: #888; }
    </style>
</head>
<body>

<h1>Nummer toevoegen</h1>

<p>Nummer: <strong><?php echo htmlspecialchars($nummer); ?></strong></p>
<p>Artiest ID: <strong><?php echo htmlspecialchars($artiestid); ?></strong></p>

<?php if ($melding): ?>
    <p style="color:red;"><?php echo htmlspecialchars($melding); ?></p>
<?php endif; ?>

<form method="POST">
    <input type="hidden" name="nummer" value="<?php echo htmlspecialchars($nummer); ?>">
    <input type="hidden" name="artiestid" value="<?php echo htmlspecialchars($artiestid); ?>">

    <label for="bestaande_tabel">Kies een bestaande tabel:</label>
    <select name="bestaande_tabel" id="bestaande_tabel">
        <option value="">-- Geen --</option>
        <?php foreach ($tabellen as $t): ?>
            <option value="<?php echo htmlspecialchars($t); ?>">
                <?php echo htmlspecialchars($t); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <div class="scheiding">— OF —</div>

    <label for="nieuwe_tabel">Maak een nieuwe tabel:</label>
    <input type="text" name="nieuwe_tabel" id="nieuwe_tabel" placeholder="Nieuwe tabelnaam...">

    <button type="submit">Toevoegen</button>
</form>

<p><a href="index.php">Terug</a></p>

</body>
</html>