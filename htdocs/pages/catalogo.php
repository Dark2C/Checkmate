<?php
// esci se chiamato direttamente
if (!defined('INSIDE_DASHBOARD')) {
    die('Errore: accesso diretto negato');
}
// la connessione è stata aperta in index.php
// eseguo una query per ottenere le categorie di prodotti
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
?><h1>Catalogo</h1>
<!-- tabella con i prodotti, la tabella ha le seguenti colonne: ID, Nome, # ultimo lotto, quantità prodotta ultimo lotto, venduti ultimo mese, azioni -->
<!-- azioni: heatmap, dettaglio -->
<table>
    <thead>
        <tr>
            <th style="width: 0;">#</th>
            <th>Nome</th>
            <th># ultimo lotto</th>
            <th>Quantità prodotta ultimo lotto</th>
            <th>Venduti ultimo mese</th>
            <th style="width: 0;">Azioni</th>
        </tr>
    </thead>
    <tbody>
        <?php

        $query = 'SELECT id, name, cached_last_lot_id, cached_qty_last_lot, cached_sold_last_month FROM categories WHERE company_id = ?';
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $company['id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        mysqli_stmt_bind_result($stmt, $id, $name, $cached_last_lot_id, $cached_qty_last_lot, $cached_sold_last_month);
        $fetchedTotal = mysqli_stmt_num_rows($stmt);
        while (mysqli_stmt_fetch($stmt)) {
        ?>
            <tr>
                <td style="text-align: right;"><?php echo $id; ?></td>
                <td><?php echo htmlentities($name); ?></td>
                <td><?php echo htmlentities($cached_last_lot_id); ?></td>
                <td><?php echo $cached_qty_last_lot; ?></td>
                <td><?php echo $cached_sold_last_month; ?></td>
                <td style="white-space: nowrap;">
                    <a class="btn" href="index.php?page=heatmap&id=<?php echo $id; ?>">Heatmap</a>
                    <a class="btn primary" href="index.php?page=dettaglio&id=<?php echo $id; ?>">Dettaglio</a>
                </td>
            </tr>
        <?php
        }
        mysqli_stmt_close($stmt);
        if ($fetchedTotal === 0) {
        ?>
            <tr>
                <td colspan="6" style="text-align: center;">Nessun prodotto presente!</td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>
<!-- pulsante per aggiungere un nuovo prodotto -->
<span style="display: block; margin-top: 20px; text-align: right;">
    <a class="btn primary" href="index.php?page=aggiungi-prodotto">Aggiungi prodotto</a>
</span>