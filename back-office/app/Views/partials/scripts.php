<?php
// Scripts de fin de page de l'administration des guildes, dans l'ordre MVC : modèle, contrôleur, puis la vue ($pageScript, dans src/js/).
?>
<script src="front-office/js/sidebar.js"></script>
<script src="back-office/app/Models/GuildeModel.js"></script>
<script src="back-office/app/Controllers/GuildeController.js"></script>
<?php if (!empty($pageScript)): ?>
<script src="back-office/src/js/<?= htmlspecialchars($pageScript) ?>"></script>
<?php endif; ?>
