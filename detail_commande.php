<?php
session_start();
include('config.php');

// --- 1. SÉCURITÉ ET RÉCUPÉRATION DE L'ID ---

// 1.1 Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_client'])) {
    header('Location: connexion.php');
    exit();
}

$id_client = $_SESSION['id_client'];
$id_commande = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_commande === 0) {
    // Si l'ID de commande est manquant ou invalide
    header('Location: commandes.php'); 
    exit();
}

// --- 2. VÉRIFICATION DE LA PROPRIÉTÉ DE LA COMMANDE ---

// Requête pour vérifier si la commande existe ET appartient bien au client connecté
$sql_verif = "SELECT date_commande FROM commandes WHERE id = ? AND id_client = ?";
$stmt_verif = mysqli_prepare($connexion, $sql_verif);
mysqli_stmt_bind_param($stmt_verif, 'ii', $id_commande, $id_client);
mysqli_stmt_execute($stmt_verif);
$result_verif = mysqli_stmt_get_result($stmt_verif);
$commande_info = mysqli_fetch_assoc($result_verif);
mysqli_stmt_close($stmt_verif);

// Si la commande n'existe pas ou n'appartient pas à ce client
if (!$commande_info) {
    mysqli_close($connexion);
    // On redirige vers l'historique sans message d'erreur pour des raisons de sécurité
    header('Location: commandes.php'); 
    exit();
}

$date_commande = $commande_info['date_commande'];

// --- 3. RÉCUPÉRATION DES DÉTAILS DE LA COMMANDE ---

// Requête pour joindre ligne_commandes et articles
$sql_lignes = "
    SELECT 
        lc.quantite,
        lc.total AS total_ligne,
        a.nom AS nom_article,
        a.prix AS prix_vente 
    FROM 
        ligne_commandes lc
    JOIN 
        articles a ON lc.id_article = a.id
    WHERE 
        lc.id_commande = ?
";
$stmt_lignes = mysqli_prepare($connexion, $sql_lignes);
mysqli_stmt_bind_param($stmt_lignes, 'i', $id_commande);
mysqli_stmt_execute($stmt_lignes);
$result_lignes = mysqli_stmt_get_result($stmt_lignes);

$lignes_commande = [];
$total_general = 0;
while ($ligne = mysqli_fetch_assoc($result_lignes)) {
    $lignes_commande[] = $ligne;
    $total_general += $ligne['total_ligne'];
}

mysqli_stmt_close($stmt_lignes);
mysqli_close($connexion);

// --- 4. AFFICHAGE DE LA PAGE ---

echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails Commande #' . $id_commande . '</title>
    <link rel="stylesheet" href="style.css">
   
</head>
<body>';

include('header.php'); 
?>

    <div class="page-container" style="width: 80%; margin: 0 auto; padding: 20px;">
        <h1>Détails de ma Commande #<?php echo $id_commande; ?></h1>
        
        <p>
            **Date de la commande :** <?php echo htmlspecialchars($date_commande); ?>
        </p>
        
        <table class="detail-table">
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Prix Unitaire (à l'achat)</th>
                    <th>Quantité</th>
                    <th>Sous-total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lignes_commande as $ligne): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ligne['nom_article']); ?></td>
                        <td><?php echo number_format($ligne['prix_vente'], 0, ',', ' '); ?> FCFA</td> 
                        <td><?php echo $ligne['quantite']; ?></td>
                        <td><?php echo number_format($ligne['total_ligne'], 0, ',', ' '); ?> FCFA</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-summary">
            Total Payé : <?php echo number_format($total_general, 0, ',', ' '); ?> FCFA
        </div>
        
        <p style="text-align: center; margin-top: 30px;">
            <a href="commandes.php">← Retour à l'historique des commandes</a>
        </p>

    </div>

<?php
echo '</div> </body>
</html>';
?>