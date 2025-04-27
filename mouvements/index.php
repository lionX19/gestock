<?php
session_start();
require_once("../db/db.php");

// Requête pour récupérer les mouvements avec le nom du produit
$req = $pdo->query("
    SELECT m.*, p.nom AS produit_nom 
    FROM Mouvements m 
    JOIN Produits p ON m.id_produit = p.id_produit 
    ORDER BY m.id_mouvement DESC 
    LIMIT 0,9
");
$i = 1;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des mouvements</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>

<body>
    <!-- Navbar -->
    <?php include("../includes/navbar.php"); ?>
    <!-- Navbar -->

    <!-- Début Contenu Principal -->
    <main>
        <div class="container">
            <h1 class="text-start my-3">Liste des mouvements
                <a href="create.php" class="btn btn-outline-success">Ajouter</a>
            </h1>

            <!-- Formulaire de recherche -->
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher par produit ou type de mouvement"
                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                </div>
            </form>

            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Produit</th>
                        <th>Type</th>
                        <th>Quantité</th>
                        <th>Date</th>
                        <th>Commentaire</th>
                        <th>Destination/Motif</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Gestion de la recherche
                    $search = isset($_GET['search']) ? $_GET['search'] : '';
                    if ($search) {
                        $req = $pdo->prepare("
                            SELECT m.*, p.nom AS produit_nom 
                            FROM Mouvements m 
                            JOIN Produits p ON m.id_produit = p.id_produit 
                            WHERE p.nom LIKE ? OR m.type_mouvement LIKE ? 
                            ORDER BY m.id_mouvement DESC
                        ");
                        $req->execute(["%$search%", "%$search%"]);
                    } else {
                        $req = $pdo->query("
                            SELECT m.*, p.nom AS produit_nom 
                            FROM Mouvements m 
                            JOIN Produits p ON m.id_produit = p.id_produit 
                            ORDER BY m.id_mouvement DESC 
                            LIMIT 0,9
                        ");
                    }

                    while ($mouvement = $req->fetch(PDO::FETCH_OBJ)) : ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($mouvement->produit_nom); ?></td>
                            <td><?php echo htmlspecialchars($mouvement->type_mouvement); ?></td>
                            <td>
                                <?php echo $mouvement->quantite; ?>
                                <span class="stock-alert" data-produit-id="<?php echo $mouvement->id_produit; ?>"></span>
                            </td>
                            <td><?php echo $mouvement->date_mouvement; ?></td>
                            <td><?php echo htmlspecialchars($mouvement->commentaire ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($mouvement->destination_motif ?? ''); ?></td>
                            <td>
                                <a class="btn btn-outline-primary btn-sm" href="edit_mouvement.php?id=<?php echo $mouvement->id_mouvement; ?>">Éditer</a>
                                <a class="btn btn-outline-danger btn-sm" href="delete_mouvement.php?id=<?php echo $mouvement->id_mouvement; ?>"
                                    onclick="return confirm('Voulez-vous vraiment supprimer ce mouvement ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Produit</th>
                        <th>Type</th>
                        <th>Quantité</th>
                        <th>Date</th>
                        <th>Commentaire</th>
                        <th>Destination/Motif</th>
                        <th>Actions</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </main>
    <!-- Fin Contenu Principal -->

    <!-- Footer -->
    <?php include("../includes/footer.php"); ?>
    <!-- Footer -->

    <!-- Inclure jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Vérifier le stock pour chaque produit dans le tableau
            $('.stock-alert').each(function() {
                const produitId = $(this).data('produit-id');
                const alertSpan = $(this);

                $.ajax({
                    url: 'check_stock.php',
                    type: 'POST',
                    data: {
                        id_produit: produitId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.is_below_threshold) {
                            alertSpan.html('<i class="bi bi-exclamation-triangle-fill text-warning ms-2" title="' + response.message + '"></i>');
                        }
                    },
                    error: function() {
                        alertSpan.html('<i class="bi bi-exclamation-circle-fill text-danger ms-2" title="Erreur lors de la vérification du stock."></i>');
                    }
                });
            });
        });
    </script>
</body>

</html>