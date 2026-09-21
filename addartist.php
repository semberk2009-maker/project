<?php
require_once("conn.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!empty($_POST['artiestnaam'])) {
        $naam = $_POST["artiestnaam"];
        
       $sql = "INSERT INTO artiesten (naam) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$naam])) {
            header("Location: artiestaanmaken.php");
            exit();
        } else {
            echo "Fout bij toevoegen: ";
            print_r($stmt->errorInfo());
        }
    } else {
        echo "Artiestnaam is verplicht!";
    }
}
?>