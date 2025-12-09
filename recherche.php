<?php
session_start(); // Nécessaire pour l'affichage du menu (connexion/déconnexion)
include('config.php'); // Connexion à la base de données

// Initialisation des variables pour les champs du formulaire et les résultats
$prix_min = 0;
$prix_max = 9999999; // Une valeur très haute par défaut
$resultat_recherche = null;
$message_statut = "";

// 1. Vérifier si le formulaire de recherche a été soumis
if (isset($_POST['rechercher'])) {
    
    // 2. Récupérer et nettoyer les valeurs (s'assurer que ce sont des entiers)
    $prix_min = isset($_POST['prix_min']) ? (int)$_POST['prix_min'] : 0;
    $prix_max = isset($_POST['prix_max']) ? (int)$_POST['prix_max'] : $prix_max;

    // 3. Construction de la requête SQL
    $sql = "SELECT nom, prix FROM articles WHERE prix >= ? AND prix <= ?";
    
    // 4. Exécution de la requête préparée pour la sécurité
    $stmt = mysqli_prepare($connexion, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $prix_min, $prix_max); // 'ii' pour deux entiers
    mysqli_stmt_execute($stmt);
    
    $resultat_recherche = mysqli_stmt_get_result($stmt);
    
    // 5. Affichage du statut
    if ($resultat_recherche && mysqli_num_rows($resultat_recherche) > 0) {
        $nombre_resultats = mysqli_num_rows($resultat_recherche);
        $message_statut = "✅ $nombre_resultats article(s) trouvé(s) entre " . number_format($prix_min) . " FCFA et " . number_format($prix_max) . " FCFA.";
    } else {
        $message_statut = "❌ Aucun article ne correspond à ces critères de prix.";
    }
    
    mysqli_stmt_close($stmt);
}

mysqli_close($connexion);

// ==============================================
// 1. OUVERTURE DE PAGE ET INCLUSION DU HEADER (MENU)
// ==============================================
echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche par Prix</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>';

include('header.php');
?>
    
    <div class="search-container">
        <h2>Recherche d'articles par Prix</h2>
        
        <form action="recherche.php" method="POST">
            <label for="prix_min">Prix Min:</label>
            <input type="number" id="prix_min" name="prix_min" value="<?php echo htmlspecialchars($prix_min); ?>" required>
            
            <label for="prix_max">Prix Max:</label>
            <input type="number" id="prix_max" name="prix_max" value="<?php echo htmlspecialchars(isset($_POST['prix_max']) ? $_POST['prix_max'] : ''); ?>" required>
            
            <button type="submit" name="rechercher">Rechercher</button>
        </form>
    </div>

    <?php if (!empty($message_statut)): ?>
        <p style="text-align: center; font-weight: bold;"><?php echo $message_statut; ?></p>
    <?php endif; ?>

    <?php 
    // Affichage des résultats si la requête a été exécutée et a retourné des lignes
    if ($resultat_recherche && mysqli_num_rows($resultat_recherche) > 0): 
    ?>
        <table class="result-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prix</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($article = mysqli_fetch_assoc($resultat_recherche)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($article['nom']); ?></td>
                    <td><?php echo number_format($article['prix'], 0, ',', ' '); ?> FCFA</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
<?php
// ==============================================
// 2. FERMETURE DE PAGE
// ==============================================
echo '</div> </body>
</html>';
?>