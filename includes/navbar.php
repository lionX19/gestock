<nav class="navbar navbar-expand-sm navbar-dark bg-dark" aria-label="Third navbar example">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Tableau de Bord</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample03"
            aria-controls="navbarsExample03" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarsExample03">
            <ul class="navbar-nav me-auto mb-2 mb-sm-0">
                <!-- <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#">Home</a>
                </li> -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="dropdown03" data-bs-toggle="dropdown"
                        aria-expanded="false">Gestion des produits</a>
                    <ul class="dropdown-menu" aria-labelledby="dropdown03">
                        <li><a class="dropdown-item text-secondary" href="../produits/index.php">Produits</a>
                        </li>
                        <li><a class="dropdown-item text-primary" href="../mouvements/index.php">Mouvements</a>
                        </li>
                        <li><a class="dropdown-item text-primary" href="../mouvements/historique.php">Historique</a>
                        </li>
                        <li><a class="dropdown-item text-primary" href="../produits/stat.php">Statistiques</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="dropdown03" data-bs-toggle="dropdown"
                        aria-expanded="false">Bienvenue
                        <?php if (isset($_SESSION["name"])) {
                            echo $_SESSION["name"];
                        } ?>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="dropdown03">
                        <li><a class="dropdown-item text-success" href="#">Accueil</a></li>
                        <li><a class="dropdown-item text-primary" href="../users/profile.php">Profil</a></li>
                        <li><a class="dropdown-item text-danger" href="../logout.php">Déconnexion</a></li>
                    </ul>
                </li>
            </ul>
            <form>
                <input class="form-control" type="text" placeholder="Search" aria-label="Search">
            </form>
        </div>
    </div>
</nav>