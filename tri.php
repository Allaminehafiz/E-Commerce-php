<?php
session_start(); // Nécessaire pour l'affichage du menu (connexion/déconnexion)
include('config.php');

$types_disponibles = [];
$resultat_tri = null;
$type_selectionne = "";
$message_statut = "";

// 1. Récupérer la liste des types uniques (distincts)
$sql_types = "SELECT DISTINCT type FROM articles ORDER BY type ASC";
$resultat_types = mysqli_query($connexion, $sql_types);

if ($resultat_types) {
    while ($row = mysqli_fetch_assoc($resultat_types)) {
        if (!empty($row['type'])) {
            $types_disponibles[] = $row['type'];
        }
    }
    mysqli_free_result($resultat_types);
}


// 2. Traitement du formulaire de tri (si soumis)
if (isset($_POST['trier']) && isset($_POST['type'])) {
    
    $type_selectionne = htmlspecialchars($_POST['type']);
    
    // 3. Construction et exécution de la requête préparée
    $sql_tri = "SELECT nom, type, prix FROM articles WHERE type = ?";
    $stmt_tri = mysqli_prepare($connexion, $sql_tri);
    mysqli_stmt_bind_param($stmt_tri, 's', $type_selectionne);
    mysqli_stmt_execute($stmt_tri);
    
    $resultat_tri = mysqli_stmt_get_result($stmt_tri);
    
    // 4. Affichage du statut
    if ($resultat_tri && mysqli_num_rows($resultat_tri) > 0) {
        $nombre_resultats = mysqli_num_rows($resultat_tri);
        $message_statut = "✅ $nombre_resultats article(s) trouvé(s) pour le type : **$type_selectionne**.";
    } else {
        $message_statut = "❌ Aucun article trouvé pour le type : **$type_selectionne**.";
    }
    
    mysqli_stmt_close($stmt_tri);
}

mysqli_close($connexion);

// ==============================================
// 1. OUVERTURE DE PAGE ET INCLUSION DU HEADER (MENU)
// ==============================================
echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tri par Type</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>';

include('header.php');
?>
    
    <div class="tri-container">
        <h2>Tri des articles par type</h2>
        
        <form action="tri.php" method="POST">
            <label for="type">Sélectionner un type:</label>
            <select name="type" id="type" required>
                <option value="">-- Choisir un type --</option>
                <?php foreach ($types_disponibles as $type): ?>
                    <option value="<?php echo htmlspecialchars($type); ?>" 
                            <?php if ($type == $type_selectionne) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($type); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" name="trier">Trier</button>
        </form>
    </div>

    <?php if (!empty($message_statut)): ?>
        <p style="text-align: center; font-weight: bold;"><?php echo $message_statut; ?></p>
    <?php endif; ?>

    <?php 
    // Affichage des résultats
    if ($resultat_tri && mysqli_num_rows($resultat_tri) > 0): 
    ?>
        <table class="result-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Type</th>
                    <th>Prix</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($article = mysqli_fetch_assoc($resultat_tri)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($article['nom']); ?></td>
                    <td><?php echo htmlspecialchars($article['type']); ?></td>
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