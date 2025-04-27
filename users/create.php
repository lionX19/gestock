<?php
    session_start();
    include("traitement.php");
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajout d'utilisateurs</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>

<body>
    <!-- Navbar -->
    <?php include("../includes/navbar.php"); ?>
    <!-- Navbar -->

    <div class="container mt-5">
        <h1 class="text text-center col-md-6">Ajouter un utilisateur</h1>
        <?php if(isset($error)){ echo '<font color=red>'.$error; } ?>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group col-md-6 py-2">
                <label for="name" class="form-label">Nom <sup style="color: red;">*</sup></label>
                <input type="text" name="name" class="form-control" id="name"
                    value="<?php if (isset($_POST['name'])) { echo $_POST['name'];}?>">
            </div>
            <div class="form-group col-md-6 py-2">
                <label for="firstname" class="form-label">Prénom <sup style="color: red;">*</sup></label>
                <input type="text" name="firstname" class="form-control" id="firstname"
                    value="<?php if (isset($_POST['firstname'])) { echo $_POST['firstname'];}?>">
            </div>
            <div class="form-group col-md-6 mt-2">
                <label for="email" class="form-label">Email <sup style="color: red;">*</sup></label>
                <input type="email" name="email" class="form-control" id="email"
                    value="<?php if (isset($_POST['email'])) { echo $_POST['email'];}?>">
            </div>
            <div class="form-group col-md-6 mt-2">
                <label for="phone" class="form-label">Téléphone <sup style="color: red;">*</sup></label>
                <input type="tel" name="phone" class="form-control" id="phone"
                    value="<?php if (isset($_POST['phone'])) { echo $_POST['phone'];}?>">
            </div>
            <div class="form-group col-md-6 mt-2">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" name="password" class="form-control" id="password">
            </div>
            <div class="form-group col-md-6 mt-2">
                <label for="avatar">Photo de profil </label>
                <input type="file" name="avatar" id="avatar" class="form-control">
            </div>
            <div class=" form-group col-md-6 mt-2">
                <label for="role">Role <sup style="color: red;">*</sup></label>
                <select name="role" id="role" class="form-control">
                    <option value="1" selected>Utilisateur</option>
                    <option value="2">Administrateur</option>
                </select>
            </div>

            <div class="my-3">
                <button class="btn btn-primary" type="submit" name="btnSubmit">Soumettre</button>
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