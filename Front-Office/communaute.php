<?php
require_once __DIR__ . '/Controller/CommunauteController.php';

// Le contrôleur prépare les données, puis affiche la vue.
try {
    $controller = new CommunauteController();
    $controller->index();
} catch (PDOException $error) {
    error_log($error->getMessage());
    http_response_code(503);
    $pageTitle = 'Communauté momentanément indisponible — YallaQuest';
    require __DIR__ . '/View/unavailable.php';
}
