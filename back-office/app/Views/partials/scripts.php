<?php
// Scripts de fin de page du Back-Office, dans l'ordre MVC : modèle, contrôleur, puis la vue ($pageScript, dans js/).
$s = htmlspecialchars($shared ?? '../../shared/');
$o = htmlspecialchars($office ?? '../');
?>
<script src="<?= $s ?>js/sidebar.js"></script>
<script src="<?= $o ?>Model/GuildeModel.js"></script>
<script src="<?= $o ?>Controller/GuildeController.js"></script>
<?php if (!empty($pageScript)): ?>
<script src="<?= $o ?>js/<?= htmlspecialchars($pageScript) ?>"></script>
<?php endif; ?>
