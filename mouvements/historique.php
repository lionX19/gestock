<?php
session_start();
require_once("../db/db.php");

// Requête par défaut pour récupérer les 9 derniers mouvements avec le nom du produit
$req = $pdo->query("
    SELECT m.*, p.nom AS produit_nom 
    FROM Mouvements m 
    JOIN Produits p ON m.id_produit = p.id_produit 
    ORDER BY m.date_mouvement DESC 
    LIMIT 0,9
");
$i = 1;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des mouvements</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>

<body>
    <!-- Navbar -->
    <?php include("../includes/navbar.php"); ?>
    <!-- Navbar -->

    <!-- Début Contenu Principal -->
    <main>
        <div class="container">
            <h1 class="text-start my-3">Historique des mouvements
                <a href="create_mouvement.php" class="btn btn-outline-success">Ajouter</a>
            </h1>

            <!-- Formulaire de recherche -->
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher par produit, type ou commentaire"
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
                            WHERE p.nom LIKE ? OR m.type_mouvement LIKE ? OR m.commentaire LIKE ? 
                            ORDER BY m.date_mouvement DESC
                        ");
                        $req->execute(["%$search%", "%$search%", "%$search%"]);
                    } else {
                        $req = $pdo->query("
                            SELECT m.*, p.nom AS produit_nom 
                            FROM Mouvements m 
                            JOIN Produits p ON m.id_produit = p.id_produit 
                            ORDER BY m.date_mouvement DESC 
                            LIMIT 0,9
                        ");
                    }

                    while ($mouvement = $req->fetch(PDO::FETCH_OBJ)) : ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($mouvement->produit_nom); ?></td>
                            <td>
                                <?php
                                if ($mouvement->type_mouvement === 'ENTREE') {
                                    echo '<span class="badge rounded-pill bg-success p-2">Entrée</span>';
                                } else {
                                    echo '<span class="badge rounded-pill bg-danger p-2">Sortie</span>';
                                }
                                ?>
                            </td>
                            <td><?php echo $mouvement->quantite; ?></td>
                            <td><?php echo htmlspecialchars($mouvement->date_mouvement); ?></td>
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

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>