<?php
$error = null;
// se è stato inviato il form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // recupero il codice azienda (la partita IVA), l'username interno e la password
    $company_code = $_POST['company-code'];
    $internal_username = $_POST['internal-username'];
    $password = $_POST['password'];
    // includo il file database.php per connettermi al database
    include 'business_logic/database.php';
    // preparo la query per selezionare l'utente con il codice azienda e l'username interno specificati
    $query = 'SELECT users.id, users.username, users.password, users.confirmed FROM users JOIN companies ON users.company_id = companies.id WHERE companies.vat_id = ? AND users.username = ?';
    $stmt = mysqli_prepare($conn, $query);
    // collego i parametri alla query
    mysqli_stmt_bind_param($stmt, 'ss', $company_code, $internal_username);
    // eseguo la query
    mysqli_stmt_execute($stmt);
    // memorizzo il risultato della query
    mysqli_stmt_store_result($stmt);
    // se l'utente è stato trovato
    if (mysqli_stmt_num_rows($stmt) > 0) {
        // collego i risultati della query alle variabili
        mysqli_stmt_bind_result($stmt, $user_id, $username, $hashed_password, $confirmed);
        // recupero i risultati della query
        mysqli_stmt_fetch($stmt);
        // verifico che la password inserita corrisponda a quella nel database
        if (password_verify($password, $hashed_password)) {
            // se l'utente è confermato
            if ($confirmed) {
                // avvio una sessione
                session_start();
                // salvo l'id dell'utente in una variabile di sessione
                $_SESSION['user_id'] = $user_id;
                // reindirizzo l'utente alla dashboard
                header('Location: index.php');
                exit;
            } else {
                $error = 'Utente non confermato';
            }
        } else {
            $error = 'Utente non trovato o password errata';
        }
    } else {
        $error = 'Utente non trovato o password errata';
    }
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkmate Login</title>
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

        .product-image {
            max-width: 100%;
            height: auto;
        }

        .register-link {
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
        <img src="assets/logo_text.png" alt="Checkmate Product Image" class="product-image">
        <h1 style="text-align: center;">Login</h1>
        <form method="post">
            <?php if ($error) : ?>
                <div style="background-color: #ff4136; color: #ffffff; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="company-code">Codice Azienda</label>
                <input type="text" id="company-code" name="company-code" required>
            </div>
            <div class="form-group">
                <label for="internal-username">Username Interno</label>
                <input type="text" id="internal-username" name="internal-username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <input type="submit" value="Login">
            </div>
        </form>
        <div class="register-link">
            <a href="register.php">Registrati come nuova azienda</a>
        </div>
    </div>
</body>

</html>