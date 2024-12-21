<?php
include 'connessione.php';

// Funzione per calcolare lo stipendio
function calcola_stipendio($stipendio_base, $ore_lavorate, $ore_standard_per_mese, $bonus = 0, $detrazione_ferie = 0) {
    // Calcolare gli straordinari
    $ore_straordinarie = max(0, $ore_lavorate - $ore_standard_per_mese);
    $straordinari = $ore_straordinarie * 1.5;  // Supponiamo che lo straordinario venga pagato 1,5 volte la tariffa base.

    // Calcolare lo stipendio totale
    $stipendio_totale = $stipendio_base + $straordinari + $bonus - $detrazione_ferie;
    return round($stipendio_totale, 2);
}

// Parametri per il calcolo
$ore_standard_per_mese = 160;  // Ad esempio, 40 ore a settimana per 4 settimane
$bonus = 100;  // Bonus fisso per esempio
$detrrazione_ferie = 50;  // Detrazione per ferie non godute

$sql = "SELECT * FROM dipendenti";  // Query per recuperare i dati dei dipendenti
$result = $conn->query($sql);

$dipendenti = [];
$stipendi = [];
$presenze = [];
$ferie = [];

// Recupero dei dati dei dipendenti
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_dipendente = $row['id'];

        // Query per ottenere le presenze del dipendente
        $presenze_sql = "SELECT * FROM presenze WHERE id_dipendente = $id_dipendente";
        $presenze_result = $conn->query($presenze_sql);
        
        // Calcolare le ore lavorate totali per il dipendente
        $ore_lavorate_totali = 0;
        while ($presenza = $presenze_result->fetch_assoc()) {
            $ore_lavorate_totali += $presenza['ore_lavorate'];
        }

        // Calcolare lo stipendio
        $stipendio_totale = calcola_stipendio($row['stipendio'], $ore_lavorate_totali, $ore_standard_per_mese, $bonus, $detrrazione_ferie);

        // Query per ottenere le ferie del dipendente
        $ferie_sql = "SELECT * FROM ferie WHERE id_dipendente = $id_dipendente AND approvato = 1";
        $ferie_result = $conn->query($ferie_sql);
        $ferie_richieste = $ferie_result->num_rows;

        // Salvataggio dei dati per il grafico
        $dipendenti[] = $row['nome'] . ' ' . $row['cognome'];
        $stipendi[] = $stipendio_totale;
        $presenze[] = $presenze_result->num_rows;
        $ferie[] = $ferie_richieste;
    }
} else {
    echo "<tr><td colspan='9'>Nessun dipendente trovato</td></tr>";
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizza Dipendenti</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Importa Chart.js -->
    <link rel="stylesheet" href="style.css">  <!-- Collegamento al file CSS -->
</head>
<body>
    <h1>Elenco Dipendenti</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Cognome</th>
                <th>Ruolo</th>
                <th>Stipendio</th>
                <th>Data di Assunzione</th>
                <th>Ore Lavorate</th>
                <th>Presenze</th>
                <th>Ferie</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                $result->data_seek(0);  // Reset the result pointer
                while ($row = $result->fetch_assoc()) {
                    $id_dipendente = $row['id'];

                    // Query per ottenere le presenze del dipendente
                    $presenze_sql = "SELECT * FROM presenze WHERE id_dipendente = $id_dipendente";
                    $presenze_result = $conn->query($presenze_sql);
                    
                    // Calcolare le ore lavorate totali per il dipendente
                    $ore_lavorate_totali = 0;
                    while ($presenza = $presenze_result->fetch_assoc()) {
                        $ore_lavorate_totali += $presenza['ore_lavorate'];
                    }

                    // Calcolare lo stipendio
                    $stipendio_totale = calcola_stipendio($row['stipendio'], $ore_lavorate_totali, $ore_standard_per_mese, $bonus, $detrrazione_ferie);

                    // Query per ottenere le ferie del dipendente
                    $ferie_sql = "SELECT * FROM ferie WHERE id_dipendente = $id_dipendente AND approvato = 1";
                    $ferie_result = $conn->query($ferie_sql);
                    $ferie_richieste = $ferie_result->num_rows;

                    // Visualizzazione dei dati del dipendente
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['nome']}</td>
                            <td>{$row['cognome']}</td>
                            <td>{$row['ruolo']}</td>
                            <td>€{$stipendio_totale}</td>
                            <td>{$row['data_assunzione']}</td>
                            <td>{$ore_lavorate_totali} ore</td>
                            <td>{$presenze_result->num_rows} giorni</td>
                            <td>{$ferie_richieste} giorni</td>
                          </tr>";
                }
            }
            ?>
        </tbody>
    </table>

    <!-- Aggiungi il grafico -->
    <h2>Grafico Dati Dipendenti</h2>
    <canvas id="graficoDipendenti"></canvas>

    <script>
        // Creazione del grafico con Chart.js
        const ctx = document.getElementById('graficoDipendenti').getContext('2d');
        const graficoDipendenti = new Chart(ctx, {
            type: 'bar', // Tipo di grafico
            data: {
                labels: <?php echo json_encode($dipendenti); ?>, // Etichette (nomi dei dipendenti)
                datasets: [{
                    label: 'Stipendio (€)',
                    data: <?php echo json_encode($stipendi); ?>, // Dati (stipendi)
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Presenze',
                    data: <?php echo json_encode($presenze); ?>, // Dati (presenze)
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Ferie',
                    data: <?php echo json_encode($ferie); ?>, // Dati (ferie)
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
    </script>
</body>
</html>

<?php
// Chiudere la connessione al database
$conn->close();
?>
