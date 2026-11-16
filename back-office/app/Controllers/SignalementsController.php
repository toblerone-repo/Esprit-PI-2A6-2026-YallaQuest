<?php
require_once __DIR__ . '/../Model/Signalement.php';

class SignalementsController
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
        $model = new Signalement();
        $module = 'signalements';
        $page = $this->readValue($_GET, 'action', 'liste');
        $title = 'Signalements';
        $activePage = 'signalements';
        $pageError = '';
        $flash = null;

        switch ($page) {
            case 'voir':
                $id = $this->readValue($_GET, 'id', '0');
                $report = $model->find($id);
                if (!$report) {
                    http_response_code(404);
                    $title = 'Contenu introuvable';
                    $pageError = 'Le signalement demandé n’existe pas.';
                    break;
                }
                $title = 'Signalement #' . $id;
                $note = $report['moderator_note'];
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $action = $this->readValue($_POST, 'action');
                    $note = $this->readValue($_POST, 'note');
                    if ($model->process($id, $action, $note)) {
                        header('Location: index.php?module=signalements&action=voir&id=' . $id . '&message=' . $action);
                        exit;
                    }
                    $flash = ['type' => 'error', 'message' => 'Le signalement n’a pas pu être traité.'];
                }
                switch ($this->readValue($_GET, 'message')) {
                    case 'review':
                        $flash = ['type' => 'success', 'message' => 'Le signalement est maintenant en cours de traitement.'];
                        break;
                    case 'keep':
                        $flash = ['type' => 'success', 'message' => 'Le signalement a été classé sans suite. La publication est conservée.'];
                        break;
                    case 'hide':
                        $flash = ['type' => 'success', 'message' => 'La publication a été masquée et le signalement clôturé.'];
                        break;
                }
                $publication = false;
                if ($report['publication_id']) {
                    $publicationModel = new Publication();
                    $publication = $publicationModel->find($report['publication_id']);
                }
                $isOpen = $report['status'] == 'pending' || $report['status'] == 'reviewing';
                break;

            default:
                $page = 'liste';
                $allReports = $model->getSignalements();
                $selectedStatus = $this->readValue($_GET, 'statut', 'open');
                $reportFilters = ['open' => 'À examiner', 'closed' => 'Historique', 'all' => 'Tous'];
                foreach ($reportStatuses as $key => $label) {
                    $reportFilters[$key] = $label;
                }
                $search = $this->readValue($_GET, 'recherche');
                $publicationId = $this->readValue($_GET, 'publication', '0');
                $counts = ['pending' => 0, 'reviewing' => 0, 'resolved' => 0, 'dismissed' => 0];
                $reports = [];
                foreach ($allReports as $report) {
                    if (isset($counts[$report['status']])) {
                        $counts[$report['status']]++;
                    }
                    $matchesStatus = $selectedStatus == 'all' || $report['status'] == $selectedStatus;
                    if ($selectedStatus == 'open') {
                        $matchesStatus = $report['status'] == 'pending' || $report['status'] == 'reviewing';
                    } elseif ($selectedStatus == 'closed') {
                        $matchesStatus = $report['status'] == 'resolved' || $report['status'] == 'dismissed';
                    }
                    $text = $report['publication_title'] . ' ' . $report['publication_author'] . ' ' . $report['details'];
                    $matchesSearch = $search == '' || mb_stripos($text, $search, 0, 'UTF-8') !== false;
                    $matchesPublication = $publicationId == '' || $publicationId == '0' || $report['publication_id'] == $publicationId;
                    if ($matchesStatus && $matchesSearch && $matchesPublication) {
                        $reports[] = $report;
                    }
                }
        }
        require __DIR__ . '/../View/layout.php';
    }
}
