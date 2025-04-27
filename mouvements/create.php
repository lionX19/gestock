<?php
session_start();
require_once("../db/db.php");

// Récupérer la liste des produits pour le formulaire
$produits = $pdo->query("SELECT id_produit, nom FROM Produits ORDER BY nom")->fetchAll(PDO::FETCH_OBJ);

// Initialisation des variables pour gérer les erreurs et les valeurs saisies
$error = null;
$values = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnSubmit'])) {
    // Récupération des données du formulaire
    $id_produit = $_POST['id_produit'] ?? '';
    $type_mouvement = $_POST['type_mouvement'] ?? '';
    $quantite = trim($_POST['quantite'] ?? '');
    $date_mouvement = trim($_POST['date_mouvement'] ?? '');
    $commentaire = trim($_POST['commentaire'] ?? '') ?: null;
    $destination_motif = trim($_POST['destination_motif'] ?? '') ?: null;

    // Validation des champs
    if (empty($id_produit) || empty($type_mouvement) || empty($quantite) || empty($date_mouvement)) {
        $error = "Tous les champs obligatoires doivent être remplis.";
    } elseif (!is_numeric($quantite) || $quantite <= 0 || floor($quantite) != $quantite) {
        $error = "La quantité doit être un entier positif.";
    } elseif (!in_array($type_mouvement, ['ENTREE', 'SORTIE'])) {
        $error = "Type de mouvement invalide.";
    } else {
        try {
            // Vérifier la quantité en stock pour les sorties
            if ($type_mouvement === 'SORTIE') {
                $req = $pdo->prepare("SELECT quantite_stock FROM Produits WHERE id_produit = ?");
                $req->execute([$id_produit]);
                $stock = $req->fetchColumn();
                if ($quantite > $stock) {
                    $error = "Quantité insuffisante en stock.";
                }
            }

            if (!$error) {
                // Insérer le mouvement
                $req = $pdo->prepare("
                    INSERT INTO Mouvements (id_produit, type_mouvement, quantite, date_mouvement, commentaire, destination_motif)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $req->execute([$id_produit, $type_mouvement, $quantite, $date_mouvement, $commentaire, $destination_motif]);

                // Mettre à jour le stock
                $req = $pdo->prepare(
                    $type_mouvement === 'ENTREE'
                        ? "UPDATE Produits SET quantite_stock = quantite_stock + ? WHERE id_produit = ?"
                        : "UPDATE Produits SET quantite_stock = quantite_stock - ? WHERE id_produit = ?"
                );
                $req->execute([$quantite, $id_produit]);

                // Redirection vers la liste des mouvements
                header("Location: index.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de l'ajout du mouvement : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un mouvement</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>

<body>
    <!-- Navbar -->
    <?php include("../includes/navbar.php"); ?>
    <!-- Navbar -->

    <div class="container mt-5">
        <h1 class="text-center col-md-6">Ajouter un mouvement</h1>
        <?php if ($error) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div id="stock-alert" class="alert alert-warning d-none"></div>
        <form action="" method="post">
            <div class="form-group col-md-6 py-2">
                <label for="id_produit" class="form-label">Produit <sup style="color: red;">*</sup></label>
                <select name="id_produit" class="form-control" id="id_produit" required>
                    <option value="">Sélectionner un produit</option>
                    <?php foreach ($produits as $produit) : ?>
                        <option value="<?php echo $produit->id_produit; ?>"
                            <?php echo isset($values['id_produit']) && $values['id_produit'] == $produit->id_produit ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($produit->nom); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-6 py-2">
                <label for="type_mouvement" class="form-label">Type de mouvement <sup style="color: red;">*</sup></label>
                <select name="type_mouvement" class="form-control" id="type_mouvement" required>
                    <option value="">Sélectionner un type</option>
                    <option value="ENTREE" <?php echo isset($values['type_mouvement']) && $values['type_mouvement'] == 'ENTREE' ? 'selected' : ''; ?>>Entrée</option>
                    <option value="SORTIE" <?php echo isset($values['type_mouvement']) && $values['type_mouvement'] == 'SORTIE' ? 'selected' : ''; ?>>Sortie</option>
                </select>
            </div>
            <div class="form-group col-md-6 py-2">
                <label for="quantite" class="form-label">Quantité <sup style="color: red;">*</sup></label>
                <input type="number" name="quantite" class="form-control" id="quantite"
                    value="<?php echo isset($values['quantite']) ? htmlspecialchars($values['quantite']) : ''; ?>" required>
            </div>
            <div class="form-group col-md-6 py-2">
                <label for="date_mouvement" class="form-label">Date <sup style="color: red;">*</sup></label>
                <input type="datetime-local" name="date_mouvement" class="form-control" id="date_mouvement"
                    value="<?php echo isset($values['date_mouvement']) ? htmlspecialchars($values['date_mouvement']) : ''; ?>" required>
            </div>
            <div class="form-group col-md-6 py-2">
                <label for="commentaire" class="form-label">Commentaire</label>
                <textarea name="commentaire" class="form-control" id="commentaire"><?php echo isset($values['commentaire']) ? htmlspecialchars($values['commentaire']) : ''; ?></textarea>
            </div>
            <div class="form-group col-md-6 py-2">
                <label for="destination_motif" class="form-label">Destination/Motif (pour sorties)</label>
                <input type="text" name="destination_motif" class="form-control" id="destination_motif"
                    value="<?php echo isset($values['destination_motif']) ? htmlspecialchars($values['destination_motif']) : ''; ?>">
            </div>
            <div class="my-3">
                <button class="btn btn-primary" type="submit" name="btnSubmit">Ajouter</button>
                <a class="btn btn-danger" href="index.php">Retour</a>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <?php include("../includes/footer.php"); ?>
    <!-- Footer -->

    <!-- Inclure jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            function checkStock() {
                const idProduit = $('#id_produit').val();
                const typeMouvement = $('#type_mouvement').val();
                const quantite = $('#quantite').val();

                if (idProduit && typeMouvement && quantite && quantite > 0) {
                    $.ajax({
                        url: 'check_stock.php',
                        type: 'POST',
                        data: {
                            id_produit: idProduit,
                            type_mouvement: typeMouvement,
                            quantite: quantite
                        },
                        dataType: 'json',
                        success: function(response) {
                            const alertDiv = $('#stock-alert');
                            if (response.success && response.is_below_threshold) {
                                alertDiv.text(response.message).removeClass('d-none').addClass('alert-warning');
                            } else {
                                alertDiv.addClass('d-none').removeClass('alert-warning').text('');
                            }
                        },
                        error: function() {
                            $('#stock-alert').text('Erreur lors de la vérification du stock.').removeClass('d-none').addClass('alert-danger');
                        }
                    });
                } else {
                    $('#stock-alert').addClass('d-none').removeClass('alert-warning').text('');
                }
            }

            // Vérifier le stock lors du changement de produit, type de mouvement ou quantité
            $('#id_produit, #type_mouvement, #quantite').on('change input', checkStock);
        });
    </script>
</body>

</html>