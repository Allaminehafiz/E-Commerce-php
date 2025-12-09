<?php
session_start();
include('config.php');

$article = null;
$id_article = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message_statut = "";

// 1. Récupération du statut (pour l'UX après ajout au panier par panier.php)
if (isset($_SESSION['message_statut'])) {
    $message_statut = $_SESSION['message_statut'];
    unset($_SESSION['message_statut']);
}

// 2. Récupération des détails de l'article
if ($id_article > 0) {
    // Note: Utilisation de la colonne 'photo' qui existe dans votre structure de BDD
    $sql = "SELECT id, nom, type, prix, description, photo FROM articles WHERE id = ?";
    $stmt = mysqli_prepare($connexion, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id_article);
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);
    
    if ($resultat && mysqli_num_rows($resultat) === 1) {
        $article = mysqli_fetch_assoc($resultat);
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($connexion);

// --- AFFICHAGE HTML ---

echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>' . ($article ? htmlspecialchars($article['nom']) : 'Article Introuvable') . '</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>';

include('header.php'); 

?>

    <?php if ($article): ?>

        <h1><?php echo htmlspecialchars($article['nom']); ?></h1>

        <?php if (!empty($message_statut)): ?>
            <p class="message-statut" style="background-color: #d4edda; color: #155724; border-color: #c3e6cb;">
                <?php echo $message_statut; ?>
                <a href="panier.php" style="color: #155724; font-weight: bold; text-decoration: underline;">(Voir mon panier)</a>
            </p>
        <?php endif; ?>


        <div class="details-container">
            
            <div class="image-col">
                <img src="<?php echo htmlspecialchars($article['photo']); ?>" alt="<?php echo htmlspecialchars($article['nom']); ?>">
            </div>
            
            <div class="info-col">
                <h2>Description</h2>
                <p><?php echo nl2br(htmlspecialchars($article['description'])); ?></p>
                
                <p><strong>Type:</strong> <?php echo htmlspecialchars($article['type']); ?></p>
                
                <p class="prix">Prix: <?php echo number_format($article['prix'], 0, ',', ' '); ?> FCFA</p>
                
                <form action="panier.php" method="POST">
                    <input type="hidden" name="id_article" value="<?php echo $article['id']; ?>">
                    
                    <label for="quantite">Quantité:</label>
                    <input type="number" id="quantite" name="quantite" value="1" min="1" required 
                           style="width: 80px; padding: 5px; margin-right: 15px; display: inline-block;">
                           
                    <button type="submit" name="ajouter" class="signup-btn" style="width: auto;">
                        Ajouter au panier
                    </button>
                </form>
                
                <p style="margin-top: 20px;">
                    <a href="index.php">← Continuer mes achats</a>
                </p>
            </div>
        </div>

    <?php else: ?>
        <p style="text-align: center; font-size: 1.5em; margin-top: 50px;">
            Désolé, cet article n'existe pas ou l'identifiant est invalide.
        </p>
        <p style="text-align: center;"><a href="index.php">Retourner à l'accueil</a></p>
    <?php endif; ?>

<?php
echo '</div> 
</body>
</html>';
?>