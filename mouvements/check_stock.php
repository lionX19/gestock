<?php
require_once("../db/db.php");

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (isset($_POST['id_produit'])) {
    $id_produit = $_POST['id_produit'];
    $quantite = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 0;
    $type_mouvement = isset($_POST['type_mouvement']) ? $_POST['type_mouvement'] : '';

    try {
        // Récupérer les informations du produit
        $req = $pdo->prepare("SELECT quantite_stock, seuil_reapprovisionnement FROM Produits WHERE id_produit = ?");
        $req->execute([$id_produit]);
        $produit = $req->fetch(PDO::FETCH_OBJ);

        if ($produit) {
            $nouveau_stock = $produit->quantite_stock;

            // Ajuster le stock selon le type de mouvement
            if ($type_mouvement === 'ENTREE') {
                $nouveau_stock += $quantite;
            } elseif ($type_mouvement === 'SORTIE') {
                $nouveau_stock -= $quantite;
            }

            // Vérifier si le seuil est atteint ou dépassé
            if ($nouveau_stock <= $produit->seuil_reapprovisionnement) {
                $response['success'] = true;
                $response['message'] = "Attention : le stock sera de $nouveau_stock unités après ce mouvement, ce qui est inférieur ou égal au seuil de réapprovisionnement ({$produit->seuil_reapprovisionnement}).";
                $response['is_below_threshold'] = true;
            } else {
                $response['success'] = true;
                $response['message'] = "Le stock est correct.";
                $response['is_below_threshold'] = false;
            }
        } else {
            $response['message'] = "Produit non trouvé.";
        }
    } catch (PDOException $e) {
        $response['message'] = "Erreur : " . $e->getMessage();
    }
} else {
    $response['message'] = "ID du produit manquant.";
}

echo json_encode($response);
exit;
