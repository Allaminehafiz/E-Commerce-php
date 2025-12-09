<?php
// 1. Démarrer la session pour s'assurer que l'on travaille sur la bonne session
session_start();

// 2. Détruire toutes les variables de session
$_SESSION = array();

// 3. Si vous utilisez des cookies de session, détruire le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Détruire la session
session_destroy();

// 5. Rediriger l'utilisateur vers la page d'accueil ou de connexion
header('Location: index.php');
exit();
?>