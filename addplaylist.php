<?php
require_once("conn.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!empty($_POST['naam']) && !empty($_POST['artiest'])) {
        $tabelNaam = preg_replace("/[^a-zA-Z0-9_]/", "", $_POST["naam"]);
        $artiestNaam = $_POST["artiest"];

        $stmt = $pdo->prepare("SELECT artiest_id FROM artiesten WHERE naam = ?");
        $stmt->execute([$artiestNaam]);
        $artiest = $stmt->fetch();

        if (!$artiest) {
            die("Artiest niet gevonden!");
        }

        $artiestId = $artiest['artiest_id'];

        $sql = "CREATE TABLE `$tabelNaam` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nummer VARCHAR(255) NOT NULL,
            artiestid INT NOT NULL
        )";

        if ($pdo->exec($sql)) {
            // Optioneel: meteen een rij invoegen met de artiestid?
            // $pdo->exec("INSERT INTO `$tabelNaam` (nummer, artiestid) VALUES ('', $artiestId)");
            
            header("Location: index.php");
            exit();
        } else {
            echo "Fout bij aanmaken tabel.";
        }
    } else {
        echo "Tabelnaam en artiest zijn verplicht!";
    }
}