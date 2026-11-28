<?php
// Amine : guildes admin. Houssem : protection CSRF/XSS. Guildes : données lues dans la base.
require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();
$cspNonce = $cspNonce ?? bin2hex(random_bytes(16));
$currentPage = 'guildes';
if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)
    && !AuthController::verifyCsrfToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    exit('Jeton de sécurité invalide. Rechargez la page.');
}
AuthController::requireRole('admin');

require_once __DIR__ . '/../../../Controllers/guildeC.php';

/* Yalla Quest · Back-Office : administration des guildes.
   Les guildes sont créées et les quêtes proposées depuis le front-office (guildes.php) ;
   cette page les liste. Même gabarit que les autres pages du back-office : Views/css/guilde.css, aucun effet. */
$pageTitle = 'Guildes · Administration · Yalla Quest';
$e = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$guildeC = new GuildeC();
$guildes = $guildeC->listGuildes();
$propositions = $guildeC->listPropositions();

$nbMembres = array_sum(array_map(static fn ($g) => (int) $g['nb_membres'], $guildes));
$nbEnVote = count(array_filter($propositions, static fn ($p) => $p['statut'] === 'en_vote'));
$nbAdoptees = count(array_filter($propositions, static fn ($p) => $p['statut'] === 'adoptee'));
$places = count($guildes) * GuildeC::MEMBRES_MAX;
$statuts = [
    'en_vote' => ['En vote', 'pill lilac'],
    'adoptee' => ['Adoptée', 'pill gold'],
    'rejetee' => ['Rejetée', 'pill'],
];
?>
<?php
require_once __DIR__ . '/../partials/head.php';
?>
<div class="app">
<main class="canvas" id="canvas" tabindex="-1">
    <div class="title-row">
        <div>
            <p class="kicker">Administration · guildes</p>
            <h1>Guildes</h1>
            <p class="about">Une seule guilde par membre, <?= GuildeC::MEMBRES_MAX ?> membres au plus.</p>
            <p class="sub"><strong><?= count($guildes) ?> guilde<?= count($guildes) > 1 ? 's' : '' ?>, <?= $nbMembres ?> membre<?= $nbMembres > 1 ? 's' : '' ?>.</strong> <?= $nbEnVote ?> proposition<?= $nbEnVote > 1 ? 's' : '' ?> de quêtes <?= $nbEnVote > 1 ? 'sont' : 'est' ?> en vote.</p>
        </div>
    </div>

    <section class="kpis" aria-label="Chiffres clés">
        <div class="kpi"><p class="kpi-label">Guildes</p><p class="kpi-value"><?= count($guildes) ?></p></div>
        <div class="kpi"><p class="kpi-label">Membres</p><p class="kpi-value"><?= $nbMembres ?> <small>/ <?= $places ?> places</small></p></div>
        <div class="kpi"><p class="kpi-label">Propositions en vote</p><p class="kpi-value"><?= $nbEnVote ?></p></div>
        <div class="kpi"><p class="kpi-label">Quêtes adoptées</p><p class="kpi-value"><?= $nbAdoptees ?></p></div>
    </section>

    <h2 class="section">Toutes les guildes</h2>
    <div class="sheet"><div class="table-wrap">
        <table class="respond admin-table">
            <thead><tr>
                <th>Guilde</th><th>Fondée par</th><th>Création</th><th class="n">Membres</th><th class="n">En vote</th><th class="n">Propositions</th>
            </tr></thead>
            <tbody>
            <?php if (empty($guildes)): ?>
                <tr><td colspan="6" class="second">Aucune guilde pour l’instant. Les membres les créent depuis la page Guildes du site.</td></tr>
            <?php endif; ?>
            <?php foreach ($guildes as $g): $id = (int) $g['id_guilde']; ?>
                <tr>
                    <td class="id"><span class="gcell"><img src="../../img/competences/<?= $e(GuildeC::image(null, $id)) ?>" alt=""><span><strong><?= $e($g['nom']) ?></strong><?php if ((string) $g['description'] !== ''): ?><span class="desc"><?= $e($g['description']) ?></span><?php endif; ?></span></span></td>
                    <td class="hide-sm"><?= $e($g['createur']) ?></td>
                    <td class="hide-sm"><?= $e(GuildeC::dateFr($g['date_creation'])) ?></td>
                    <td class="n hide-sm"><?= (int) $g['nb_membres'] ?> / <?= GuildeC::MEMBRES_MAX ?></td>
                    <td class="n second"><span class="pill<?= (int) $g['nb_en_vote'] > 0 ? ' lilac' : '' ?>"><?= (int) $g['nb_en_vote'] ?> en vote</span></td>
                    <td class="n hide-sm"><?= (int) $g['nb_propositions'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div></div>

    <h2 class="section">Propositions de quêtes</h2>
    <div class="sheet"><div class="table-wrap">
        <table class="respond admin-table">
            <thead><tr>
                <th>Quête proposée</th><th>Guilde</th><th>Proposée le</th><th>Fin du vote</th><th class="n">Pour</th><th class="n">Contre</th><th>Statut</th>
            </tr></thead>
            <tbody>
            <?php if (empty($propositions)): ?>
                <tr><td colspan="7" class="second">Aucune quête proposée pour l’instant.</td></tr>
            <?php endif; ?>
            <?php foreach ($propositions as $p): [$libelle, $classe] = $statuts[$p['statut']]; ?>
                <tr>
                    <td class="id"><strong><?= $e($p['titre']) ?></strong><span class="desc"><?= $e($p['competence'] ?? '') ?> · <?= $e($p['difficulte']) ?> · <?= (int) $p['xp'] ?> XP</span></td>
                    <td class="second"><?= $e($p['guilde']) ?></td>
                    <td class="hide-sm"><?= $e(GuildeC::dateFr($p['date_proposition'])) ?></td>
                    <td class="hide-sm"><?= $e(GuildeC::dateFr($p['date_limite_vote'])) ?></td>
                    <td class="n hide-sm"><?= (int) $p['pour'] ?></td>
                    <td class="n hide-sm"><?= (int) $p['contre'] ?></td>
                    <td class="end"><span class="<?= $classe ?>"><?= $libelle ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div></div>
</main>
</div>
<?php include_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
