<?php
session_start();
require_once("../db/db.php");

// Vérifier si l'ID du produit est fourni
if (!isset($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$id_produit = $_GET["id"];

// Récupérer les données du produit
try {
    $req = $pdo->prepare("SELECT * FROM Produits WHERE id_produit = ?");
    $req->execute([$id_produit]);
    $produit = $req->fetch(PDO::FETCH_OBJ);

    if (!$produit) {
        header("Location: index.php");
        exit;
    }
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération du produit : " . $e->getMessage();
}

// Contrôle de saisie
if (isset($_POST["btnSubmit"])) {
    // Récupérer et nettoyer les données du formulaire
    $reference = trim($_POST["reference"] ?? '');
    $nom = trim($_POST["nom"] ?? '');
    $categorie = trim($_POST["categorie"] ?? '');
    $prix_unitaire = trim($_POST["prix_unitaire"] ?? '');
    $quantite_stock = trim($_POST["quantite_stock"] ?? '');
    $seuil_reapprovisionnement = trim($_POST["seuil_reapprovisionnement"] ?? '');

    // Validation des champs
    if (empty($reference) || empty($nom) || empty($categorie) || empty($prix_unitaire) || empty($quantite_stock) || empty($seuil_reapprovisionnement)) {
        $error = "Tous les champs obligatoires doivent être remplis.";
    } elseif (!is_numeric($prix_unitaire) || $prix_unitaire < 0) {
        $error = "Le prix unitaire doit être un nombre positif.";
    } elseif (!is_numeric($quantite_stock) || $quantite_stock < 0 || floor($quantite_stock) != $quantite_stock) {
        $error = "La quantité en stock doit être un entier positif.";
    } elseif (!is_numeric($seuil_reapprovisionnement) || $seuil_reapprovisionnement < 0 || floor($seuil_reapprovisionnement) != $seuil_reapprovisionnement) {
        $error = "Le seuil de réapprovisionnement doit être un entier positif.";
    } else {
        try {
            // Vérifier si la référence existe déjà pour un autre produit
            $req = $pdo->prepare("SELECT COUNT(*) FROM Produits WHERE reference = ? AND id_produit != ?");
            $req->execute([$reference, $id_produit]);
            if ($req->fetchColumn() > 0) {
                $error = "Cette référence est déjà utilisée par un autre produit.";
            } else {
                // Mettre à jour le produit
                $req = $pdo->prepare("
                    UPDATE Produits 
                    SET reference = ?, nom = ?, categorie = ?, prix_unitaire = ?, quantite_stock = ?, seuil_reapprovisionnement = ?
                    WHERE id_produit = ?
                ");
                $req->execute([$reference, $nom, $categorie, $prix_unitaire, $quantite_stock, $seuil_reapprovisionnement, $id_produit]);

                // Redirection vers la liste des produits
                header("Location: index.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de la mise à jour du produit : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un produit</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>

<body>
    <!-- Navbar -->
    <?php include("../includes/navbar.php"); ?>
    <!-- Navbar -->

    <div class="container mt-5">
        <h1 class="text-center col-md-6">Modifier un produit</h1>
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="" method="post">
            <div class="form-group col-md-6 py-2">
                <label for="reference" class="form-label">Référence <sup style="color: red;">*</sup></label>
                <input type="text" name="reference" class="form-control" id="reference"
                    value="<?php echo isset($_POST['reference']) ? htmlspecialchars($_POST['reference']) : htmlspecialchars($produit->reference); ?>" required>
            </div>
            <div class="form-group col-md-6 py-2">
                <label for="nom" class="form-label">Nom <sup style="color: red;">*</sup></label>
                <input type="text" name="nom" class="form-control" id="nom"
                    value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : htmlspecialchars($produit->nom); ?>" required>
            </div>
            <div class="form-group col-md-6 py-2">
                <label for="categorie" class="form-label">Catégorie <sup style="color: red;">*</sup></label>
                <input type="text" name="categorie" class="form-control" id="categorie"
                    value="<?php echo isset($_POST['categorie']) ? htmlspecialchars($_POST['categorie']) : htmlspecialchars($produit->categorie); ?>" required>
            </div>
            <div class="form-group col-md-6 py-2">
                <label for="prix_unitaire" class="form-label">Prix unitaire (Fcfa) <sup style="color: red;">*</sup></label>
                <input type="number" step="0.01" name="prix_unitaire" class="form-control" id="prix_unitaire"
                    value="<?php echo isset($_POST['prix_unitaire']) ? htmlspecialchars($_POST['prix_unitaire']) : $produit->prix_unitaire; ?>" required>
            </div>
            <div class="form-group col-md-6 py-2">
                <label for="quantite_stock" class="form-label">Quantité en stock <sup style="color: red;">*</sup></label>
                <input type="number" name="quantite_stock" class="form-control" id="quantite_stock"
                    value="<?php echo isset($_POST['quantite_stock']) ? htmlspecialchars($_POST['quantite_stock']) : $produit->quantite_stock; ?>" required>
            </div>
            <div class="form-group col-md-6 py-2">
                <label for="seuil_reapprovisionnement" class="form-label">Seuil de réapprovisionnement <sup style="color: red;">*</sup></label>
                <input type="number" name="seuil_reapprovisionnement" class="form-control" id="seuil_reapprovisionnement"
                    value="<?php echo isset($_POST['seuil_reapprovisionnement']) ? htmlspecialchars($_POST['seuil_reapprovisionnement']) : $produit->seuil_reapprovisionnement; ?>" required>
            </div>
            <div class="my-3">
                <button class="btn btn-primary" type="submit" name="btnSubmit">Enregistrer</button>
                <a class="btn btn-danger" href="index.php">Retour</a>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <?php include("../includes/footer.php"); ?>
    <!-- Footer -->
</body>
<script src="../assets/js/bootstrap.bundle.min.js"></script>

</html>