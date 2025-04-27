<?php
session_start();
include("db/db.php");
//Vérifier si le formulaire a été soumi
if (isset($_POST["btnSubmit"])) {
    if (!empty($_POST["email"]) and !empty($_POST["password"])) {
        // Récupération des champs du formulaire
        $email = trim(htmlspecialchars($_POST["email"]));
        $password = trim(htmlspecialchars($_POST["password"]));

        $req = $pdo->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
        $req->execute(array($email, sha1($password)));

        if ($req->rowCount() == 1) {
            $user = $req->fetch(); // Récupérer les données de l'utilisateur.
            $_SESSION['id'] = $user->id_user;
            $_SESSION['name'] = $user->name;
            $_SESSION['role'] = $user->role;
            header("Location: produits/index.php?name=" . $_SESSION['name']);
        } else {
            $error = "Nom d'utilisateur ou mot de passe incorrect";
        }
    } else {
        $error = "Veuillez remplir tous les champs !";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page de Connexion</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h2 class="text text-center">Connectez-vous !</h2>
        <form class="w-50 m-auto" action="" method="post">
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" class="form-control" id="email">
            </div>
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" class="form-control" id="password">
            </div>
            <button class="my-2 btn btn-primary" type="submit" name="btnSubmit">Se connecter</button>
        </form>
    </div>
</body>

<script src="assets/js/bootstrap.min.js"></script>

</html>