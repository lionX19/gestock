<?php
    session_start();
    require_once("../db/db.php");
    
    if(isset($_GET["id"])) {
        $id = $_GET["id"];

        $req = $pdo->prepare("SELECT * FROM users WHERE id_user=$id");
        $req->execute();
        $user = $req->fetch();
    }

    // Controle de saisie
    if (isset($_POST["btnSubmit"])) {
        // Vérifier si les champs sont non vides.
        if(!empty($_POST["name"]) AND !empty($_POST["firstname"]) AND !empty($_POST["email"]) AND 
            !empty($_POST["phone"])) {
                // Déclaration des variables en retirant les espaces grace à trim et en retirant les possibles injections html grace à htmlspecialchars.
                $name = trim(htmlspecialchars($_POST["name"]));
                $firstname = trim(htmlspecialchars($_POST["firstname"]));
                $email = trim(htmlspecialchars($_POST["email"]));
                $phone = trim(htmlspecialchars($_POST["phone"]));
                if (empty($_POST["password"])) {
                    $password = "password";
                }else {
                    $password = trim(htmlspecialchars($_POST["password"]));
                }
                $avatarName = $_FILES["avatar"]["name"];
                if (isset($_POST["role"])) {
                    $role = trim(htmlspecialchars($_POST["role"]));
                }else {
                    $role = 1;
                }
                
                //Récupérer la date actuelle.
                $date = new DateTime();
                $date_modif = $date->format('Y-m-d H:i:s');
                
                //Gestion de l'image
                $valid_extension = array('png', 'jpeg', 'jpg'); //Liste des extensions valides.
                $avatar_path = pathinfo($avatarName, PATHINFO_EXTENSION); //récupérer l'extension du fichier.
                $file_tmp = $_FILES["avatar"]["tmp_name"]; //Emplacement temporaire du fichier.
                $file_name = $name."_".$avatarName; // nom du fichier stocké dans le dossier images.
            
                // Vérifier la saisie de l'utilisateur
            if (strlen($name) >= 3 AND strlen($name) <= 255){ // votre nom doit contenir entre 3 à 255 caractères.
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) { //Vérifie le format de l'email, s'il est conforme.
                    if (strlen($email) <= 255) { // L'e-mail doit etre moins de 255 caractères de long.
                        //Si pas de correspondance, on poursuit notre insertion en base de données.
                        if (strlen($password) >=8 AND strlen($password) <= 255) {//Vérifie la taille du mot de passe. Il doit supérieur ou égal à 8 caractères et inférieur ou égal à 255 caractères.
                            $password_crypted = sha1($password); // crypter le mot de passe.
                            if (strlen($phone) >= 9 AND strlen($phone) <= 15) { //Vérifie la taille du numéro de téléphone, s'il est compris entre 9 et 15 caractères.
                                // Vérifie si le role appartient soit à l'utilisateur simple (1), ou à l'administrateur (2).
                                if ($role == 1 OR $role == 2) { 
                                    //Si le champ de l'image est vide, on change la valeur du nom du fichier par le nom de l'image par défaut.
                                    if(empty($avatarName)) {
                                        // insérer les données en base de données.
                                        $req = $pdo->prepare("UPDATE users SET name=?, firstname=?, email=?, 
                                        password=?, phone=?, role=?, updated_at=? WHERE id_user=$id");
                                        $req->execute(array($name, $firstname, $email, $password_crypted, $phone, 
                                            $role, $date_modif));
                                        // Redirection vers la page Liste des utilisateurs.
                                        header("Location:index.php");
                                    }
                                    //Vérifiez l'extension du fichier.
                                    elseif (in_array(strtolower($avatar_path), $valid_extension)) {
                                        //Définir le chemin de stockage du fichier.
                                        $destinations = '../assets/images/'.$file_name; //chemin d'accès ou sera stocké le fichier.
                                        // Déplacer le fichier dans le dossier images.
                                        if (move_uploaded_file($file_tmp, $destinations)) {
                                            // insérer les données en base de données.
                                            $req = $pdo->prepare("UPDATE users SET name=?, firstname=?, email=?, 
                                                password=?, phone=?, avatar=?, role=?, updated_at=? WHERE id_user=$id");
                                            $req->execute(array($name, $firstname, $email, $password_crypted, $phone, 
                                                $file_name, $role, $date_modif));
                                            // Redirection vers la page Liste des utilisateurs.
                                            header("Location:index.php");
                                        } else {
                                            $error = "une erreur s'est produite !";
                                        }
                                    }else {
                                        $error = "veuillez entrer une image correcte ('.png', '.jpeg', '.jpg').";
                                    }
                                }else {
                                    $error = "Oups...";
                                }
                            }else {
                                $error = "Veuillez entrer un numéro de téléphone d'au moins 9 caractères.";
                            }
                        }else {
                            $error = "veuillez entrer un mot de passe compris entre 8 et 255 caractères.";
                        }
                    }else {
                        $error = "L'e-mail doit etre moins de 255 caractères de long.";
                    }
                }else {
                    $error = "Veuillez entrer un e-mail correct !";
                }
            }else {
                $error = "Veuillez entrer un Nom qui contient entre 3 et 255 caractères.";
            }
        } else {
            $error = "veuillez remplir tous les champs !";
        }
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
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group col-md-6 py-2">
                <label for="name" class="form-label">Nom <sup style="color: red;">*</sup></label>
                <input type="text" name="name" class="form-control" id="name"
                    value="<?php if (isset($_POST['name'])) { echo $_POST['name'];} else{ echo $user->name;}?>">
            </div>
            <div class="form-group col-md-6 py-2">
                <label for="firstname" class="form-label">Prénom <sup style="color: red;">*</sup></label>
                <input type="text" name="firstname" class="form-control" id="firstname"
                    value="<?php if (isset($_POST['firstname'])) { echo $_POST['firstname'];} else{ echo $user->firstname;}?>">
            </div>
            <div class="form-group col-md-6 mt-2">
                <label for="email" class="form-label">Email <sup style="color: red;">*</sup></label>
                <input type="email" name="email" class="form-control" id="email"
                    value="<?php if (isset($_POST['email'])) { echo $_POST['email'];} else{ echo $user->email;}?>">
            </div>
            <div class="form-group col-md-6 mt-2">
                <label for="phone" class="form-label">Téléphone <sup style="color: red;">*</sup></label>
                <input type="tel" name="phone" class="form-control" id="phone"
                    value="<?php if (isset($_POST['phone'])) { echo $_POST['phone'];} else{ echo $user->phone;}?>">
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
                    <option value="1" <?php if($user->role == 1){echo "selected";} ?>>Utilisateur</option>
                    <option value="2" <?php if($user->role == 2){echo "selected";} ?>>Administrateur</option>
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