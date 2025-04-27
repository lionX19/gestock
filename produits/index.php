<?php
session_start();
require_once("../db/db.php");

try {
    // Initialisation des variables
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $low_stock = isset($_GET['low_stock']) && $_GET['low_stock'] == '1';
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $per_page = 10; // Nombre de produits par page
    $offset = ($page - 1) * $per_page;

    // Valider les valeurs de pagination
    $offset = (int)$offset;
    $per_page = (int)$per_page;

    // Compter le nombre total de produits pour la pagination
    $count_sql = "SELECT COUNT(*) FROM Produits";
    $count_conditions = [];
    $count_params = [];

    if ($search) {
        $count_conditions[] = "(reference LIKE ? OR nom LIKE ? OR categorie LIKE ?)";
        $count_params[] = "%$search%";
        $count_params[] = "%$search%";
        $count_params[] = "%$search%";
    }

    if ($low_stock) {
        $count_conditions[] = "quantite_stock <= seuil_reapprovisionnement";
    }

    if (!empty($count_conditions)) {
        $count_sql .= " WHERE " . implode(" AND ", $count_conditions);
    }

    $count_req = $pdo->prepare($count_sql);
    $count_req->execute($count_params);
    $total_products = $count_req->fetchColumn();
    $total_pages = ceil($total_products / $per_page);

    // Construction de la requête SQL pour les produits
    $sql = "SELECT * FROM Produits";
    $conditions = [];
    $params = [];

    if ($search) {
        $conditions[] = "(reference LIKE ? OR nom LIKE ? OR categorie LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($low_stock) {
        $conditions[] = "quantite_stock <= seuil_reapprovisionnement";
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    // Ajouter l'ordre et la limite directement (éviter les paramètres pour LIMIT)
    $sql .= " ORDER BY id_produit DESC LIMIT $offset, $per_page";

    // Débogage temporaire
    // var_dump($sql, $params, $offset, $per_page); // Décommentez pour vérifier

    // Préparer et exécuter la requête
    $req = $pdo->prepare($sql);
    $req->execute($params);

    $i = ($page - 1) * $per_page + 1; // Ajuster le compteur pour la page actuelle
} catch (PDOException $e) {
    header("Location: error.php?message=" . urlencode("Erreur de base de données : " . $e->getMessage()));
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des produits</title>
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
            <h1 class="text-start my-3">Liste des produits
                <a href="create.php" class="btn btn-outline-success">Ajouter</a>
            </h1>

            <!-- Formulaire de recherche -->
            <form method="GET" class="mb-3">
                <div class="input-group mb-2">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher par référence, nom ou catégorie" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="low_stock" name="low_stock" value="1" <?php echo isset($_GET['low_stock']) && $_GET['low_stock'] == '1' ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="low_stock">Afficher uniquement les stocks faibles/ruptures</label>
                </div>
            </form>

            <!-- Message pour résultats vides -->
            <?php if ($req->rowCount() == 0) : ?>
                <div class="alert alert-info">Aucun produit trouvé.</div>
            <?php endif; ?>

            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Référence</th>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>P.U</th>
                        <th>Quantité</th>
                        <th>Seuil</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($produit = $req->fetch(PDO::FETCH_OBJ)) :
                        $stock_faible = $produit->quantite_stock <= $produit->seuil_reapprovisionnement;
                    ?>
                        <tr <?php echo $stock_faible ? 'class="table-warning"' : ''; ?>>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($produit->reference); ?></td>
                            <td><?php echo htmlspecialchars($produit->nom); ?></td>
                            <td><?php echo htmlspecialchars($produit->categorie); ?></td>
                            <td><?php echo number_format($produit->prix_unitaire, 2, ',', ' '); ?> Fcfa</td>
                            <td>
                                <?php echo $produit->quantite_stock; ?>
                                <span class="stock-alert" data-produit-id="<?php echo $produit->id_produit; ?>"></span>
                            </td>
                            <td><?php echo $produit->seuil_reapprovisionnement; ?></td>
                            <td>
                                <a class="btn btn-outline-primary btn-sm" href="edit.php?id=<?php echo $produit->id_produit; ?>">Éditer</a>
                                <a class="btn btn-outline-danger btn-sm" href="delete.php?id=<?php echo $produit->id_produit; ?>"
                                    onclick="return confirm('Voulez-vous vraiment supprimer ce produit ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Référence</th>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>P.U</th>
                        <th>Quantité</th>
                        <th>Seuil</th>
                        <th>Actions</th>
                    </tr>
                </tfoot>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1) : ?>
                <nav aria-label="Navigation des produits">
                    <ul class="pagination justify-content-center">
                        <!-- Bouton Précédent -->
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" aria-label="Précédent">
                                <span aria-hidden="true">«</span>
                            </a>
                        </li>

                        <!-- Numéros de page -->
                        <?php
                        $range = 2; // Nombre de pages affichées de chaque côté
                        $start = max(1, $page - $range);
                        $end = min($total_pages, $page + $range);

                        if ($start > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($_GET, ['page' => 1])) . '">1</a></li>';
                            if ($start > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }

                        for ($p = $start; $p <= $end; $p++) {
                            echo '<li class="page-item ' . ($p == $page ? 'active' : '') . '">';
                            echo '<a class="page-link" href="?' . http_build_query(array_merge($_GET, ['page' => $p])) . '">' . $p . '</a>';
                            echo '</li>';
                        }

                        if ($end < $total_pages) {
                            if ($end < $total_pages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($_GET, ['page' => $total_pages])) . '">' . $total_pages . '</a></li>';
                        }
                        ?>

                        <!-- Bouton Suivant -->
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" aria-label="Suivant">
                                <span aria-hidden="true">»</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </main>
    <!-- Fin Contenu Principal -->

    <!-- Footer -->
    <?php include("../includes/footer.php"); ?>
    <!-- Footer -->

    <!-- Inclure jQuery -->
    <script src="../assets/js/jquery.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Vérifier le stock pour tous les produits affichés
            const produitIds = $('.stock-alert').map(function() {
                return $(this).data('produit-id');
            }).get();

            if (produitIds.length > 0) {
                $.ajax({
                    url: '../mouvements/check_stock.php',
                    type: 'POST',
                    data: {
                        id_produits: produitIds
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('.stock-alert').each(function() {
                                const produitId = $(this).data('produit-id');
                                const alertSpan = $(this);
                                if (response.stocks && response.stocks[produitId] && response.stocks[produitId].is_below_threshold) {
                                    alertSpan.html('<i class="bi bi-exclamation-triangle-fill text-warning ms-2" title="' + response.stocks[produitId].message + '"></i>');
                                }
                            });
                        }
                    },
                    error: function() {
                        $('.stock-alert').html('<i class="bi bi-exclamation-circle-fill text-danger ms-2" title="Erreur lors de la vérification du stock."></i>');
                    }
                });
            }
        });
    </script>
</body>

</html>