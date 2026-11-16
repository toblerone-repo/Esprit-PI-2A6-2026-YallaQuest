<?php
require_once __DIR__ . '/../Model/Publication.php';
require_once __DIR__ . '/../Model/Signalement.php';

class PublicationsController
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
        require __DIR__ . '/../../shared/options.php';
        $model = new Publication();
        $reportModel = new Signalement();
        $module = 'publications';
        $page = $this->readValue($_GET, 'action', 'liste');
        $id = $this->readValue($_GET, 'id', '0');
        $title = 'Publications';
        $activePage = 'publications';
        $pageError = '';
        $flash = null;

        // Un code dans l'adresse permet d'afficher le résultat après une redirection.
        switch ($this->readValue($_GET, 'message')) {
            case 'cree':
                $flash = ['type' => 'success', 'message' => 'La publication a été créée.'];
                break;
            case 'modifie':
                $flash = ['type' => 'success', 'message' => 'La publication a été modifiée.'];
                break;
            case 'supprime':
                $flash = ['type' => 'success', 'message' => 'Publication supprimée. Les signalements sont conservés dans l’historique.'];
                break;
        }

        switch ($page) {
            case 'tableau-de-bord':
                $title = 'Tableau de bord';
                $activePage = 'dashboard';
                $publications = $model->getPublications();
                $reports = $reportModel->getSignalements();
                $publicationCounts = ['published' => 0, 'draft' => 0, 'hidden' => 0];
                $categoryCounts = ['nutrition' => 0, 'mouvement' => 0, 'serenite' => 0];
                foreach ($publications as $publication) {
                    if (isset($publicationCounts[$publication['status']])) {
                        $publicationCounts[$publication['status']]++;
                    }
                    if ($publication['status'] == 'published' && isset($categoryCounts[$publication['category']])) {
                        $categoryCounts[$publication['category']]++;
                    }
                }
                $openReports = [];
                foreach ($reports as $report) {
                    if ($report['status'] == 'pending' || $report['status'] == 'reviewing') {
                        $openReports[] = $report;
                    }
                }
                $recentPublications = array_slice($publications, 0, 5);
                $recentReports = array_slice($openReports, 0, 4);
                break;

            case 'voir':
                $publication = $model->find($id);
                if (!$publication) {
                    http_response_code(404);
                    $title = 'Contenu introuvable';
                    $pageError = 'La publication demandée n’existe pas.';
                    break;
                }
                $title = 'Détail de la publication';
                $relatedReports = [];
                foreach ($reportModel->getSignalements() as $report) {
                    if ($report['publication_id'] == $id) {
                        $relatedReports[] = $report;
                    }
                }
                break;

            case 'ajouter':
            case 'modifier':
                $isEdit = $page == 'modifier';
                $title = 'Nouvelle publication';
                $submitLabel = 'Créer la publication';
                $formAction = 'index.php?action=ajouter';
                $data = [
                    'author' => 'Équipe YallaQuest', 'title' => '', 'content' => '', 'more' => '',
                    'category' => 'nutrition', 'type' => 'Petite victoire', 'image' => '', 'status' => 'draft',
                ];
                if ($isEdit) {
                    $publication = $model->find($id);
                    if (!$publication) {
                        http_response_code(404);
                        $title = 'Contenu introuvable';
                        $pageError = 'La publication demandée n’existe pas.';
                        break;
                    }
                    $title = 'Modifier la publication';
                    $submitLabel = 'Enregistrer les modifications';
                    $formAction = 'index.php?action=modifier&id=' . $id;
                    foreach ($data as $key => $default) {
                        $data[$key] = $publication[$key];
                    }
                    if ($data['image'] == null) {
                        $data['image'] = '';
                    }
                }

                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    foreach ($data as $key => $default) {
                        $data[$key] = $this->readValue($_POST, $key);
                    }
                    if ($data['image'] == '') {
                        $data['image'] = null;
                    }
                    if ($isEdit) {
                        $saved = $model->update($id, $data);
                        $redirect = 'index.php?action=voir&id=' . $id . '&message=modifie';
                    } else {
                        $saved = $model->create($data);
                        $redirect = 'index.php?message=cree';
                    }
                    if ($saved) {
                        header('Location: ' . $redirect);
                        exit;
                    }
                    $flash = ['type' => 'error', 'message' => 'La publication n’a pas pu être enregistrée.'];
                }
                break;

            case 'supprimer':
                $publication = $model->find($id);
                if (!$publication) {
                    http_response_code(404);
                    $title = 'Contenu introuvable';
                    $pageError = 'La publication demandée n’existe pas.';
                    break;
                }
                $title = 'Supprimer la publication';
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    if ($model->delete($id)) {
                        header('Location: index.php?message=supprime');
                        exit;
                    }
                    $flash = ['type' => 'error', 'message' => 'La publication n’a pas pu être supprimée.'];
                }
                break;

            default:
                $page = 'liste';
                $allPublications = $model->getPublications();
                $search = $this->readValue($_GET, 'recherche');
                $selectedStatus = $this->readValue($_GET, 'statut', 'all');
                $selectedCategory = $this->readValue($_GET, 'categorie', 'all');
                $reportCounts = [];
                foreach ($reportModel->getSignalements() as $report) {
                    if ($report['publication_id']) {
                        $publicationId = (int) $report['publication_id'];
                        if (!isset($reportCounts[$publicationId])) {
                            $reportCounts[$publicationId] = 0;
                        }
                        $reportCounts[$publicationId]++;
                    }
                }
                $publications = [];
                $counts = ['published' => 0, 'draft' => 0, 'hidden' => 0];
                foreach ($allPublications as $publication) {
                    if (isset($counts[$publication['status']])) {
                        $counts[$publication['status']]++;
                    }
                    $publication['report_count'] = 0;
                    if (isset($reportCounts[$publication['id']])) {
                        $publication['report_count'] = $reportCounts[$publication['id']];
                    }
                    $text = $publication['title'] . ' ' . $publication['author'] . ' ' . $publication['content'];
                    $matchesSearch = $search == '' || mb_stripos($text, $search, 0, 'UTF-8') !== false;
                    $matchesStatus = $selectedStatus == 'all' || $publication['status'] == $selectedStatus;
                    $matchesCategory = $selectedCategory == 'all' || $publication['category'] == $selectedCategory;
                    if ($matchesSearch && $matchesStatus && $matchesCategory) {
                        $publications[] = $publication;
                    }
                }
        }
        require __DIR__ . '/../View/layout.php';
    }
}
