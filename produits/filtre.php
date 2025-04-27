<?php
require_once("../db/db.php");

header('Content-Type: application/json');

$response = ['success' => false, 'products' => [], 'message' => ''];

try {
    // Initialisation des variables
    $search = isset($_POST['search']) ? trim($_POST['search']) : '';
    $low_stock = isset($_POST['low_stock']) && $_POST['low_stock'] == '1';

    // Construction de la requête SQL
    $sql = "SELECT id_produit, reference, nom, categorie, prix_unitaire, quantite_stock, seuil_reapprovisionnement FROM Produits";
    $conditions = [];
    $params = [];

    // Ajouter la condition de recherche si applicable
    if ($search) {
        $conditions[] = "(reference LIKE ? OR nom LIKE ? OR categorie LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    // Ajouter la condition de stock faible si applicable
    if ($low_stock) {
        $conditions[] = "quantite_stock <= seuil_reapprovisionnement";
    }

    // Combiner les conditions
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    // Ajouter l'ordre
    $sql .= " ORDER BY id_produit DESC";

    // Préparer et exécuter la requête
    $req = $pdo->prepare($sql);
    $req->execute($params);

    // Récupérer les produits
    $products = $req->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['products'] = $products;
    $response['message'] = $products ? 'Produits récupérés avec succès.' : 'Aucun produit trouvé.';
} catch (PDOException $e) {
    $response['message'] = "Erreur de base de données : " . $e->getMessage();
}

echo json_encode($response);
exit;
