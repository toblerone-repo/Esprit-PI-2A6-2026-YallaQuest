<?php
// Guildes : création d'une guilde, proposition de quête, listes front-office et back-office.
/* =====================================================================
   Contrôleur des guildes. Toutes les requêtes SQL de la partie Guildes
   sont ici ; les vues (guildes.php, guilde_admin.php) ne font qu'afficher.

   $guildeC = new GuildeC();
   $guildeC->listGuildes()                      -> guildes avec fondateur, membres, propositions
   $guildeC->getIdGuildeDuMembre($idUser)       -> id de la guilde du membre, ou null
   $guildeC->getMembres($idGuilde)              -> membres d'une guilde
   $guildeC->listPropositions($idGuilde = null) -> propositions (toutes ou d'une guilde)
   $guildeC->verifierGuilde($post, $idUser)     -> tableau d'erreurs (vide = valide)
   $guildeC->creerGuilde($guilde)               -> true si la guilde est créée (créateur = 1er membre)
   $guildeC->verifierProposition($post, $idUser)-> tableau d'erreurs (vide = valide)
   $guildeC->proposerQuete($quete, $prop)       -> true si la quête et sa proposition sont créées
   $guildeC->verifierAdhesion($idG, $idUser)    -> tableau d'erreurs ; rejoindreGuilde($idG, $idUser) -> bool
   $guildeC->verifierVote($idP, $rep, $idUser)  -> tableau d'erreurs ; voter($idP, $idUser, $rep) -> bool
   $guildeC->listVotes()                        -> votes regroupés par proposition

   Règles : une seule guilde par membre ; une proposition se vote jusqu'à
   date_limite_vote incluse, puis elle est adoptée si « pour » > « contre ».
   ===================================================================== */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/guilde.php';
require_once __DIR__ . '/../Models/quete.php';
require_once __DIR__ . '/../Models/proposition.php';

class GuildeC
{
    public const DIFFICULTES = ['Facile', 'Moyenne', 'Difficile'];
    public const XP_MIN = 10;
    public const XP_MAX = 1000;
    public const VOTE_JOURS_MAX = 30;      // durée maximale d'un vote, en jours
    public const MEMBRES_MAX = 10;

