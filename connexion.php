<?php
// On démarre la session en tout premier pour pouvoir utiliser $_SESSION
session_start();

// Le bloc d'ouverture de page avec le <head>, <body> et l'inclusion du menu
echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Client</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>';

// Inclusion du menu de navigation
include('header.php');

// Pour l'étape 3 des améliorations, nous allons afficher un éventuel message d'erreur
if (isset($_SESSION['erreur_connexion'])) {
    echo '<div style="color: red; text-align: center; padding: 10px; border: 1px solid red; width: 400px; margin: 20px auto;">';
    echo htmlspecialchars($_SESSION['erreur_connexion']);
    echo '</div>';
    // Une fois affiché, on supprime le message pour qu\'il ne réapparaisse pas au prochain chargement
    unset($_SESSION['erreur_connexion']); 
}
?>

    <div class="form-container">
        <h2>Connexion Client</h2>
        
        <form action="traitement.php" method="POST">
            
            <label for="email">Mail :</label>
            <input type="email" id="email" name="email" required>
            
            <label for="passwrd">Mot de passe :</label>
            <input type="password" id="passwrd" name="passwrd" required>
            
            <button type="submit" name="connexion">Se connecter</button>
        </form>
        
        <p style="text-align: center; margin-top: 15px;">
            Pas encore de compte ? <a href="inscription.php">S'inscrire ici</a>
        </p>
    </div>

<?php
// Fermeture de la page, des balises ouvertes par le header.php
echo '</div> </body>
</html>';
?>