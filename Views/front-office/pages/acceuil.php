<?php
require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();
$currentPage = 'acceuil';
$pageTitle = "Page d'acceuil · YallaQuest";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require_once __DIR__ . '/../includes/head.php'; ?>
</head>
<body style="margin: 0;">
    <?php include_once __DIR__ . '/../includes/navbar.php'; ?>

    <main style="padding: 2rem;">
        <h1>Page d'acceuil</h1>
    </main>

    <?php include_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
