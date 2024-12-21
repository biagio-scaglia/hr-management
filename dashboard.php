<?php
include 'connessione.php';

// Recupera i dati delle presenze per il grafico
$presenze_sql = "SELECT id_dipendente, SUM(ore_lavorate) AS ore_lavorate_totali FROM presenze GROUP BY id_dipendente";
$presenze_result = $conn->query($presenze_sql);

$ore_lavorate = [];
$dipendenti = [];
while ($row = $presenze_result->fetch_assoc()) {
    $dipendenti[] = $row['id_dipendente'];
    $ore_lavorate[] = $row['ore_lavorate_totali'];
}

// Recupera i dati delle ferie per il grafico
$ferie_sql = "SELECT id_dipendente, COUNT(*) AS ferie_richieste FROM ferie WHERE approvato = 1 GROUP BY id_dipendente";
$ferie_result = $conn->query($ferie_sql);

$ferie_richieste = [];
while ($row = $ferie_result->fetch_assoc()) {
    $ferie_richieste[] = $row['ferie_richieste'];
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard HR</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>Dashboard HR</h1>

    <canvas id="presenzeChart" width="400" height="200"></canvas>
    <canvas id="ferieChart" width="400" height="200"></canvas>

    <script>
        var presenzeData = {
            labels: <?php echo json_encode($dipendenti); ?>,
            datasets: [{
                label: 'Ore Lavorate',
                data: <?php echo json_encode($ore_lavorate); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        };

        var ferieData = {
            labels: <?php echo json_encode($dipendenti); ?>,
            datasets: [{
                label: 'Ferie Richieste',
                data: <?php echo json_encode($ferie_richieste); ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        };

        var ctxPresenze = document.getElementById('presenzeChart').getContext('2d');
        var presenzeChart = new Chart(ctxPresenze, {
            type: 'bar',
            data: presenzeData
        });

        var ctxFerie = document.getElementById('ferieChart').getContext('2d');
        var ferieChart = new Chart(ctxFerie, {
            type: 'bar',
            data: ferieData
        });
    </script>
</body>
</html>
