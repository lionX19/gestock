<?php
session_start();

// Récupérer le message d'erreur depuis l'URL ou définir un message par défaut
$error_message = isset($_GET['message']) ? htmlspecialchars(urldecode($_GET['message'])) : 'Une erreur est survenue. Veuillez réessayer plus tard.';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        .error-container {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .error-image {
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
            border-radius: 10px;
        }

        .error-message {
            font-size: 1.5rem;
            color: #dc3545;
            /* Rouge Bootstrap */
            margin-bottom: 20px;
        }

        @media (max-width: 576px) {
            .error-image {
                max-width: 80%;
            }

            .error-message {
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <?php include("includes/navbar.php"); ?>
    <!-- Navbar -->

    <!-- Début Contenu Principal -->
    <main>
        <div class="container error-container">
            <div>
                <!-- Image de chantier -->
                <img src="assets/images/error.jpg"
                    alt="Chantier en cours"
                    class="error-image">
                <!-- Message d'erreur -->
                <p class="error-message"><?php echo $error_message; ?></p>
                <!-- Bouton de retour -->
                <a href="en_chantier.php" class="btn btn-primary">Retour à l'accueil</a>
            </div>
        </div>
    </main>
    <!-- Fin Contenu Principal -->

    <!-- Footer -->
    <?php include("includes/footer.php"); ?>
    <!-- Footer -->

    <!-- Inclure jQuery et Bootstrap JS -->
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>