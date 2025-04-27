<?php
require_once('../db/db.php');

if (isset($_POST["btnSubmit"])) {
    if (!empty($_POST["name"]) AND !empty($_POST["firstname"]) AND !empty($_POST["email"]) AND !empty($_POST["phone"]) AND
        !empty($_POST["role"])) {
        
        // Récupération des champs du formulaire
        $name = trim(htmlspecialchars($_POST["name"]));
        $firstname = trim(htmlspecialchars($_POST["firstname"]));
        $email = trim(htmlspecialchars($_POST["email"]));
        $phone = trim(htmlspecialchars($_POST["phone"]));
        if(empty($_POST["password"])) {
            $password = "password";
        }else {
            $password = trim(htmlspecialchars($_POST["password"]));
        }
        $role = trim(htmlspecialchars($_POST["role"]));
        $avatarName = $_FILES["avatar"]["name"];

        //Gestion de l'image
        $valid_extension = array('png', 'jpeg', 'jpg'); //Liste des extensions valides.
        $avatar_path = pathinfo($avatarName, PATHINFO_EXTENSION); //récupérer l'extension du fichier.
        $file_tmp = $_FILES["avatar"]["tmp_name"]; //Emplacement temporaire du fichier.
        $file_name = $name."_".$avatarName; // nom du fichier stocké dans le dossier images.
        die(var_dump($avatarName));
        // Validation des champs
        if (strlen($name) >= 3 AND strlen($name) <= 255) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if (strlen($email) <= 255) {
                    // Comparaison de l'email de l'utilisateur à ceux déjà présent en base de données.
                    $query = $pdo->prepare("SELECT email FROM users WHERE email=?");
                    $query->execute(array($email));
                    
                    //Si pas de correspondance, on poursuit notre insertion en base de données.
                    if ($query->rowCount() != 1) {
                        if (strlen($phone) >= 9 AND strlen($phone) <= 15) {
                            if (strlen($password) >= 8 AND strlen($password) <= 255) {
                                $password_crypted = sha1($password);
                                if ($role == 1 OR $role == 2) { // Vérifie si le role appartient soit à l'utilisateur simple (1), ou à l'administrateur (2).
                                    //Si le champ de l'image est vide, on change la valeur du nom du fichier par le nom de l'image par défaut.
                                    if(empty($avatarName)) {
                                        $file_name = "default.png"; //Nom de l'image par défaut.
                                        // insérer les données en base de données.
                                        $req = $pdo->prepare("INSERT INTO users SET name=?, firstname=?, email=?, 
                                        password=?, phone=?, avatar=?, role=?");
                                        $req->execute(array($name, $firstname, $email, $password_crypted, $phone, 
                                            $file_name, $role));
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
                                            $req = $pdo->prepare("INSERT INTO users SET name=?, firstname=?, email=?, 
                                                password=?, phone=?, avatar=?, role=?");
                                            $req->execute(array($name, $firstname, $email, $password_crypted, $phone, 
                                                $file_name, $role));
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
                                $error = "veuillez entrer un mot de passe compris entre 8 et 255 caractères.";
                            }
                        }else {
                            $error = "veuillez entrer un numéro de téléphone compris entre 9 et 15 caractères.";
                        }
                    }else {
                        $error = "Cet email existe déjà en base de données !";
                    }
                } else {
                    $error = "L'e-mail doit comporter moins de 255 caractères de long.";
                }
            } else {
                $error = "Veuillez entrer un e-mail correct.";
            }
        } else {
            $error = "Veuillez entrer un Nom qui contient entre 3 et 255 caractères.";
        }
        
    } else {
        $error = "Veuillez remplir tous les champs !";
    }
}

?>