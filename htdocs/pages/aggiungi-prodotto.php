<?php
// esci se chiamato direttamente
if (!defined('INSIDE_DASHBOARD')) {
    die('Errore: accesso diretto negato');
}
// se il form è stato inviato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // eseguo una query per inserire il prodotto (inteso come categoria)
    // la tabella categories è così costruita (con foreign key company_id che fa riferimento alla tabella companies):
    /*
    CREATE TABLE categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        default_metadata JSON NOT NULL DEFAULT '{}',
        cached_last_lot_id VARCHAR(255) NOT NULL,
        cached_qty_last_lot INT NOT NULL,
        cached_sold_last_month INT NOT NULL,
        FOREIGN KEY (company_id) REFERENCES companies(id)
    );
    */
    $query = 'INSERT INTO categories (company_id, name, default_metadata, cached_last_lot_id, cached_qty_last_lot, cached_sold_last_month) VALUES (?, ?, ?, ?, ?, ?)';
    $stmt = mysqli_prepare($conn, $query);
    $default_metadata = [];
    if (isset($_POST['metadata_name']) && isset($_POST['metadata_value'])) {
        foreach ($_POST['metadata_name'] as $index => $name) {
            $default_metadata[$name] = $_POST['metadata_value'][$index];
        }
    }
    $cached_last_lot_id = '';
    $cached_qty_last_lot = 0;
    $cached_sold_last_month = 0;
    $default_metadata = json_encode($default_metadata);
    mysqli_stmt_bind_param($stmt, 'isssii', $company['id'], $_POST['name'], $default_metadata, $cached_last_lot_id, $cached_qty_last_lot, $cached_sold_last_month);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    // reindirizzo alla pagina del catalogo
    ?>
    <script>
        window.location = 'index.php?page=catalogo';
    </script>
    <?php
    exit;
}
?><h1>Aggiungi prodotto</h1>
<!-- form per aggiungere un prodotto, il form ha il seguente campo obbligatorio: nome, offre inoltre la possibilità di aggiungere metadati aggiuntivi -->
<form action="index.php?page=aggiungi-prodotto" method="post" style="margin: 0 20%; display: flex; flex-direction: column;">
    <input type="text" id="name" name="name" required placeholder="Nome prodotto">
    <div id="metadata">
    </div>
    <div style="display: flex; justify-content: space-between; margin-top: 1em;">
        <button type="button" id="addMetadata" class="btn">Aggiungi metadato</button>
        <button type="submit" class="btn primary" style="margin-left: 1em;">Salva</button>
    </div>
</form>
<script>
    document.getElementById('addMetadata').addEventListener('click', function() {
        var metadata = document.getElementById('metadata');
        // aggiungo un div con un input per il nome del metadato e un input per il valore del metadato di default e un pulsante per rimuovere il metadato
        var div = document.createElement('div');
        div.style.display = 'flex'; // Add this line to make the row occupy all available space
        div.style.marginTop = '1em'; // Add this line to add some space between the rows
        var name = document.createElement('input');
        name.type = 'text';
        name.name = 'metadata_name[]';
        name.placeholder = 'Nome metadato';
        name.style.flex = '1'; // Add this line to make the input field occupy all available space
        name.style.borderTopRightRadius = '0';
        name.style.borderBottomRightRadius = '0';
        name.style.backgroundColor = '#fffb';
        var value = document.createElement('input');
        value.type = 'text';
        value.name = 'metadata_value[]';
        value.placeholder = 'Valore metadato';
        value.style.flex = '1'; // Add this line to make the input field occupy all available space
        value.style.borderRadius = '0';
        var remove = document.createElement('button');
        remove.type = 'button';
        remove.textContent = 'Rimuovi';
        remove.classList.add('btn');
        remove.classList.add('danger');
        remove.style.minWidth = '0'; // Add this line to make the button occupy the minimum space
        remove.style.borderTopLeftRadius = '0';
        remove.style.borderBottomLeftRadius = '0';
        remove.addEventListener('click', function() {
            metadata.removeChild(div);
        });
        div.appendChild(name);
        div.appendChild(value);
        div.appendChild(remove);
        metadata.appendChild(div);
    });
</script>