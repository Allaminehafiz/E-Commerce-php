<?php
// Paramètres de connexion à la base de données
$serveur = "localhost"; // Généralement 'localhost' sur un environnement de développement local (comme XAMPP/WAMP)
$utilisateur = "root";  // Nom d'utilisateur par défaut (souvent 'root')
$mot_de_passe = "";     // Mot de passe par défaut (souvent vide)
$nom_base_de_donnees = "dbecom"; // Le nom de la DB que nous avons créé

// 1. Établir la connexion à MySQL
$connexion = mysqli_connect($serveur, $utilisateur, $mot_de_passe, $nom_base_de_donnees);

// 2. Vérifier si la connexion a échoué
if (!$connexion) {
    // Si la connexion échoue, on affiche un message d'erreur clair et on arrête le script.
    die("Erreur de connexion à la base de données : " . mysqli_connect_error());
}

// Optionnel: Définir l'encodage pour éviter les problèmes d'affichage des caractères (comme les accents)
mysqli_set_charset($connexion, "utf8");

// La variable $connexion est maintenant un objet de connexion utilisable dans tout le projet.
?>