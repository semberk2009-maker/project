<?php
// Start de sessie (nodig om sessiegegevens te kunnen lezen)
session_start();

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    // Geen sessie? Stuur door naar account.php
    header('Location: account.php');
    exit; // Belangrijk: stop de uitvoering van de rest van de code
}

// Als we hier komen, is de gebruiker ingelogd
// Je kunt hier eventueel meer controles toevoegen
?>