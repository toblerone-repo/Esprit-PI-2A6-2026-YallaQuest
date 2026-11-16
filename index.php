<?php
// YallaQuest — point d'entrée unique de toute l'application.
//
//   Front-Office : index.php?page=<communaute|quetes|journal|competences|guildes|objectifs>
//   Back-Office  : index.php?admin=<tableau-de-bord|publications|signalements|quetes|activites|
//                                   types-activite|competences|totems|guildes|objectifs>
//
// Toutes les vues sont affichées depuis ce fichier : leurs liens, formulaires et ressources
// (css, js, img) sont donc écrits par rapport à la racine du projet.

define('YQ_ROOT', __DIR__);
define('YQ_FRONT', YQ_ROOT . '/front-office');
define('YQ_BACK', YQ_ROOT . '/back-office');

require_once YQ_BACK . '/src/config.php';

$frontPages = [
    'communaute'  => 'Communauté',
    'quetes'      => 'Quêtes',
    'journal'     => "Journal d'activité",
    'competences' => 'Compétences',
    'guildes'     => 'Guildes',
    'objectifs'   => 'Objectifs',
];
$adminPages = [
    'tableau-de-bord' => 'Tableau de bord',
    'publications'    => 'Publications',
    'signalements'    => 'Signalements',
    'quetes'          => 'Quêtes',
    'activites'       => 'Activités',
    'types-activite'  => "Types d'activité",
    'competences'     => 'Compétences',
    'totems'          => 'Totems',
    'guildes'         => 'Guildes',
    'objectifs'       => 'Objectifs',
];

function yq_not_found()
{
    http_response_code(404);
    echo '<!doctype html><meta charset="utf-8"><title>Page introuvable</title>'
        . '<p>Page introuvable. <a href="index.php">Retour à l’accueil</a></p>';
    exit;
}

// ------------------------------------------------------------------ Back-Office
if (isset($_GET['admin'])) {
    // L'administration reste réservée à un accès local (ancien back-office/.htaccess « Require local »).
    $remote = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    if (!in_array($remote, ['127.0.0.1', '::1'], true)) {
        http_response_code(403);
        exit('Administration réservée à un accès local.');
    }

    $adminPage = (string) $_GET['admin'];
    if ($adminPage === '') {
        $adminPage = 'publications';
    }
    if (!isset($adminPages[$adminPage])) {
        yq_not_found();
    }

    switch ($adminPage) {
        case 'tableau-de-bord':
            $_GET['action'] = 'tableau-de-bord';
            // no break : le tableau de bord fait partie du module Publications
        case 'publications':
            require_once YQ_BACK . '/app/Controllers/PublicationsController.php';
            (new PublicationsController())->index();
            break;
        case 'signalements':
            require_once YQ_BACK . '/app/Controllers/SignalementsController.php';
            (new SignalementsController())->index();
            break;
        case 'objectifs':
            require_once YQ_BACK . '/app/Controllers/AdminController.php';
            require YQ_BACK . '/app/Views/pages/objectifs.php';
            break;
        default:
            // pages statiques : quetes, activites, types-activite, competences, totems, guildes
            require YQ_BACK . '/app/Views/pages/' . $adminPage . '.php';
    }
    exit;
}

// ------------------------------------------------------------------ Front-Office
$page = isset($_GET['page']) ? (string) $_GET['page'] : 'communaute';
if (!isset($frontPages[$page])) {
    yq_not_found();
}

switch ($page) {
    case 'communaute':
        require_once YQ_FRONT . '/Controllers/CommunauteController.php';
        try {
            (new CommunauteController())->index();
        } catch (PDOException $error) {
            error_log($error->getMessage());
            http_response_code(503);
            $pageTitle = 'Communauté momentanément indisponible — YallaQuest';
            require YQ_FRONT . '/includes/pages/unavailable.php';
        }
        break;
    case 'objectifs':
        require_once YQ_FRONT . '/Controllers/ObjectifC.php';
        require YQ_FRONT . '/includes/pages/objectifs.php';
        break;
    default:
        // pages sans contrôleur : quetes, journal, competences, guildes
        require YQ_FRONT . '/includes/pages/' . $page . '.php';
}