    private const IMAGES = ['endurance.jpg', 'focus.jpg', 'force.jpg', 'mobilite.jpg', 'nutrition.jpg'];
    private const MOIS = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
    private const MOIS_LONGS = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    private const JOURS = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];

    private PDO $db;
    private ?string $aujourdhui = null;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    // Date du jour lue dans MySQL : la même horloge que CURDATE() dans les requêtes.
    public function aujourdhui(): string
    {
        if ($this->aujourdhui === null) {
            $this->aujourdhui = (string) $this->db->query('SELECT CURDATE()')->fetchColumn();
        }
        return $this->aujourdhui;
    }

    // ==============================================================
    // LECTURE
    // ==============================================================

    public function listGuildes(): array
    {
        $sql = "SELECT g.id_guilde, g.nom, g.description, g.date_creation, g.id_utilisateur_createur,
                       u.pseudo AS createur_pseudo, u.prenom AS createur_prenom, u.nom AS createur_nom,
                       (SELECT COUNT(*) FROM adherer a WHERE a.id_guilde = g.id_guilde) AS nb_membres,
                       (SELECT COUNT(*) FROM proposition p WHERE p.id_guilde = g.id_guilde AND p.date_limite_vote >= CURDATE()) AS nb_en_vote,
                       (SELECT COUNT(*) FROM proposition p WHERE p.id_guilde = g.id_guilde) AS nb_propositions
                FROM guilde g
                LEFT JOIN utilisateur u ON u.id_utilisateur = g.id_utilisateur_createur
                ORDER BY g.date_creation DESC, g.id_guilde DESC";
        $rows = $this->db->query($sql)->fetchAll();
        foreach ($rows as &$row) {
            $row['createur'] = self::nomAffiche($row['createur_pseudo'], $row['createur_prenom'], $row['createur_nom']);
        }
        return $rows;
    }

    public function getIdGuildeDuMembre(int $idUtilisateur): ?int
    {
        $stmt = $this->db->prepare(
            'SELECT g.id_guilde FROM guilde g
             WHERE g.id_guilde IN (SELECT a.id_guilde FROM adherer a WHERE a.id_utilisateur = :id1)
                OR g.id_guilde = (SELECT u.id_guilde FROM utilisateur u WHERE u.id_utilisateur = :id2)
             ORDER BY g.id_guilde LIMIT 1'
        );
        $stmt->execute(['id1' => $idUtilisateur, 'id2' => $idUtilisateur]);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }

    public function getMembres(int $idGuilde): array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id_utilisateur, u.pseudo, u.prenom, u.nom, a.date_adhesion
             FROM adherer a
             JOIN utilisateur u ON u.id_utilisateur = a.id_utilisateur
             JOIN guilde g ON g.id_guilde = a.id_guilde
             WHERE a.id_guilde = :g
             ORDER BY (a.id_utilisateur = g.id_utilisateur_createur) DESC, a.date_adhesion, u.id_utilisateur'
        );
        $stmt->execute(['g' => $idGuilde]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['nom_affiche'] = self::nomAffiche($row['pseudo'], $row['prenom'], $row['nom']);
        }
        return $rows;
    }

    public function listPropositions(?int $idGuilde = null): array
    {
        $sql = "SELECT p.id_proposition, p.date_proposition, p.date_limite_vote, p.id_guilde, g.nom AS guilde,
                       q.id_quete, q.titre, q.difficulte, q.xp,
                       co.nom AS competence, co.visuel, ca.nom AS categorie,
                       (SELECT COUNT(*) FROM voter v WHERE v.id_proposition = p.id_proposition AND v.reponse_vote = 'pour') AS pour,
                       (SELECT COUNT(*) FROM voter v WHERE v.id_proposition = p.id_proposition AND v.reponse_vote = 'contre') AS contre
                FROM proposition p
                JOIN guilde g ON g.id_guilde = p.id_guilde
                JOIN quete q ON q.id_quete = p.id_quete
                LEFT JOIN competence co ON co.id_competence = q.id_competence
                LEFT JOIN categorie ca ON ca.id_categorie = q.id_categorie";
        $params = [];
        if ($idGuilde !== null) {
            $sql .= ' WHERE p.id_guilde = :g';
            $params['g'] = $idGuilde;
        }
        $sql .= ' ORDER BY p.date_proposition DESC, p.id_proposition DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['pour'] = (int) $row['pour'];
            $row['contre'] = (int) $row['contre'];
            $row['statut'] = $this->statutProposition($row);
        }
        return $rows;
    }

    // en_vote jusqu'à la date limite incluse, puis adoptee (pour > contre) ou rejetee
    public function statutProposition(array $p): string
    {
        if ((string) $p['date_limite_vote'] >= $this->aujourdhui()) {
            return 'en_vote';
        }
        return ((int) $p['pour'] > (int) $p['contre']) ? 'adoptee' : 'rejetee';
    }

    public function listCompetences(): array
    {
        return $this->db->query('SELECT id_competence, nom FROM competence ORDER BY nom')->fetchAll();
    }

    public function listCategories(): array
    {
        return $this->db->query('SELECT id_categorie, nom FROM categorie ORDER BY nom')->fetchAll();
    }

    private function existeId(string $table, string $colonne, int $id): bool
    {
        // $table et $colonne viennent du code (jamais de l'utilisateur).
        $stmt = $this->db->prepare("SELECT 1 FROM $table WHERE $colonne = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function nomGuildeExiste(string $nom): bool
    {
        // Comparaison selon la collation utf8mb4_unicode_ci : sans tenir compte de la casse ni des accents.
        $stmt = $this->db->prepare('SELECT 1 FROM guilde WHERE nom = :nom LIMIT 1');
        $stmt->execute(['nom' => $nom]);
        return (bool) $stmt->fetchColumn();
    }

    // ==============================================================
    // CRÉER UNE GUILDE
    // ==============================================================

    public function verifierGuilde(array $data, int $idUtilisateur): array
    {
        $errors = [];
        $nom = self::espaces((string) ($data['nom'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));

        if ($this->getIdGuildeDuMembre($idUtilisateur) !== null) {
            $errors['general'] = 'Vous êtes déjà membre d’une guilde : un membre ne peut appartenir qu’à une seule guilde.';
        }
        if ($nom === '') {
            $errors['nom'] = 'Le nom de la guilde est obligatoire.';
        } elseif (mb_strlen($nom) < 3 || mb_strlen($nom) > 100) {
            $errors['nom'] = 'Le nom doit contenir de 3 à 100 caractères.';
        } elseif (!preg_match("/^[\p{L}\p{N}][\p{L}\p{N} '’\-]*$/u", $nom)) {
            $errors['nom'] = 'Le nom ne peut contenir que des lettres, des chiffres, des espaces, des apostrophes et des tirets.';
        } elseif ($this->nomGuildeExiste($nom)) {
            $errors['nom'] = 'Une guilde porte déjà ce nom.';
        }
        if (mb_strlen($description) > 300) {
            $errors['description'] = 'La description ne doit pas dépasser 300 caractères.';
        }
        return $errors;
    }

    public function creerGuilde(Guilde $guilde): bool
    {
        $idUser = $guilde->getIdCreateur();
        $date = $guilde->getDateCreation() !== '' ? $guilde->getDateCreation() : $this->aujourdhui();
        try {
            $this->db->beginTransaction();

            // Verrou sur le compte : deux envois simultanés ne créent pas deux guildes.
            $lock = $this->db->prepare('SELECT id_guilde FROM utilisateur WHERE id_utilisateur = :id FOR UPDATE');
            $lock->execute(['id' => $idUser]);
            $row = $lock->fetch();
            $dejaMembre = $this->db->prepare('SELECT 1 FROM adherer WHERE id_utilisateur = :id LIMIT 1');
            $dejaMembre->execute(['id' => $idUser]);
            if ($row === false || $row['id_guilde'] !== null || $dejaMembre->fetchColumn()) {
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare(
                'INSERT INTO guilde (nom, description, date_creation, id_utilisateur_createur)
                 VALUES (:nom, :description, :date_creation, :createur)'
            );
            $stmt->execute([
                'nom' => $guilde->getNom(),
                'description' => $guilde->getDescription() !== '' ? $guilde->getDescription() : null,
                'date_creation' => $date,
                'createur' => $idUser,
            ]);
            $idGuilde = (int) $this->db->lastInsertId();

            $this->db->prepare('INSERT INTO adherer (id_utilisateur, id_guilde, date_adhesion) VALUES (:u, :g, :d)')
                ->execute(['u' => $idUser, 'g' => $idGuilde, 'd' => $date]);
            $this->db->prepare('UPDATE utilisateur SET id_guilde = :g WHERE id_utilisateur = :u')
                ->execute(['g' => $idGuilde, 'u' => $idUser]);

            $this->db->commit();
            $guilde->setId($idGuilde);
            $guilde->setDateCreation($date);
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Création guilde : ' . $e->getMessage());
            return false;
        }
    }

    // ==============================================================
    // PROPOSER UNE QUÊTE (nouvelle quête + proposition à sa guilde)
    // ==============================================================

    public function verifierProposition(array $data, int $idUtilisateur): array
    {
        $errors = [];
        $titre = self::espaces((string) ($data['titre'] ?? ''));
        $difficulte = (string) ($data['difficulte'] ?? '');
        $xp = trim((string) ($data['xp'] ?? ''));
        $idCategorie = trim((string) ($data['id_categorie'] ?? ''));
        $idCompetence = trim((string) ($data['id_competence'] ?? ''));
        $dateLimite = trim((string) ($data['date_limite_vote'] ?? ''));

        $idGuilde = $this->getIdGuildeDuMembre($idUtilisateur);
        if ($idGuilde === null) {
            $errors['general'] = 'Rejoignez ou créez une guilde pour proposer une quête.';
        }

        if ($titre === '') {
            $errors['titre'] = 'Le titre de la quête est obligatoire.';
        } elseif (mb_strlen($titre) < 3 || mb_strlen($titre) > 150) {
            $errors['titre'] = 'Le titre doit contenir de 3 à 150 caractères.';
        } elseif ($idGuilde !== null && $this->titreEnVote($idGuilde, $titre)) {
            $errors['titre'] = 'Cette quête est déjà en vote dans votre guilde.';
        }

        if (!in_array($difficulte, self::DIFFICULTES, true)) {
            $errors['difficulte'] = 'Choisissez une difficulté.';
        }

        if (!preg_match('/^\d{1,4}$/', $xp) || (int) $xp < self::XP_MIN || (int) $xp > self::XP_MAX) {
            $errors['xp'] = 'Les XP doivent être un nombre entier entre ' . self::XP_MIN . ' et ' . self::XP_MAX . '.';
        }

        if (!ctype_digit($idCategorie) || !$this->existeId('categorie', 'id_categorie', (int) $idCategorie)) {
            $errors['id_categorie'] = 'Choisissez une catégorie.';
        }
        if (!ctype_digit($idCompetence) || !$this->existeId('competence', 'id_competence', (int) $idCompetence)) {
            $errors['id_competence'] = 'Choisissez une compétence.';
        }

        $d = DateTimeImmutable::createFromFormat('!Y-m-d', $dateLimite);
        if ($d === false || $d->format('Y-m-d') !== $dateLimite) {
            $errors['date_limite_vote'] = 'Indiquez une date de fin de vote valide.';
        } else {
            $today = new DateTimeImmutable($this->aujourdhui());
            $max = $today->modify('+' . self::VOTE_JOURS_MAX . ' days');
            if ($d <= $today) {
                $errors['date_limite_vote'] = 'Le vote doit se terminer au plus tôt demain.';
            } elseif ($d > $max) {
                $errors['date_limite_vote'] = 'Le vote ne peut pas durer plus de ' . self::VOTE_JOURS_MAX . ' jours.';
            }
        }
        return $errors;
    }

    private function titreEnVote(int $idGuilde, string $titre): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM proposition p JOIN quete q ON q.id_quete = p.id_quete
             WHERE p.id_guilde = :g AND q.titre = :t AND p.date_limite_vote >= CURDATE() LIMIT 1'
        );
        $stmt->execute(['g' => $idGuilde, 't' => $titre]);
        return (bool) $stmt->fetchColumn();
    }

    public function proposerQuete(Quete $quete, Proposition $proposition): bool
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare(
                'INSERT INTO quete (titre, difficulte, xp, id_categorie, id_competence)
                 VALUES (:titre, :difficulte, :xp, :cat, :comp)'
            );
            $stmt->execute([
                'titre' => $quete->getTitre(),
                'difficulte' => $quete->getDifficulte(),
                'xp' => $quete->getXp(),
                'cat' => $quete->getIdCat(),
                'comp' => $quete->getIdComp(),
            ]);
            $idQuete = (int) $this->db->lastInsertId();

            $dateProp = $proposition->getDateProposition() !== '' ? $proposition->getDateProposition() : $this->aujourdhui();
            $stmt = $this->db->prepare(
                'INSERT INTO proposition (date_proposition, date_limite_vote, id_guilde, id_quete)
                 VALUES (:dp, :dl, :g, :q)'
            );
            $stmt->execute([
                'dp' => $dateProp,
                'dl' => $proposition->getDateLimiteVote(),
                'g' => $proposition->getIdGuilde(),
                'q' => $idQuete,
            ]);
            $idProposition = (int) $this->db->lastInsertId();

            $this->db->commit();
            $quete->setId($idQuete);
            $proposition->setIdQuete($idQuete);
            $proposition->setDateProposition($dateProp);
            $proposition->setId($idProposition);
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Proposition de quête : ' . $e->getMessage());
            return false;
        }
    }

    // ==============================================================
    // REJOINDRE UNE GUILDE
    // ==============================================================

    private function nbMembres(int $idGuilde): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM adherer WHERE id_guilde = :g');
        $stmt->execute(['g' => $idGuilde]);
        return (int) $stmt->fetchColumn();
    }

    public function verifierAdhesion(int $idGuilde, int $idUtilisateur): array
    {
        if (!$this->existeId('guilde', 'id_guilde', $idGuilde)) {
            return ['general' => 'Cette guilde n’existe pas.'];
        }
        if ($this->getIdGuildeDuMembre($idUtilisateur) !== null) {
            return ['general' => 'Vous êtes déjà membre d’une guilde : un membre ne peut appartenir qu’à une seule guilde.'];
        }
        if ($this->nbMembres($idGuilde) >= self::MEMBRES_MAX) {
            return ['general' => 'Cette guilde est complète (' . self::MEMBRES_MAX . ' membres au plus).'];
        }
        return [];
    }

    public function rejoindreGuilde(int $idGuilde, int $idUtilisateur): bool
    {
        try {
            $this->db->beginTransaction();

            // Verrous : la guilde (places restantes) puis le compte (une seule guilde par membre).
            $lockG = $this->db->prepare('SELECT id_guilde FROM guilde WHERE id_guilde = :g FOR UPDATE');
            $lockG->execute(['g' => $idGuilde]);
            $lockU = $this->db->prepare('SELECT id_guilde FROM utilisateur WHERE id_utilisateur = :u FOR UPDATE');
            $lockU->execute(['u' => $idUtilisateur]);
            $user = $lockU->fetch();
            $dejaMembre = $this->db->prepare('SELECT 1 FROM adherer WHERE id_utilisateur = :u LIMIT 1');
            $dejaMembre->execute(['u' => $idUtilisateur]);

            if ($lockG->fetch() === false || $user === false || $user['id_guilde'] !== null
                || $dejaMembre->fetchColumn() || $this->nbMembres($idGuilde) >= self::MEMBRES_MAX) {
                $this->db->rollBack();
                return false;
            }

            $date = $this->aujourdhui();
            $this->db->prepare('INSERT INTO adherer (id_utilisateur, id_guilde, date_adhesion) VALUES (:u, :g, :d)')
                ->execute(['u' => $idUtilisateur, 'g' => $idGuilde, 'd' => $date]);
            $this->db->prepare('UPDATE utilisateur SET id_guilde = :g WHERE id_utilisateur = :u')
                ->execute(['g' => $idGuilde, 'u' => $idUtilisateur]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Adhésion guilde : ' . $e->getMessage());
            return false;
        }
    }

    // ==============================================================
    // VOTER SUR UNE PROPOSITION (pour / contre, modifiable jusqu'à la clôture)
    // ==============================================================

    public const REPONSES = ['pour', 'contre'];

    public function getProposition(int $idProposition): ?array
    {
        $stmt = $this->db->prepare('SELECT id_proposition, id_guilde, date_limite_vote FROM proposition WHERE id_proposition = :p');
        $stmt->execute(['p' => $idProposition]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function verifierVote(int $idProposition, string $reponse, int $idUtilisateur): array
    {
        $p = $this->getProposition($idProposition);
        if ($p === null) {
            return ['general' => 'Cette proposition n’existe pas.'];
        }
        if (!in_array($reponse, self::REPONSES, true)) {
            return ['general' => 'Choisissez « pour » ou « contre ».'];
        }
        if ($this->getIdGuildeDuMembre($idUtilisateur) !== (int) $p['id_guilde']) {
            return ['general' => 'Seuls les membres de la guilde peuvent voter sur cette proposition.'];
        }
        if ((string) $p['date_limite_vote'] < $this->aujourdhui()) {
            return ['general' => 'Le vote sur cette proposition est clos.'];
        }
        return [];
    }

    // Premier vote ou changement d'avis : une seule ligne par membre et par proposition.
    public function voter(int $idProposition, int $idUtilisateur, string $reponse): bool
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO voter (id_utilisateur, id_proposition, date_vote, reponse_vote)
                 VALUES (:u, :p, :d, :r)
                 ON DUPLICATE KEY UPDATE reponse_vote = :r2, date_vote = :d2'
            );
            $date = $this->aujourdhui();
            return $stmt->execute(['u' => $idUtilisateur, 'p' => $idProposition, 'd' => $date, 'r' => $reponse, 'r2' => $reponse, 'd2' => $date]);
        } catch (PDOException $e) {
            error_log('Vote proposition : ' . $e->getMessage());
            return false;
        }
    }

    // Votes par proposition : [id_proposition => [['id_utilisateur', 'nom_affiche', 'reponse_vote', 'date_vote'], ...]]
    public function listVotes(): array
    {
        $rows = $this->db->query(
            'SELECT v.id_proposition, v.id_utilisateur, v.reponse_vote, v.date_vote, u.pseudo, u.prenom, u.nom
             FROM voter v JOIN utilisateur u ON u.id_utilisateur = v.id_utilisateur
             ORDER BY v.date_vote, v.id_utilisateur'
        )->fetchAll();
        $votes = [];
        foreach ($rows as $row) {
            $row['nom_affiche'] = self::nomAffiche($row['pseudo'], $row['prenom'], $row['nom']);
            $votes[(int) $row['id_proposition']][] = $row;
        }
        return $votes;
    }

    // ==============================================================
    // AIDES D'AFFICHAGE (sans SQL)
    // ==============================================================

    public static function nomAffiche(?string $pseudo, ?string $prenom, ?string $nom): string
    {
        $pseudo = trim((string) $pseudo);
        if ($pseudo !== '') {
            return $pseudo;
        }
        $complet = trim(trim((string) $prenom) . ' ' . trim((string) $nom));
        return $complet !== '' ? $complet : 'membre';
    }

    public static function initiales(string $texte): string
    {
        $mots = preg_split('/[\s._\-]+/u', trim($texte), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $init = '';
        foreach (array_slice($mots, 0, 2) as $mot) {
            $init .= mb_strtoupper(mb_substr($mot, 0, 1));
        }
        if (mb_strlen($init) < 2) {
            $init = mb_strtoupper(mb_substr(preg_replace('/[\s._\-]+/u', '', $texte) ?? '', 0, 2));
        }
        return $init !== '' ? $init : '?';
    }

    // « 28 nov. 2026 »
    public static function dateFr(?string $ymd): string
    {
        $d = $ymd ? DateTimeImmutable::createFromFormat('!Y-m-d', substr($ymd, 0, 10)) : false;
        if ($d === false) {
            return '';
        }
        return (int) $d->format('j') . ' ' . self::MOIS[(int) $d->format('n') - 1] . ' ' . $d->format('Y');
    }

    // « samedi 28 novembre »
    public static function jourFr(string $ymd): string
    {
        $d = DateTimeImmutable::createFromFormat('!Y-m-d', substr($ymd, 0, 10));
        if ($d === false) {
            return '';
        }
        return self::JOURS[(int) $d->format('w')] . ' ' . (int) $d->format('j') . ' ' . self::MOIS_LONGS[(int) $d->format('n') - 1];
    }

    // Image d'une compétence (Views/img/competences/), ou une image choisie à partir d'un numéro.
    public static function image(?string $visuel, int $numero): string
    {
        $fichier = basename(str_replace('\\', '/', (string) $visuel));
        if ($fichier !== '' && in_array($fichier, self::IMAGES, true)) {
            return $fichier;
        }
        return self::IMAGES[abs($numero) % count(self::IMAGES)];
    }

    // Espaces de début/fin retirés, suites d'espaces réduites à une seule.
    public static function espaces(string $texte): string
    {
        return trim(preg_replace('/\s+/u', ' ', $texte) ?? '');
    }
}
