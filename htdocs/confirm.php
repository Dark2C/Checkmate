<?php
// apro la sessione
session_start();
if (!isset($_SESSION['company_id'])) {
    header('Location: index.php');
    exit;
}
// mi collego al database e restituisco l'oggetto connessione
include 'business_logic/database.php';
// recupero i dati dell'azienda
$query = 'SELECT email, name, legal_name, vat_id, founding_date, founding_location, address_locality, address_region, postal_code, street_address FROM companies WHERE id = ?';

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['company_id']);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
mysqli_stmt_bind_result($stmt, $email, $name, $legal_name, $vat_id, $founding_date, $founding_location, $address_locality, $address_region, $postal_code, $street_address);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// cancello la variabile di sessione company_id
unset($_SESSION['company_id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione Account Aziendale completata con successo - Checkmate</title>
    <link rel="icon" href="assets/icon.png" type="image/png">
    <style>
        body {
            background-color: #1a1a1a;
            color: #ffffff;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #333333;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #444444;
            border-radius: 5px;
            background-color: #1a1a1a;
            color: #ffffff;
        }

        .form-group input[type="submit"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #0074d9;
            color: #ffffff;
            cursor: pointer;
        }

        .form-group input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 100%;
            height: auto;
        }

        .login-link {
            text-align: center;
            margin-top: 10px;

            a {
                color: #0074d9;
                text-decoration: none;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo">
            <img src="assets/logo_text.png" alt="Checkmate Product Image">
        </div>
        <h1 style="text-align: center;">Registrazione completata con successo!</h1>
        <p>Di seguito sono riportati i dati dell'azienda che hai appena registrato:</p>
        <ul>
            <li><strong>Email:</strong> <?php echo htmlentities($email); ?></li>
            <li><strong>Nome:</strong> <?php echo htmlentities($name); ?></li>
            <li><strong>Ragione Sociale:</strong> <?php echo htmlentities($legal_name); ?></li>
            <li><strong>Partita IVA:</strong> <?php echo htmlentities($vat_id); ?></li>
            <li><strong>Data di Fondazione:</strong> <?php echo htmlentities($founding_date); ?></li>
            <li><strong>Sede di Fondazione:</strong> <?php echo htmlentities($founding_location); ?></li>
            <li><strong>Località:</strong> <?php echo htmlentities($address_locality); ?></li>
            <li><strong>Regione:</strong> <?php echo htmlentities($address_region); ?></li>
            <li><strong>CAP:</strong> <?php echo htmlentities($postal_code); ?></li>
            <li><strong>Indirizzo:</strong> <?php echo htmlentities($street_address); ?></li>
        </ul>
        <p style="text-align: center;">Per completare la registrazione, segui il link che ti abbiamo inviato via email.</p>
        <div class="login-link">
            <a href="login.php">Effettua il login</a>
        </div>
    </div>
</body>

</html>