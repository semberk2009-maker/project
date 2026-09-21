<?php
require_once('conn.php');

$zoek_artiest = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zoek_artiest = $_POST['artiest'] ?? '';
}

$query = "SELECT a.artiest_id, a.naam AS artiest_naam, n.naam AS nummer_naam 
          FROM artiesten a
          LEFT JOIN nummers n ON a.artiest_id = n.artiest_id";

if (!empty($zoek_artiest)) {
    $query .= " WHERE a.naam LIKE :zoekterm";
}

$query .= " ORDER BY a.naam, n.naam";

$stmt = $pdo->prepare($query);

if (!empty($zoek_artiest)) {
    $stmt->bindValue(':zoekterm', '%' . $zoek_artiest . '%');
}

$stmt->execute();
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artiest & Nummers</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Styling voor het navigatiemenu */
        nav {
            margin-bottom: 20px;
        }

        nav a {
            margin-right: 15px;
            text-decoration: underline;
        }

        form {
            margin-bottom: 30px;
        }

        input[type="text"] {
            padding: 5px;
            font-size: 16px;
        }

        button {
            padding: 5px 10px;
            font-size: 16px;
            cursor: pointer;
        }

        .box {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid;
        }

        .nummer-header {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .spec-item {
            margin-bottom: 2px;
        }

        .spec-label {
            display: inline-block;
            width: 100px;
        }
    </style>
</head>

<body>

    <h1>Muziek Bibliotheek</h1>

    <!-- Navigatie links toegevoegd -->
    <nav>
        <a href="artiestaanmaken.php">Artiest Aanmaken</a>
        <a href="nummeraanmaken.php">Nummer Aanmaken</a>
    </nav>

    <form method="POST" action="">
        <input type="text" name="artiest" placeholder="Zoek artiest..." value="<?php echo htmlspecialchars($zoek_artiest); ?>">
        <button type="submit">Zoeken</button>
    </form>

    <div>
        <?php
        $heeft_resultaten = false;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
            if ($row['nummer_naam'] !== null):
                $heeft_resultaten = true;
        ?>
                <div class="nummer-header">
                    <a href="toevoegen.php?nummer=<?php echo urlencode($row['nummer_naam']); ?>&artiestid=<?php echo urlencode($row['artiest_id']); ?>">
                        <?php echo htmlspecialchars($row['nummer_naam']); ?>
                    </a>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Artiest ID:</span>
                    <span class="spec-value"><?php echo htmlspecialchars($row['artiest_id']); ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Artiest:</span>
                    <span class="spec-value"><?php echo htmlspecialchars($row['artiest_naam']); ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Titel:</span>
                    <span class="spec-value"><?php echo htmlspecialchars($row['nummer_naam']); ?></span>
                </div>
    </div>
<?php
            endif;
        endwhile;

        if (!$heeft_resultaten) {
            echo '<p>Geen nummers gevonden.</p>';
        }
?>
</div>

</body>

</html>