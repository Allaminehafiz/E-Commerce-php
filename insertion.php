<?php
// 1. Inclure le fichier de connexion à la base de données
include('config.php');

// 2. Vérifier si le formulaire a été soumis (si le bouton 'S'enregistrer' a été cliqué)
if (isset($_POST['enregistrer'])) {
    
    // 3. Récupérer et nettoyer les données du formulaire (Sécurité : htmlspecialchars)
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $adresse = htmlspecialchars($_POST['adresse']);
    $ville = htmlspecialchars($_POST['ville']);
    $email = htmlspecialchars($_POST['email']);
    $mot_de_passe_clair = $_POST['passwrd']; // Le mot de passe non haché

    // 4. Hacher le mot de passe (Cryptage)
    // C'est ESSENTIEL pour la sécurité. On ne stocke JAMAIS les mots de passe en clair.
    $mot_de_passe_hache = password_hash($mot_de_passe_clair, PASSWORD_DEFAULT);
    
    // 5. Préparation de la requête INSERT (utilisant des requêtes préparées pour la sécurité)
    $sql = "INSERT INTO clients (nom, prenom, adresse, ville, email, passwrd) VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($connexion, $sql);
    
    // 6. Lier les paramètres à la requête
    // 'ssssss' signifie 6 chaînes de caractères (string)
    mysqli_stmt_bind_param($stmt, 'ssssss', $nom, $prenom, $adresse, $ville, $email, $mot_de_passe_hache);
    
    // 7. Exécuter la requête
    if (mysqli_stmt_execute($stmt)) {
        $message = "✅ Inscription réussie ! Bienvenue, $nom $prenom. Vous pouvez maintenant vous connecter.";
    } else {
        // Gérer les erreurs, par exemple si l'e-mail est déjà utilisé (si la contrainte UNIQUE a été remise)
        $message = "❌ Erreur lors de l'enregistrement : " . mysqli_error($connexion);
    }
    
    // 8. Fermeture du statement
    mysqli_stmt_close($stmt);

} else {
    $message = "Accès non autorisé. Veuillez passer par le formulaire d'inscription.";
}

// 9. Fermer la connexion à la DB
mysqli_close($connexion);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation d'Inscription</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div style="text-align: center; margin-top: 50px;">
        <h1>Statut de l'Inscription</h1>
        <p style="font-size: 1.2em;"><?php echo $message; ?></p>
        <p><a href="connexion.php">Cliquez ici pour vous connecter</a></p>
    </div>
</body>
</html>