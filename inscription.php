<?php
// On démarre la session en tout premier pour pouvoir utiliser $_SESSION
// Nécessaire ici pour que le menu (header.php) puisse vérifier la connexion
session_start(); 

// Le bloc d'ouverture de page avec le <head>, <body> et l'inclusion du menu
echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription Client</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>';

// Inclusion du menu de navigation
include('header.php');
?>

    <div class="form-container">
        <h2>Vos coordonnées (Inscription)</h2>
        
        <form action="insertion.php" method="POST">
            
            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" required>
            
            <label for="prenom">Prénom :</label>
            <input type="text" id="prenom" name="prenom" required>
            
            <label for="adresse">Adresse :</label>
            <input type="text" id="adresse" name="adresse">
            
            <label for="ville">Ville :</label>
            <input type="text" id="ville" name="ville">
            
            <label for="email">Mail :</label>
            <input type="email" id="email" name="email" required>
            
            <label for="passwrd">Passwrd :</label>
            <input type="password" id="passwrd" name="passwrd" required>
            
            <button type="submit" name="enregistrer">S'enregistrer</button>
        </form>
    </div>

<?php
// Fermeture de la page, des balises ouvertes par le header.php
echo '</div> </body>
</html>';
?>