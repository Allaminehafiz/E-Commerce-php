<?php
session_start();
include('config.php');

// Initialiser le panier si ce n'est pas fait
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Initialisation des variables pour l'affichage
$articles_panier = [];
$total_general = 0;
$message_statut = "";

// 1. Gérer les messages de statut (s'il y en a)
if (isset($_SESSION['message_statut'])) {
    $message_statut = $_SESSION['message_statut'];
    unset($_SESSION['message_statut']);
}


// 2. Traiter l'ajout d'un article (venant de catalogue.php)
if (isset($_POST['ajouter']) && isset($_POST['id_article']) && isset($_POST['quantite'])) {
    
    $id_article = (int)$_POST['id_article'];
    $quantite = (int)$_POST['quantite'];
    
    // S'assurer que les valeurs sont valides
    if ($id_article > 0 && $quantite > 0) {
        
        // Vérifier si l'article est DÉJÀ dans le panier
        if (array_key_exists($id_article, $_SESSION['panier'])) {
            $_SESSION['panier'][$id_article] += $quantite;
            $_SESSION['message_statut'] = "✅ La quantité de l'article a été mise à jour dans votre panier.";
            
        } else {
            $_SESSION['panier'][$id_article] = $quantite;
            $_SESSION['message_statut'] = "✅ $quantite article(s) ajouté(s) au panier.";
        }
        
        // REDIRECTION VERS LA PAGE DE DÉTAIL POUR AFFICHER LE MESSAGE
        header('Location: catalogue.php?id=' . $id_article);
        exit();
    }
}


// 3. Traiter la modification de quantité (depuis le panier)
if (isset($_POST['modifier'])) {
    $id_article_modifie = (int)$_POST['id_article'];
    $nouvelle_quantite = (int)$_POST['quantite'];
    
    if ($id_article_modifie > 0) {
        if ($nouvelle_quantite > 0) {
            $_SESSION['panier'][$id_article_modifie] = $nouvelle_quantite;
            $message_statut = "✅ Quantité mise à jour pour l'article #$id_article_modifie.";
        } else {
            // Si la quantité est 0, on supprime l'article
            unset($_SESSION['panier'][$id_article_modifie]);
            $message_statut = "✅ Article #$id_article_modifie retiré du panier.";
        }
    }
}


// 4. Traiter la suppression d'un article
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id'])) {
    $id_a_supprimer = (int)$_GET['id'];
    if (isset($_SESSION['panier'][$id_a_supprimer])) {
        unset($_SESSION['panier'][$id_a_supprimer]);
        $message_statut = "✅ Article retiré du panier.";
    }
    header('Location: panier.php');
    exit();
}


// 5. Récupération des données des articles dans la base de données
if (!empty($_SESSION['panier'])) {
    
    // Récupérer uniquement les IDs des articles actuellement dans le panier
    $ids_articles = array_keys($_SESSION['panier']);
    $placeholders = implode(',', array_fill(0, count($ids_articles), '?'));
    $types = str_repeat('i', count($ids_articles)); 

    $sql = "SELECT id, nom, prix FROM articles WHERE id IN ($placeholders)";
    
    $stmt = mysqli_prepare($connexion, $sql);
    call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt, $types], $ids_articles));
    
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);

    while ($article = mysqli_fetch_assoc($resultat)) {
        $id_art = $article['id'];
        $quantite = $_SESSION['panier'][$id_art];
        $sous_total = $article['prix'] * $quantite;
        $total_general += $sous_total;

        $articles_panier[$id_art] = [
            'nom' => $article['nom'],
            'prix' => $article['prix'],
            'quantite' => $quantite,
            'sous_total' => $sous_total
        ];
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($connexion);


// ==============================================
// AFFICHAGE HTML
// ==============================================
echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Panier</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>';

include('header.php'); 
?>

    <h1>Mon Panier d'Achats 🛒</h1>

    <?php if (!empty($message_statut)): ?>
        <p class="message-statut" 
           style="background-color: <?php echo (strpos($message_statut, '✅') !== false) ? '#d4edda; color: #155724; border-color: #c3e6cb;' : '#f8d7da; color: #721c24; border-color: #f5c6cb;'; ?>">
            <?php echo $message_statut; ?>
        </p>
    <?php endif; ?>

    <?php if (empty($articles_panier)): ?>
        <p style="text-align: center; font-size: 1.2em;">
            Votre panier est vide. <a href="index.php">Commencez vos achats ici</a>.
        </p>
    <?php else: ?>

        <table class="panier-table">
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Prix Unitaire</th>
                    <th>Quantité</th>
                    <th>Sous-total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles_panier as $id_art => $article): ?>
                    <tr>
                        <td><a href="catalogue.php?id=<?php echo $id_art; ?>"><?php echo htmlspecialchars($article['nom']); ?></a></td>
                        <td><?php echo number_format($article['prix'], 0, ',', ' '); ?> FCFA</td>
                        
                        <td>
                            <form action="panier.php" method="POST" style="display: flex; align-items: center;">
                                <input type="hidden" name="id_article" value="<?php echo $id_art; ?>">
                                <input type="number" name="quantite" value="<?php echo $article['quantite']; ?>" min="0" required
                                       style="width: 50px; padding: 5px;">
                                <button type="submit" name="modifier" style="margin-left: 10px; padding: 5px 10px; background-color: #f39c12; color: white; border: none; cursor: pointer;">
                                    Modifier
                                </button>
                            </form>
                        </td>
                        
                        <td><?php echo number_format($article['sous_total'], 0, ',', ' '); ?> FCFA</td>
                        <td>
                            <a href="panier.php?action=supprimer&id=<?php echo $id_art; ?>" 
                               style="color: #e74c3c; font-weight: bold; text-decoration: none;">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="panier-total">
            Total du Panier: <?php echo number_format($total_general, 0, ',', ' '); ?> FCFA
        </div>
        
        <div class="validate-button">
            <a href="commandes.php?action=valider">Valider la Commande</a>
        </div>

    <?php endif; ?>
    
<?php
echo '</div> </body>
</html>';
?>