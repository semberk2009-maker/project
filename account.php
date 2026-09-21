<?php
// Sessie starten helemaal bovenaan zodat header() later werkt
session_start();

// connectie naar database opzetten //
require_once('conn.php');

$email = "";
$username = "";
$password = "";
$allesgoed = false;
$succes_gemaakt = false;

// Checken of de gebruiker al ingelogd is
$is_ingelogd = isset($_SESSION['user_id']);

// Als de gebruiker al ingelogd is: NIETS in de database stoppen
// We slaan de hele POST-verwerking over
if (!$is_ingelogd && $_SERVER["REQUEST_METHOD"] === "POST") {

    // If statement om te checken of username bestaat
    if (isset($_POST["username"])) {
        // In een variabel stoppen
        $username = $_POST["username"];
        // Patern aanmaken
        $patern = "/[A-Z]/i";
        // allesgoed naar true zetten zodat het weet of je username goed is
        $allesgoed = true;
        if (preg_match($patern, $username)) {
        } else {
            // Style veranderen van username naar rood
            echo "<style>#username{color: red;}</style>";
            // Allesgoed naar false zetten als het fout is
            $allesgoed = false;
        };
    };
    // if statement om te checken of de email bestaat
    if (isset($_POST["email"])) {
        // In een variabel stoppen
        $email = $_POST["email"];
        // If statement om te checken of de email een email is
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // allesgoed op true laten als het nog niet false is
            $allesgoed = ($allesgoed !== false);
        } else {
            // Style veranderen van username naar rood
            echo ("<style>#email{color: red;}</style>");
            // Allesgoed naar false zetten als het fout is
            $allesgoed = false;
        };
    }
    // if statement om te checken of de wachtwoord bestaat
    if (isset($_POST["password"])) {
        // In een variabel stoppen
        $password = $_POST["password"];
        // Patern checken met wachtwoord dus 5 tekens teminste en teminste 1 speciaal teken
        if (preg_match('/^(?=.*[^a-zA-Z0-9]).{5,}$/', $password)) {
            // allesgoed op true laten als het nog niet false is
            $allesgoed = ($allesgoed !== false);
        } else {
            // Style veranderen van username naar rood
            echo "<style>#ww{color:red;}</style>";
            // Allesgoed naar false zetten als het fout is
            $allesgoed = false;
        };
    };

    // Checken of de requestmethod post is 
    if ($allesgoed === true) {
        // Alle antwoorden in een variabelen zetten
        $user_input = $_POST['username'] ?? '';
        $email_input = $_POST['email'] ?? '';
        $pass_input = $_POST['password'] ?? '';
        // Checken of de inputs leeg zijn
        if (empty($user_input) || empty($email_input) || empty($pass_input)) {
            // Feedback geven als ze empty zijn
            echo "<div class='error-msg'>Vul alle velden in</div>";
            echo "<style>#username, #email, #ww { color: red; }</style>";
        } else {
            // Query aanmaken, preparen en executen
            // Query aanmaken die de aantal rows selecteerd waar het naam hetzelfde is uit accounts
            $stmtselect = $conn->prepare("SELECT COUNT(*) FROM accounts WHERE Naam = ?");
            $stmtselect->bind_param("s", $user_input);
            $stmtselect->execute();
            // Het result oppakken
            $result = $stmtselect->get_result();
            // De rows oppakken
            $row = $result->fetch_row();
            // Aantal aanmaken
            $aantal = $row[0];
            // Stmt closen
            $stmtselect->close();
            // If statement maken om te checken of er al een account bestaat met dezelfde naam
            if ($aantal > 0) {
                // Feedback geven als het al bestaat
                echo "<div class='error-msg'>Dit account bestaat al.</div>";
                echo "<style>#username{color: red;}</style>";
            } else {
                // Anders het password hashen
                $safe_password = password_hash($pass_input, PASSWORD_DEFAULT);
                // Query aanmaken om het account in accounts te stoppen
                $stmt = $conn->prepare("INSERT INTO accounts (naam, email, wachtwoord) VALUES (?,?,?)");
                // Parameters binden
                $stmt->bind_param("sss", $user_input, $email_input, $safe_password);
                // Statement executen
                if ($stmt->execute()) {
                    // Sessie aanmaken voor de nieuwe gebruiker
                    session_regenerate_id(true); // voorkomt session fixation
                    $_SESSION['user_id']  = $stmt->insert_id;
                    $_SESSION['username'] = $user_input;

                    // Doorsturen naar index.php
                    header('Location: index.php');
                    exit;
                } else {
                    // Anders feedback geven een de error
                    echo "<div class='error-msg'>Fout bij opslaan: " . $stmt->error . "</div>";
                    // Style veranderen naar rood
                    echo "<style>#username, #email, #ww { color: red; }</style>";
                }
                // Statement afsluiten
                $stmt->close();
            }
        }
    }

    // Checken of het niet goed is met een if statement
    if ($allesgoed === false && (empty($username) || empty($email) || empty($password))) {
        // Feedback geven
        echo "<div class='error-msg'>Vul alle velden in</div>";
        // Style veranderen naar rood
        echo "<style>#username, #email, #ww { color: red; }</style>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>account aanmaken</title>
    <link rel="stylesheet" href="account.css">
    <?php if ($succes_gemaakt): ?>
        <style>
            #username,
            #email,
            #ww {
                color: green !important;
            }
        </style>
    <?php endif; ?>
</head>

<body>
    <?php if ($is_ingelogd): ?>
        <!-- Pijl linksboven: alleen zichtbaar als je ingelogd bent -->
        <a href="index.php" class="back-arrow" aria-label="Terug naar index">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
        </a>
    <?php endif; ?>

    <?php
    // Checken of de email goed is
    if (!$is_ingelogd && $_SERVER["REQUEST_METHOD"] === "POST" && !filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)): ?>
        <div class='error-msg'>Ongeldig e-mailadres ingevoerd</div>
    <?php endif; ?>

    <form method="post">
        <input id="username" class="username" placeholder="Username" name="username" type="text" value="<?php echo $username ?>">
        <input id="email" class="email" placeholder="Email" name="email" type="text" value="<?php echo $email ?>">
        <input id="ww" class="password" placeholder="Password" name="password" type="password" value="<?php echo $password ?>">
        <input class="button" type="submit" value="Login">
        <!-- <button type="submit" name="delete_all">Verwijder ALLE Accounts</button> -->
    </form>

    <?php
    // if (isset($_POST['delete_all'])) {
    //     $sql = "TRUNCATE TABLE accounts";

    //     if ($conn->query($sql) === TRUE) {
    //         echo "<div class='success-msg'>De tabel is leeg</div>";
    //     } else {
    //         echo "<div class='error-msg'>Er ging iets mis: " . $conn->error . "</div>";
    //     }
    // }
    ?>
</body>

</html>