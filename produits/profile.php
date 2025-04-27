<?php
    session_start();
    require_once("../db/db.php");
    
    if(isset($_SESSION["id"])) {
        $id = $_SESSION["id"];

        $req = $pdo->prepare("SELECT * FROM users WHERE id_user=$id");
        $req->execute();
        $user = $req->fetch();
    }
    
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modification d'utilisateurs</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>

<body>
    <!-- Navbar -->
    <?php include("../includes/navbar.php"); ?>
    <!-- Navbar -->

    <div class="container mt-5">
        <h1 class="text text-center col-md-6">Editer un utilisateur</h1>
        <?php if(isset($error)){ echo '<font color=red>'.$error; } ?>
        <form action="" method="post">
            <div class="form-group col-md-6 py-2">
                <label for="name" class="form-label">Nom</label>
                <input type="text" name="name" class="form-control" id="name" value="<?= $user->name ?>">
            </div>
            <div class="form-group col-md-6 py-2">
                <label for="firstname" class="form-label">Prénom</label>
                <input type="text" name="firstname" class="form-control" id="firstname" value="<?= $user->firstname;?>">
            </div>
            <div class="form-group col-md-6 mt-2">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" class="form-control" id="email" value="<?= $user->email;?>">
            </div>
            <div class="form-group col-md-6 mt-2">
                <label for="phone" class="form-label">Téléphone</label>
                <input type="tel" name="phone" class="form-control" id="phone" value="<?= $user->phone;?>">
            </div>

            <div class="my-3">
                <a class="btn btn-success" href="edit.php?id=<?= $user->id_user?>">Modifier</a>
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