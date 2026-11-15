<?php
require_once __DIR__ . '/Controller/PublicationsController.php';
require_once __DIR__ . '/Controller/SignalementsController.php';

if (isset($_GET['module']) && $_GET['module'] == 'signalements') {
    $controller = new SignalementsController();
} else {
    $controller = new PublicationsController();
}
$controller->index();
