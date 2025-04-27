<?php
session_start();
require_once("../db/db.php");

$req = $pdo->query("SELECT * FROM users ORDER BY id_user DESC LIMIT 0,9");
$i = 1;
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des utilisateurs</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>

<body>
    <!-- Navbar -->
    <?php include("../includes/navbar.php"); ?>
    <!-- Navbar -->

    <!-- début Contenu Principal -->
    <main>
        <div class="container">
            <h1 class="text-start my-3">Liste des utilisateurs
                <a href="create.php" class="btn btn-outline-success">Ajouter</a>
            </h1>

            <table class="table">
                <thead class="table-dark">
                    <th>#</th>
                    <th>Avatar</th>
                    <th>Noms & Prénoms</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Role</th>
                    <th>Actions</th>
                </thead>
                <tbody>
                    <?php while ($user = $req->fetch()) : ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><img src="../assets/images/<?= $user->avatar ?>" width="72" height="57" class="rounded-4"
                                    alt="mon image"></td>
                            <td><?= $user->name . " " . $user->firstname ?></td>
                            <td><?= $user->email ?></td>
                            <td><?= $user->phone ?></td>
                            <td>
                                <?php
                                if ($user->role == 1) {
                                    echo '<span class="badge rounded-pill bg-primary p-2">Utilisateur</span>';
                                } else {
                                    echo '<span class="badge rounded-pill bg-success p-2">Administrateur</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <a class="btn btn-outline-primary" href="edit.php?id=<?= $user->id_user ?>">Editer</a>
                                <a class="btn btn-outline-danger" onclick="alert('Voulez-vous vraiment supprimer ?')"
                                    href="delete.php?id=<?= $user->id_user ?>">Supprimer
                                </a>
                            </td>
                        </tr>
                    <?php endwhile ?>
                </tbody>
                <tfoot class="table-dark">
                    <th>#</th>
                    <th>Avatar</th>
                    <th>Noms & Prénoms</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tfoot>
            </table>
        </div>
    </main>
    <!-- Fin Contenu Principal -->

    <!-- Footer -->
    <?php include("../includes/footer.php"); ?>
    <!-- Footer -->
</body>
<script src="../assets/js/bootstrap.bundle.min.js"></script>

</html>