<?php
require_once __DIR__ . '/../Models/Publication.php';
require_once __DIR__ . '/../Models/Signalement.php';

class CommunauteController
{
    private function readValue($data, $name, $default = '')
    {
        if (isset($data[$name])) {
            return $data[$name];
        }
        return $default;
    }

    public function index()
    {
        require YQ_BACK . '/src/options.php';
        $pageTitle = 'Communauté — YallaQuest';
        $selectedCategory = $this->readValue($_GET, 'categorie', 'all');
        $search = $this->readValue($_GET, 'recherche');

        $publicationModel = new Publication();
        $filteredPublications = [];
        foreach ($publicationModel->getPublications(true) as $publication) {
            $text = $publication['title'] . ' ' . $publication['content'] . ' ' . $publication['author'];
            $matchesCategory = $selectedCategory == 'all' || $publication['category'] == $selectedCategory;
            $matchesSearch = $search == '' || mb_stripos($text, $search, 0, 'UTF-8') !== false;
            if ($matchesCategory && $matchesSearch) {
                $filteredPublications[] = $publication;
            }
        }
        $publicationCount = count($filteredPublications);
        $preview = null;
        $flash = null;
        $reportFormId = 0;
        $reportFormReason = '';
        $reportFormDetails = '';
        $formValues = ['title' => '', 'content' => '', 'category' => 'nutrition', 'type' => 'Petite victoire'];
        $action = $this->readValue($_POST, 'action');

        if ($action == 'preview') {
            foreach ($formValues as $key => $default) {
                $formValues[$key] = $this->readValue($_POST, $key, $default);
            }
            $preview = [
                'id' => 'preview', 'author' => 'Toi', 'initials' => 'TO',
                'category' => $formValues['category'], 'type' => $formValues['type'],
                'title' => $formValues['title'], 'content' => $formValues['content'],
                'more' => '', 'image' => null,
            ];
        } elseif ($action == 'report') {
            $reportFormId = $this->readValue($_POST, 'publication_id', '0');
            $reportFormReason = $this->readValue($_POST, 'reason');
            $reportFormDetails = $this->readValue($_POST, 'details');
            $reportModel = new Signalement();
            $result = $reportModel->create($reportFormId, $reportFormReason, $reportFormDetails);
            switch ($result) {
                case 'created':
                    $flash = ['type' => 'success', 'message' => 'Merci. Ton signalement a été transmis à la modération.'];
                    break;
                case 'invalid':
                    $flash = ['type' => 'error', 'message' => 'Choisis un motif dans la liste (2000 caractères au plus pour les précisions).'];
                    break;
                case 'unavailable':
                    $flash = ['type' => 'error', 'message' => 'Le signalement ne peut pas être envoyé pour le moment.'];
                    break;
                default:
                    $flash = ['type' => 'error', 'message' => 'Cette publication n’est plus disponible.'];
            }
        }

        $displayPublications = [];
        if ($preview) {
            $displayPublications[] = $preview;
        }
        foreach ($filteredPublications as $publication) {
            $displayPublications[] = $publication;
        }
        require __DIR__ . '/../includes/pages/communaute.php';
    }
}
