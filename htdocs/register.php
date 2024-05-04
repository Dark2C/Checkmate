<?php
$error = null;
// se è stato inviato il form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // recupero i dati inviati
    $partita_iva = $_POST['partita-iva'];
    $password = $_POST['password'];
    // la partita iva deve essere valida
    function controllaPartitaIVA($pi)
    {
        if (strlen($pi) != 11) return false;
        elseif (preg_match("/^[0-9]+\$/D", $pi) != 1) return false;
        else {
            $s = $c = 0;
            for ($i = 0; $i <= 9; $i += 2) {
                $s += ord($pi[$i]) - ord('0');
            }
            for ($i = 1; $i <= 9; $i += 2) {
                $c = 2 * (ord($pi[$i]) - ord('0'));
                if ($c > 9) $c = $c - 9;
                $s += $c;
            }
            $controllo = (10 - $s % 10) % 10;
            return !($controllo != (ord($pi[10]) - ord('0')));
        }
    }
    if (!controllaPartitaIVA($partita_iva)) {
        $error = 'Partita IVA non valida';
    } else {
        $url = 'https://html.duckduckgo.com/html/?q=%2B%22' . $partita_iva . '%22%20site%3Awww.ufficiocamerale.it';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:124.0) Gecko/20100101 Firefox/124.0');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language: it-IT,it;q=0.8,en-US;q=0.5,en;q=0.3',
            'DNT: 1',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
            'Sec-Fetch-Dest: document',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: cross-site'
        ]);
        $html = curl_exec($ch);
        curl_close($ch);
        if (strpos($html, 'result__a') === false) {
            $error = 'Partita IVA non trovata';
        } else {
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);
            $href = $xpath->query('//a[@class="result__a"]')->item(0)->getAttribute('href');
            $href = substr($href, 25);
            $href = explode('&', $href)[0];
            $href = urldecode($href);
            $html = file_get_contents($href);
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);
            $json = $xpath->query('//script[@type="application/ld+json"]')->item(1)->nodeValue;
            $data = json_decode($json, true);
            // includeo business_logic/database.php per connettermi al database
            include 'business_logic/database.php';
            $query = 'SELECT * FROM companies WHERE vat_id = ?';
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 's', $data['vatID']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $error = 'Partita IVA già registrata';
            } else {
                // l'email è obbligatoria, se non è presente in data allora restituisco un errore
                if (trim($data['email'] || '') === '') {
                    $error = 'Email non trovata per la partita IVA inserita';
                } else {
                    // inserisco i dati nel database
                    // avvio una transazione
                    mysqli_begin_transaction($conn);
                    // inserisco l'azienda
                    $query = 'INSERT INTO companies (email, name, legal_name, vat_id, founding_date, founding_location, address_locality, address_region, postal_code, street_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
                    // la tabella companies è stata creata con il seguente schema:
                    /*
                    CREATE TABLE companies (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        email VARCHAR(255) NOT NULL,
                        name VARCHAR(255) NOT NULL,
                        legal_name VARCHAR(255) NOT NULL,
                        vat_id VARCHAR(11) NOT NULL,
                        founding_date DATE NOT NULL,
                        founding_location VARCHAR(255) NOT NULL,
                        address_locality VARCHAR(255) NOT NULL,
                        address_region VARCHAR(255) NOT NULL,
                        postal_code VARCHAR(5) NOT NULL,
                        street_address VARCHAR(255) NOT NULL
                    );
                    */
                    $stmt = mysqli_prepare($conn, $query);
                    $password = password_hash($password, PASSWORD_DEFAULT);
                    mysqli_stmt_bind_param($stmt, 'ssssssssss', $data['email'], $data['name'], $data['legalName'], $data['vatID'], $data['foundingDate'], $data['foundingLocation'], $data['address']['addressLocality'], $data['address']['addressRegion'], $data['address']['postalCode'], $data['address']['streetAddress']);
                    if (mysqli_stmt_execute($stmt)) {
                        // inserisco l'utente admin dell'azienda nella tabella users
                        $company_id = mysqli_insert_id($conn);
                        $query = 'INSERT INTO users (company_id, username, password, role, confirmed) VALUES (?, ?, ?, ?, 0)';
                        // la tabella users è stata creata con il seguente schema: (compreso di foreign key company_id che fa riferimento alla tabella companies)
                        /*
                        CREATE TABLE users (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            company_id INT NOT NULL,
                            username VARCHAR(255) NOT NULL,
                            password VARCHAR(255) NOT NULL,
                            role VARCHAR(255) NOT NULL,
                            confirmed BOOLEAN NOT NULL,
                            FOREIGN KEY (company_id) REFERENCES companies(id)
                        );
                        */
                        $stmt = mysqli_prepare($conn, $query);
                        $role = 'admin';
                        mysqli_stmt_bind_param($stmt, 'isss', $company_id, $role, $password, $role);
                        if (mysqli_stmt_execute($stmt)) {
                            // confermo la transazione
                            mysqli_commit($conn);
                            // salvo l'id dell'azienda appena registrata in una variabile di sessione
                            session_start();
                            $_SESSION['company_id'] = $company_id;
                            header('Location: confirm.php');
                            exit;
                        } else {
                            $error = 'Errore durante la registrazione';
                        }
                    } else {
                        $error = 'Errore durante la registrazione';
                    }
                    mysqli_rollback($conn);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione Account Aziendale - Checkmate</title>
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
        <h1 style="text-align: center;">Registrazione</h1>
        <form method="post">
            <?php if ($error) : ?>
                <div style="background-color: #ff0000; color: #ffffff; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="partita-iva">Partita IVA</label>
                <input type="text" id="partita-iva" name="partita-iva" required pattern="[0-9]{11}">
            </div>
            <div class="form-group">
                <label for="password">Password (admin)</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <input type="submit" value="Registrati">
            </div>
            <div class="login-link">
                <a href="login.php">Hai già un account? Accedi qui</a>
            </div>
        </form>
    </div>
</body>

</html>