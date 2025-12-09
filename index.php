<?php
session_start(); // Nécessaire ici pour le menu dans header.php (connexion/déconnexion)

include('config.php');



// Début de l'en-tête HTML, y compris le <head>, <body> et l'inclusion du menu
echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil | Liste des Articles</title> 
    <link rel="stylesheet" href="style.css">
</head>
<body>';

// Inclusion du menu

include('header.php');


// 2. Préparer la requête SQL pour récupérer tous les articles
$sql = "SELECT id, nom, prix, photo FROM articles";

// 3. Exécuter la requête sur la connexion ($connexion)
$resultat = mysqli_query($connexion, $sql);

// 4. Vérifier si la requête a réussi et s'il y a des résultats
if ($resultat && mysqli_num_rows($resultat) > 0) {
    
    echo '<div class="promotion-banner">
        <img src="images/banniere2.jpg" alt="Bannière de promotion" style="width: 100%; height: auto; display: block;">
    </div>';
    echo '<div class="article-grid">';
    
    // 5. Boucle pour parcourir chaque ligne (chaque article) du résultat
    while ($article = mysqli_fetch_assoc($resultat)) {
    ?>
        <div class="article-card">
            <a href="catalogue.php?id=<?php echo $article['id']; ?>">
                <img src="<?php echo htmlspecialchars($article['photo']); ?>" alt="<?php echo $article['nom']; ?>">
                <h3><?php echo htmlspecialchars($article['nom']); ?></h3>
            </a>
            <p>Prix: **<?php echo number_format($article['prix'], 0, ',', ' '); ?> FCFA**</p>
        </div>
    <?php
    } // Fin de la boucle while
    
    echo '</div>'; // Fin .article-grid
    
} else {
    // Si la requête n'a pas renvoyé d'article
    echo "<p>Aucun article trouvé pour le moment.</p>";
}

// 6. Libérer la mémoire utilisée par le résultat de la requête
mysqli_free_result($resultat);

// 7. Bonne pratique : fermer la connexion à la fin du script
mysqli_close($connexion);

// Fermeture des balises ouvertes par l'echo initial et le header.php
echo '</div> 
</body>
</html>';
?>