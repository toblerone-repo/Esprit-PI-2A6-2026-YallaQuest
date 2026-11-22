<?php
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

require_once __DIR__ . '/../../../Controllers/AuthController.php';
$csrfToken = AuthController::getCsrfToken();


/* Yalla Quest · Back-Office : administration des guildes (page statique, données d'exemple de la partie Guildes).
   Même gabarit que les autres pages du back-office : barre latérale et en-tête communs, Views/css/guilde.css, aucun effet. */
$currentPage = 'guildes';
$pageTitle = 'Guildes · Administration · Yalla Quest';
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
            <p class="about">Une seule guilde par membre, dix membres au plus.</p>
            <p class="sub"><strong>5 guildes, 15 membres.</strong> 6 propositions de quêtes sont en vote.</p>
        </div>
        <div class="actions">
            <button class="btn primary" type="button">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M8 3v10M3 8h10"/></svg>Créer une guilde
            </button>
        </div>
    </div>

    <section class="kpis" aria-label="Chiffres clés">
        <div class="kpi"><p class="kpi-label">Guildes</p><p class="kpi-value">5</p></div>
        <div class="kpi"><p class="kpi-label">Membres</p><p class="kpi-value">15 <small>/ 50 places</small></p></div>
        <div class="kpi"><p class="kpi-label">Propositions en vote</p><p class="kpi-value">6</p></div>
        <div class="kpi"><p class="kpi-label">Quêtes adoptées</p><p class="kpi-value">3</p></div>
    </section>

    <h2 class="section">Toutes les guildes</h2>
    <div class="sheet"><div class="table-wrap">
        <table class="respond admin-table">
            <thead><tr>
                <th>Guilde</th><th>Fondée par</th><th>Création</th><th class="n">Membres</th><th class="n">En vote</th><th class="n">Propositions</th><th class="n"><span class="sr">Actions</span></th>
            </tr></thead>
            <tbody>
                <tr>
                    <td class="id"><span class="gcell"><img src="../../img/competences/mobilite.jpg" alt=""><span><strong>Les Lanternes</strong><span class="desc">Marcher, s'étirer et cuisiner ensemble après les cours.</span></span></span></td>
                    <td class="hide-sm">yasmine.t</td>
                    <td class="hide-sm">10 févr. 2026</td>
                    <td class="n hide-sm">5 / 10</td>
                    <td class="n second"><span class="pill lilac">3 en vote</span></td>
                    <td class="n hide-sm">5</td>
                    <td class="end"><span class="row-actions"><button class="btn sm" type="button">Modifier</button><button class="btn sm danger" type="button">Supprimer</button></span></td>
                </tr>
                <tr>
                    <td class="id"><span class="gcell"><img src="../../img/competences/focus.jpg" alt=""><span><strong>Souffle profond</strong><span class="desc">Respiration et étirements, dix minutes par jour, sans pression.</span></span></span></td>
                    <td class="hide-sm">karim.bs</td>
                    <td class="hide-sm">20 oct. 2025</td>
                    <td class="n hide-sm">3 / 10</td>
                    <td class="n second"><span class="pill lilac">1 en vote</span></td>
                    <td class="n hide-sm">2</td>
                    <td class="end"><span class="row-actions"><button class="btn sm" type="button">Modifier</button><button class="btn sm danger" type="button">Supprimer</button></span></td>
                </tr>
                <tr>
                    <td class="id"><span class="gcell"><img src="../../img/competences/endurance.jpg" alt=""><span><strong>Médina en mouvement</strong><span class="desc">Explorer la ville à pied, une ruelle à la fois.</span></span></span></td>
                    <td class="hide-sm">leila.m</td>
                    <td class="hide-sm">2 avr. 2026</td>
                    <td class="n hide-sm">3 / 10</td>
                    <td class="n second"><span class="pill lilac">1 en vote</span></td>
                    <td class="n hide-sm">1</td>
                    <td class="end"><span class="row-actions"><button class="btn sm" type="button">Modifier</button><button class="btn sm danger" type="button">Supprimer</button></span></td>
                </tr>
                <tr>
                    <td class="id"><span class="gcell"><img src="../../img/competences/nutrition.jpg" alt=""><span><strong>Cuisine de saison</strong><span class="desc">Recettes tunisiennes locales, légères et de saison.</span></span></span></td>
                    <td class="hide-sm">omar.g</td>
                    <td class="hide-sm">14 juil. 2026</td>
                    <td class="n hide-sm">2 / 10</td>
                    <td class="n second"><span class="pill">0 en vote</span></td>
                    <td class="n hide-sm">1</td>
                    <td class="end"><span class="row-actions"><button class="btn sm" type="button">Modifier</button><button class="btn sm danger" type="button">Supprimer</button></span></td>
                </tr>
                <tr>
                    <td class="id"><span class="gcell"><img src="../../img/competences/force.jpg" alt=""><span><strong>Coureurs du matin</strong><span class="desc">Courir 5 km avant 8 h, trois fois par semaine.</span></span></span></td>
                    <td class="hide-sm">sami.j</td>
                    <td class="hide-sm">1 sept. 2026</td>
                    <td class="n hide-sm">2 / 10</td>
                    <td class="n second"><span class="pill lilac">1 en vote</span></td>
                    <td class="n hide-sm">1</td>
                    <td class="end"><span class="row-actions"><button class="btn sm" type="button">Modifier</button><button class="btn sm danger" type="button">Supprimer</button></span></td>
                </tr>
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
                <tr><td class="id"><strong>Préparer une salade méchouia</strong><span class="desc">Nutrition</span></td><td class="second">Les Lanternes</td><td class="hide-sm">12 nov. 2026</td><td class="hide-sm">16 nov. 2026</td><td class="n hide-sm">2</td><td class="n hide-sm">1</td><td class="end"><span class="pill lilac">En vote</span></td></tr>
                <tr><td class="id"><strong>Courir 5 km avant midi. Sans écouteurs.</strong><span class="desc">Endurance</span></td><td class="second">Les Lanternes</td><td class="hide-sm">14 nov. 2026</td><td class="hide-sm">19 nov. 2026</td><td class="n hide-sm">1</td><td class="n hide-sm">0</td><td class="end"><span class="pill lilac">En vote</span></td></tr>
                <tr><td class="id"><strong>30 jours de gainage</strong><span class="desc">Force</span></td><td class="second">Les Lanternes</td><td class="hide-sm">10 nov. 2026</td><td class="hide-sm">25 nov. 2026</td><td class="n hide-sm">2</td><td class="n hide-sm">2</td><td class="end"><span class="pill lilac">En vote</span></td></tr>
                <tr><td class="id"><strong>10 minutes de respiration chaque soir</strong><span class="desc">Focus</span></td><td class="second">Les Lanternes</td><td class="hide-sm">20 sept. 2026</td><td class="hide-sm">27 sept. 2026</td><td class="n hide-sm">3</td><td class="n hide-sm">1</td><td class="end"><span class="pill gold">Adoptée</span></td></tr>
                <tr><td class="id"><strong>Cuisiner un plat de saison</strong><span class="desc">Nutrition</span></td><td class="second">Les Lanternes</td><td class="hide-sm">25 sept. 2026</td><td class="hide-sm">1 oct. 2026</td><td class="n hide-sm">1</td><td class="n hide-sm">3</td><td class="end"><span class="pill">Rejetée</span></td></tr>
                <tr><td class="id"><strong>Séance d'étirements guidée</strong><span class="desc">Mobilité</span></td><td class="second">Souffle profond</td><td class="hide-sm">25 oct. 2026</td><td class="hide-sm">31 oct. 2026</td><td class="n hide-sm">3</td><td class="n hide-sm">0</td><td class="end"><span class="pill gold">Adoptée</span></td></tr>
                <tr><td class="id"><strong>Courir 5 km avant midi. Sans écouteurs.</strong><span class="desc">Endurance</span></td><td class="second">Souffle profond</td><td class="hide-sm">13 nov. 2026</td><td class="hide-sm">18 nov. 2026</td><td class="n hide-sm">1</td><td class="n hide-sm">1</td><td class="end"><span class="pill lilac">En vote</span></td></tr>
                <tr><td class="id"><strong>Marcher 30 minutes dans la médina</strong><span class="desc">Endurance</span></td><td class="second">Médina en mouvement</td><td class="hide-sm">11 nov. 2026</td><td class="hide-sm">17 nov. 2026</td><td class="n hide-sm">2</td><td class="n hide-sm">0</td><td class="end"><span class="pill lilac">En vote</span></td></tr>
                <tr><td class="id"><strong>Cuisiner un plat de saison</strong><span class="desc">Nutrition</span></td><td class="second">Cuisine de saison</td><td class="hide-sm">28 sept. 2026</td><td class="hide-sm">3 oct. 2026</td><td class="n hide-sm">2</td><td class="n hide-sm">0</td><td class="end"><span class="pill gold">Adoptée</span></td></tr>
                <tr><td class="id"><strong>Courir un semi-marathon en groupe</strong><span class="desc">Endurance</span></td><td class="second">Coureurs du matin</td><td class="hide-sm">9 nov. 2026</td><td class="hide-sm">20 nov. 2026</td><td class="n hide-sm">1</td><td class="n hide-sm">1</td><td class="end"><span class="pill lilac">En vote</span></td></tr>
            </tbody>
        </table>
    </div></div>
</main>
</div>
<?php include_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
