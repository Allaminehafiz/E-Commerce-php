<?php
// 0. DÉMARRER LA SESSION
// Ceci doit être la première instruction PHP si vous voulez utiliser $_SESSION
session_start();

// 1. Inclure le fichier de connexion à la base de données
include('config.php');

// 2. Vérifier si le formulaire a été soumis
if (isset($_POST['connexion'])) {
    
    // Récupérer et nettoyer l'e-mail
    $email_saisi = htmlspecialchars($_POST['email']);
    // Récupérer le mot de passe (on ne le nettoie pas, car il sera haché)
    $passwrd_saisi = $_POST['passwrd']; 

    // 3. Préparer la requête pour trouver l'utilisateur par e-mail
    $sql = "SELECT id, nom, prenom, passwrd FROM clients WHERE email = ?";
    
    $stmt = mysqli_prepare($connexion, $sql);
    
    // Lier le paramètre (s pour string)
    mysqli_stmt_bind_param($stmt, 's', $email_saisi);
    
    // Exécuter la requête
    mysqli_stmt_execute($stmt);
    
    $resultat = mysqli_stmt_get_result($stmt);
    $client = mysqli_fetch_assoc($resultat); // Récupérer la ligne (le client)

    // 4. Vérifier si l'utilisateur existe ET si le mot de passe est correct
    if ($client && password_verify($passwrd_saisi, $client['passwrd'])) {
        
        // --- CONNEXION RÉUSSIE ---
        
        // 5. Créer les variables de session
        // C'est ce qui permet de dire "Cet utilisateur est connecté" sur TOUTES les autres pages
        $_SESSION['id_client'] = $client['id'];
        $_SESSION['nom_client'] = $client['nom'];
        $_SESSION['prenom_client'] = $client['prenom'];
        
        // 6. Rediriger vers la page des commandes (selon le cahier des charges)
        header('Location: commandes.php'); // Nous allons créer cette page plus tard
        exit(); // Toujours appeler exit() après une redirection

    } else {
        // --- ÉCHEC DE CONNEXION ---
        
        // Stocker un message d'erreur pour l'afficher sur la page de connexion
        $_SESSION['erreur_connexion'] = "❌ E-mail ou mot de passe incorrect.";
        
        // Rediriger vers la page de connexion
        header('Location: connexion.php');
        exit();
    }
    
    // Fermeture du statement et de la connexion
    mysqli_stmt_close($stmt);
}

mysqli_close($connexion);
?>