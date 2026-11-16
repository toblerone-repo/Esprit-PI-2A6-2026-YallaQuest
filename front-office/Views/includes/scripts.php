<?php
// Scripts de fin de page du Front-Office, dans l'ordre MVC : modèle, contrôleur, puis la vue ($pageScript, dans js/).
$s = htmlspecialchars($shared ?? '../../shared/');
$o = htmlspecialchars($office ?? '../');
?>
<script src="<?= $s ?>js/sidebar.js"></script>
<script src="<?= $o ?>Model/GuildeModel.js"></script>
<script src="<?= $o ?>Controller/GuildeController.js"></script>
<?php if (!empty($pageScript)): ?>
<script src="<?= $o ?>js/<?= htmlspecialchars($pageScript) ?>"></script>
<?php endif; ?>
<?php if (!empty($enableEffects)): ?>
<!-- Yalla Quest · effets : copies locales dans shared/lib/, CDN en secours. Aucun build. -->
<script src="<?= $s ?>lib/gsap/gsap.min.js"></script>
<script>window.gsap || document.write('<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"><\/script>')</script>
<script src="<?= $s ?>lib/gsap/ScrollTrigger.min.js"></script>
<script>window.ScrollTrigger || document.write('<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"><\/script>')</script>
<script src="<?= $s ?>lib/lenis/lenis.min.js"></script>
<script>window.Lenis || document.write('<script src="https://cdn.jsdelivr.net/npm/lenis@1.1.14/dist/lenis.min.js"><\/script>')</script>
<script src="<?= $s ?>lib/splittype/split-type.min.js"></script>
<script>window.SplitType || document.write('<script src="https://cdn.jsdelivr.net/npm/split-type@0.3.4/umd/index.min.js"><\/script>')</script>
<script type="importmap">
{ "imports": { "three": "<?= $s ?>lib/three/three.module.min.js" } }
</script>
<script>window.YQ_THREE_CDN = 'https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.min.js';</script>
<script type="module" src="<?= $o ?>js/effects/main.js"></script>
<?php endif; ?>
