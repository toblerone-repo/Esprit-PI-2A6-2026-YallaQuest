<?php
// Darine : structure commune. Amine : gabarit du routeur. Houssem : sorties échappées.
/* Gabarit du front-office pour les vues affichées par le routeur (index.php?action=...).
   La vue prépare $title et $content (ob_start() puis ob_get_clean()), et éventuellement
   $styles (feuilles en plus, chemins relatifs à Views/front-office/pages/), puis fait :
       require __DIR__ . '/../includes/layout.php';
   <base> : la page est servie par index.php (racine du projet), alors que la navbar, le pied de page
   et les feuilles de style utilisent des chemins relatifs à Views/front-office/pages/.
   Les liens vers le routeur s'écrivent donc ../../../index.php?action=... */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="Views/front-office/pages/">
    <title><?= htmlspecialchars($title ?? 'YallaQuest', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/profil.css">
    <?php foreach ($styles ?? [] as $style): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($style, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
    <?php endforeach; ?>
</head>

<body class="page-profil">
<?php include_once __DIR__ . '/navbar.php'; ?>

<?= $content ?? '' ?>

<?php include_once __DIR__ . '/footer.php'; ?>

<?php if (isset($sessionLifetime, $_SESSION['user_id'])): ?>
<!-- Compteur d'inactivité : s'ouvre avant la fermeture de la session (Views/js/session-timeout.js).
     $sessionLifetime vient de session.php (durée d'inactivité autorisée, en secondes). -->
<link rel="stylesheet" href="../../css/session-timeout.css">
<div id="yqSessionTimeout" class="yq-session-timeout" role="alertdialog" aria-labelledby="yqSessionTimeoutTitre" aria-describedby="yqSessionTimeoutTexte" hidden
     data-duree="<?= (int) $sessionLifetime ?>"
     data-ping="../../../index.php?action=ping"
     data-deconnexion="../../../index.php?action=logout"
     data-expiree="../../../index.php?action=logout&amp;expire=1"
     data-csrf="<?= htmlspecialchars(AuthController::getCsrfToken(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
    <p id="yqSessionTimeoutTitre" class="yq-session-timeout-titre">Session inactive</p>
    <p id="yqSessionTimeoutTexte" class="yq-session-timeout-texte">La session va se fermer dans <strong data-yq-compteur><?= (int) $sessionLifetime ?></strong>&nbsp;s.</p>
    <p class="yq-session-timeout-erreur" data-yq-erreur hidden>Impossible de joindre le serveur. Réessaie.</p>
    <div class="yq-session-timeout-actions">
        <button type="button" class="yq-session-timeout-rester" data-yq-rester>Rester connecté</button>
        <button type="button" data-yq-quitter>Se déconnecter</button>
    </div>
</div>
<script src="../../js/session-timeout.js"></script>
<?php endif; ?>
</body>
</html>
