<?php
// Amine : compteur d'inactivité. Intégration : affiché sur toutes les pages du front-office,
// seulement pour un utilisateur connecté (rôle user). Inclus par includes/footer.php.
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user' || !class_exists('AuthController')) {
    return;
}
$yqDureeSession = defined('YQ_SESSION_DUREE') ? (int) YQ_SESSION_DUREE : 1800;
?>
<!-- Compteur d'inactivité : s'ouvre avant la fermeture de la session (Views/js/session-timeout.js).
     YQ_SESSION_DUREE vient de session.php (durée d'inactivité autorisée, en secondes). -->
<link rel="stylesheet" href="../../css/session-timeout.css">
<div id="yqSessionTimeout" class="yq-session-timeout" role="alertdialog" aria-labelledby="yqSessionTimeoutTitre" aria-describedby="yqSessionTimeoutTexte" hidden
     data-duree="<?= $yqDureeSession ?>"
     data-ping="../../../index.php?action=ping"
     data-deconnexion="../../../index.php?action=logout"
     data-expiree="../../../index.php?action=logout&amp;expire=1"
     data-csrf="<?= htmlspecialchars(AuthController::getCsrfToken(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
    <p id="yqSessionTimeoutTitre" class="yq-session-timeout-titre">Session inactive</p>
    <p id="yqSessionTimeoutTexte" class="yq-session-timeout-texte">La session va se fermer dans <strong data-yq-compteur><?= $yqDureeSession ?></strong>&nbsp;s.</p>
    <p class="yq-session-timeout-erreur" data-yq-erreur hidden>Impossible de joindre le serveur. Réessaie.</p>
    <div class="yq-session-timeout-actions">
        <button type="button" class="yq-session-timeout-rester" data-yq-rester>Rester connecté</button>
        <button type="button" data-yq-quitter>Se déconnecter</button>
    </div>
</div>
<script src="../../js/session-timeout.js"></script>
