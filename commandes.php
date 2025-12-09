<?php
session_start();
include('config.php');

// Initialisation des variables
$id_client = $_SESSION['id_client'] ?? null;
$historique_commandes = [];
$message_statut = "";

// 1. Gérer les messages de statut (succès ou échec de la validation)
if (isset($_SESSION['message_statut'])) {
    $message_statut = $_SESSION['message_statut'];
    unset($_SESSION['message_statut']);
}

// 2. Vérification de la connexion pour accéder à l'historique
if (!$id_client) {
    // Si l'utilisateur essaie de valider SANS être connecté, on le redirige immédiatement
    if (isset($_GET['action']) && $_GET['action'] == 'valider') {
        $_SESSION['message_statut'] = "Vous devez être connecté pour valider une commande.";
        header('Location: connexion.php');
        exit();
    }
    // Si l'utilisateur essaie de voir l'historique sans être connecté
    // Le reste du script affichera la page, mais sans commandes.
}


// 3. TRAITEMENT DE LA VALIDATION DE COMMANDE (Logique Transactionnelle)
if ($id_client && isset($_GET['action']) && $_GET['action'] == 'valider') {

    // Vérification cruciale : le panier doit être non vide
    if (empty($_SESSION['panier'])) {
        $_SESSION['message_statut'] = "Votre panier est vide. Ajoutez des articles avant de commander.";
        header('Location: panier.php');
        exit();
    }
    
    $articles_panier = $_SESSION['panier'];
    
    // DÉBUT DE LA TRANSACTION
    mysqli_begin_transaction($connexion);
    $transaction_success = true; 
    
    try {
        // A. Insérer la commande principale
        $sql_commande = "INSERT INTO commandes (id_client, date_commande) VALUES (?, NOW())";
        $stmt_commande = mysqli_prepare($connexion, $sql_commande);
        mysqli_stmt_bind_param($stmt_commande, 'i', $id_client);
        
        if (!mysqli_stmt_execute($stmt_commande)) {
            $transaction_success = false;
            throw new Exception("Erreur lors de l'enregistrement de la commande principale.");
        }
        
        $id_commande = mysqli_insert_id($connexion);
        mysqli_stmt_close($stmt_commande);


        // B. Préparer les données pour les lignes de commande
        $ids = implode(',', array_keys($articles_panier));
        // On récupère le prix actuel des articles (le prix payé)
        $sql_prix = "SELECT id, prix FROM articles WHERE id IN ($ids)"; 
        $result_prix = mysqli_query($connexion, $sql_prix);
        
        $articles_prix = [];
        while($row = mysqli_fetch_assoc($result_prix)) {
            $articles_prix[$row['id']] = $row['prix'];
        }
        
        // C. Insérer chaque ligne de commande
        $sql_ligne = "INSERT INTO ligne_commandes (id_commande, id_article, quantite, total) VALUES (?, ?, ?, ?)";
        $stmt_ligne = mysqli_prepare($connexion, $sql_ligne);
        
        foreach ($articles_panier as $id_article => $quantite) {
            
            $prix_unitaire = $articles_prix[$id_article] ?? 0;
            if ($prix_unitaire == 0) {
                $transaction_success = false;
                throw new Exception("Article ID: $id_article introuvable ou prix nul. Transaction annulée.");
            }
            
            $sous_total = $prix_unitaire * $quantite;
            
            // i i i d : int, int, int, decimal (ou float)
            mysqli_stmt_bind_param($stmt_ligne, 'iiid', $id_commande, $id_article, $quantite, $sous_total);
            
            if (!mysqli_stmt_execute($stmt_ligne)) {
                $transaction_success = false;
                throw new Exception("Erreur lors de l'enregistrement de la ligne de commande pour l'article $id_article.");
            }
        }
        
        mysqli_stmt_close($stmt_ligne);


        // D. Si tout a réussi : Valider (COMMIT)
        if ($transaction_success) {
            mysqli_commit($connexion);
            
            unset($_SESSION['panier']); // Vider le panier
            $_SESSION['message_statut'] = "🎉 Votre commande #$id_commande a été validée avec succès !";
            
            header('Location: commandes.php');
            exit();
        }

    } catch (Exception $e) {
        // E. En cas d'erreur : Annuler (ROLLBACK)
        mysqli_rollback($connexion);
        
        $_SESSION['message_statut'] = "❌ Échec de la commande : " . $e->getMessage();
        
        header('Location: panier.php'); // Rediriger vers le panier avec le message d'erreur
        exit();
    }
}


// 4. RÉCUPÉRATION DE L'HISTORIQUE POUR L'AFFICHAGE
if ($id_client) {
    // Requête pour récupérer toutes les commandes du client
    $sql_historique = "SELECT id, date_commande FROM commandes WHERE id_client = ? ORDER BY date_commande DESC";
    $stmt_historique = mysqli_prepare($connexion, $sql_historique);
    mysqli_stmt_bind_param($stmt_historique, 'i', $id_client);
    mysqli_stmt_execute($stmt_historique);
    $result_historique = mysqli_stmt_get_result($stmt_historique);

    while ($commande = mysqli_fetch_assoc($result_historique)) {
        $historique_commandes[] = $commande;
    }
    mysqli_stmt_close($stmt_historique);
}

mysqli_close($connexion);


// ==============================================
// AFFICHAGE HTML
// ==============================================
echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique de mes Commandes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>';

include('header.php'); 
?>

    <h1>Historique de mes Commandes</h1>

    <?php if (!empty($message_statut)): ?>
        <p class="message-statut" 
           style="background-color: <?php echo (strpos($message_statut, 'succès') !== false) ? '#d4edda; color: #155724; border-color: #c3e6cb;' : '#f8d7da; color: #721c24; border-color: #f5c6cb;'; ?>">
            <?php echo $message_statut; ?>
        </p>
    <?php endif; ?>

    <?php if (!$id_client): ?>
        <p style="text-align: center; font-size: 1.2em;">
            Vous devez être connecté pour consulter votre historique de commandes. 
            <a href="connexion.php">Se connecter ici</a>.
        </p>
    <?php elseif (empty($historique_commandes)): ?>
        <p style="text-align: center; font-size: 1.2em;">
            Vous n'avez pas encore passé de commande. 
            <a href="index.php">Commencez vos achats</a>.
        </p>
    <?php else: ?>

        <table class="historique-table">
            <thead>
                <tr>
                    <th>ID Commande</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historique_commandes as $commande): ?>
                    <tr>
                        <td>#<?php echo $commande['id']; ?></td>
                        <td><?php echo $commande['date_commande']; ?></td>
                        <td>
                            <a href="detail_commande.php?id=<?php echo $commande['id']; ?>">Voir les articles</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>
    
<?php
echo '</div> </body>
</html>';
?>