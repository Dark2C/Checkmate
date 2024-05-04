<?php
session_start();
if (isset($_GET['logout'])) {
    session_destroy();
    $_SESSION = null;
}
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include 'business_logic/database.php';
// seleziono l'ogggetto utente con l'id dell'utente loggato e lo salvo in $user
$query = 'SELECT * FROM users WHERE id = ?';
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
// seleziono la compagnia dell'utente loggato e la salvo in $company
$query = 'SELECT * FROM companies WHERE id = ?';
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user['company_id']);
mysqli_stmt_execute($stmt);
$company = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Checkmate</title>
    <link rel="icon" href="assets/icon.png" type="image/png">
    <style>
        body {
            background-color: #1a1a1a;
            color: #ffffff;
            font-family: Arial, sans-serif;
            padding: 0;
            margin: 0;
        }

        .container {
            display: flex;
            justify-content: space-between;
        }

        .menu {
            flex-basis: 10%;
            background-color: #333333;
            padding: 20px;
            /* altezza a schermo intero */
            height: 100vh;

            img {
                width: 100%;
                margin-bottom: 20px;
            }

            ul {
                list-style-type: none;
                padding: 0;
                margin: 0;
            }

            ul li {
                margin: 0 -20px;
                padding: 10px 20px;

                &.active {
                    background-color: #444444;
                }

                &:not(.active):hover {
                    background-color: #3B3B3B;
                }
            }

            ul li a {
                color: #ffffff;
                text-decoration: none;
                display: block;
                margin: -20px;
                padding: 20px;
            }
        }


        .content {
            flex-basis: 90%;
            padding: 20px;
            border: none;
            height: 100vh;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #44444444;
            border-radius: 10px;
            overflow: hidden;

            thead {
                color: #ffffff;

                th {
                    padding: 10px;
                }

                th:nth-child(odd) {
                    background-color: #3B3B3B40;
                }
            }

            tbody {

                /* rows of alternating colors */
                tr:nth-child(odd) {
                    background-color: #3B3B3B40;
                }

                td {
                    padding: 10px;
                }

                td:nth-child(odd) {
                    background-color: #3B3B3B40;
                }

                tr:hover {
                    background-color: #3B3B3BA0;
                }
            }
        }

        .btn {
            --curr-bg: #ddd;
            background-color: var(--curr-bg);
            color: #111;
            padding: 10px;
            border: none;
            cursor: pointer;
            display: inline-block;
            text-decoration: none;
            border-radius: 10px;

            &.primary {
                --curr-bg: #0074d9;
                color: #ffffff;
            }
            &.danger {
                --curr-bg: #ff4136;
                color: #ffffff;
            }

            &:hover {
                background-color: color-mix(in srgb, var(--curr-bg) 50%, #fff 25%);
            }

            &:active {
                background-color: color-mix(in srgb, var(--curr-bg) 50%, #000 25%);
            }
        }
        input[type="text"] {
            padding: 8px;
            border-radius: 5px;
            border: 0;
            background-color: #fffd;
        }
    </style>
</head>

<body>
    <!-- menu a sinistra con le voci: Catalogo, Utenti, Segnalazioni, Profilo, Logout -->

    <body>
        <div class="container">
            <div class="menu">
                <img src="assets/logo_text.png" alt="Checkmate" class="product-image">
                <!-- Left menu items -->
                <ul>
                    <li id="menu_catalogo"><a href="index.php?page=catalogo">Catalogo</a></li>
                    <li id="menu_utenti"><a href="index.php?page=utenti">Utenti</a></li>
                    <li id="menu_segnalazioni"><a href="index.php?page=segnalazioni">Segnalazioni</a></li>
                    <li id="menu_profilo"><a href="index.php?page=profilo">Profilo</a></li>
                    <li id="menu_logout"><a href="?logout">Logout</a></li>
                </ul>
            </div>
            <div class="content">
                <?php
                $allowed_pages = ['catalogo', 'utenti', 'segnalazioni', 'profilo', 'aggiungi-prodotto'];
                $page = $_GET['page'] ?? 'catalogo';
                if (!in_array($page, $allowed_pages)) {
                    $page = 'catalogo';
                }
                define('INSIDE_DASHBOARD', true);
                include 'pages/' . $page . '.php';
                ?><script>
                    document.getElementById('menu_<?php echo $page; ?>').classList.add('active');
                </script>
            </div>
        </div>
    </body>
</body>

</html>