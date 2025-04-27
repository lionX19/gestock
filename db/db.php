<?php

try {
    $pdo = new PDO('mysql:host=localhost;dbname=gestock;charset=UTF8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    // $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exception) {
    header("Location:../en_chantier.php?message="    . urlencode("problème de connexion à la base de données ! " . $exception->getMessage()));
    exit();
}
