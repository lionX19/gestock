<?php
session_start();
require_once("../db/db.php");

// Vérification de la session (optionnel, décommentez si nécessaire)
// if (!isset($_SESSION['user'])) {
//     header("Location: login.php");
//     exit;
// }

try {
    // 1. Quantité totale
    $req_total = $pdo->query("SELECT SUM(quantite_stock) AS total_stock FROM Produits");
    $total_stock = $req_total->fetch(PDO::FETCH_OBJ)->total_stock ?? 0;

    // 2. Produits les plus sortis (Top 5)
    $req_sorties = $pdo->query("
        SELECT p.nom, SUM(m.quantite) AS total_sortie 
        FROM Mouvements m 
        JOIN Produits p ON m.id_produit = p.id_produit 
        WHERE m.type_mouvement = 'SORTIE' 
        GROUP BY m.id_produit, p.nom 
        ORDER BY total_sortie DESC 
        LIMIT 5
    ");
    $produits_sorties = $req_sorties->fetchAll(PDO::FETCH_OBJ);

    // 3. Évolution du stock
    $req_mouvements = $pdo->query("
        SELECT date_mouvement, type_mouvement, quantite 
        FROM Mouvements 
        ORDER BY date_mouvement
    ");
    $mouvements = $req_mouvements->fetchAll(PDO::FETCH_OBJ);

    // Calculer l'évolution du stock par jour
    $stock_evolution = [];
    $current_stock = 0;
    $dates = [];

    foreach ($mouvements as $mouvement) {
        $date = date('Y-m-d', strtotime($mouvement->date_mouvement));
        if (!isset($stock_evolution[$date])) {
            $stock_evolution[$date] = $current_stock;
            $dates[] = $date;
        }

        if ($mouvement->type_mouvement === 'ENTREE') {
            $current_stock += $mouvement->quantite;
        } elseif ($mouvement->type_mouvement === 'SORTIE') {
            $current_stock -= $mouvement->quantite;
        }

        $stock_evolution[$date] = $current_stock;
    }

    // Préparer les données pour Chart.js
    $chart_labels = json_encode($dates);
    $chart_data = json_encode(array_values($stock_evolution));
} catch (PDOException $e) {
    $error = "Erreur de base de données : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques des stocks</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body>
    <!-- Navbar -->
    <?php include("../includes/navbar.php"); ?>
    <!-- Navbar -->

    <!-- Début Contenu Principal -->
    <main>
        <div class="container">
            <h1 class="text-start my-3">Statistiques des stocks</h1>

            <?php if (isset($error)) : ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php else : ?>

                <!-- 1. Quantité totale -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Quantité totale en stock</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text display-4"><?php echo $total_stock; ?> unités</p>
                    </div>
                </div>

                <!-- 2. Produits les plus sortis -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Produits les plus sortis (Top 5)</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Produit</th>
                                    <th>Quantité sortie</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($produits_sorties)) : ?>
                                    <tr>
                                        <td colspan="3" class="text-center">Aucun mouvement de sortie enregistré.</td>
                                    </tr>
                                <?php else : ?>
                                    <?php $i = 1;
                                    foreach ($produits_sorties as $produit) : ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><?php echo htmlspecialchars($produit->nom); ?></td>

                                            <td>
                                                <?php echo $produit->total_sortie; ?> unité<?php if ($produit->total_sortie >= 2) {
                                                                                                echo "s";
                                                                                            }; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. Évolution du stock -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Évolution du stock total</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="stockChart" height="100"></canvas>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </main>
    <!-- Fin Contenu Principal -->

    <!-- Footer -->
    <?php include("../includes/footer.php"); ?>
    <!-- Footer -->

    <!-- Inclure jQuery et Chart.js -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialiser le graphique Chart.js
            const ctx = document.getElementById('stockChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo $chart_labels; ?>,
                    datasets: [{
                        label: 'Stock total',
                        data: <?php echo $chart_data; ?>,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Stock (unités)'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>