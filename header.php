<?php
// Note: session_start() et include('config.php') doivent se trouver au tout début 
// de chaque page principale (index.php, panier.php, etc.)

// On vérifie si l'utilisateur est connecté pour ajuster les liens
$est_connecte = isset($_SESSION['id_client']);
$nom_client = $est_connecte ? htmlspecialchars($_SESSION['nom_client']) : 'Client';

// Définition des liens pour la navigation
// Note : Le lien "Catalogue" est le même que "Accueil" ou "Index" pour les besoins du site
$liens_nav = [
    'Accueil' => 'index.php',
    'Catalogue' => 'index.php', // Utiliser index.php pour afficher le catalogue complet
    'Panier' => 'panier.php',
    'Commandes' => 'commandes.php',
    'Connexion' => 'connexion.php'
];

// Si l'utilisateur est connecté, remplacer 'Connexion' par 'Déconnexion'
if ($est_connecte) {
    unset($liens_nav['Connexion']);
    $liens_nav['Déconnexion'] = 'deconnexion.php';
} else {
    $liens_nav['Inscription'] = 'inscription.php';
}

?>

<header class="main-header">
    
    <div class="banner-top">
        <img src="images/logoenastic.png" alt="Bannière Enastic - Faites vos achats chez nous!" 
             style="width: 100%; display: block;">
    </div>
    
    <nav class="main-nav-links">
        <ul>
            <?php foreach ($liens_nav as $texte => $url): ?>
                <li><a href="<?php echo $url; ?>"><?php echo $texte; ?></a></li>
            <?php endforeach; ?>
            
            <?php 
            // Ajout du message de bienvenue pour les utilisateurs connectés
            if ($est_connecte): 
            ?>
                <li class="welcome-msg-li"><b>Bienvenue, <?php echo $nom_client; ?></b></li>
            <?php endif; ?>
        </ul>
    </nav>
    
</header>

<div class="content-wrapper">