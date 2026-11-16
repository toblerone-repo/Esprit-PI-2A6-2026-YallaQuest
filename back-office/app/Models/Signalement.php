<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/Publication.php';

class Signalement
{
    private $db;

    public function __construct()
    {
        $this->db = Config::getConnexion();
    }

    public function getSignalements()
    {
        if ($this->db == null) {
            return [];
        }
        return $this->db->query('SELECT * FROM signalements ORDER BY id DESC')->fetchAll();
    }

    public function find($id)
    {
        if ($this->db == null) {
            return false;
        }
        $query = $this->db->prepare('SELECT * FROM signalements WHERE id = :id');
        $query->execute(['id' => $id]);
        return $query->fetch();
    }

    public function create($publicationId, $reason, $details)
    {
        if ($this->db == null) {
            return 'unavailable';
        }
        $publicationModel = new Publication();
        $publication = $publicationModel->find($publicationId);
        if (!$publication || $publication['status'] != 'published') {
            return 'missing';
        }
        $content = $publication['content'];
        if ($publication['more'] != '') {
            $content .= "\n\n" . $publication['more'];
        }
        $query = $this->db->prepare("INSERT INTO signalements
            (publication_id, publication_title, publication_author, publication_content,
                reason, details, status, moderator_note, created_at)
            VALUES (:id, :title, :author, :content, :reason, :details, 'pending', '', CURRENT_TIMESTAMP)");
        $query->execute([
            'id' => $publicationId,
            'title' => $publication['title'],
            'author' => $publication['author'],
            'content' => $content,
            'reason' => $reason,
            'details' => $details,
        ]);
        return 'created';
    }

    public function process($id, $action, $note)
    {
        if ($this->db == null) {
            return false;
        }
        $report = $this->find($id);
        if (!$report || ($report['status'] != 'pending' && $report['status'] != 'reviewing')) {
            return false;
        }
        switch ($action) {
            case 'review':
                $query = $this->db->prepare("UPDATE signalements SET status = 'reviewing',
                    moderator_note = :note WHERE id = :id");
                return $query->execute(['note' => $note, 'id' => $id]);
            case 'hide':
                $publicationModel = new Publication();
                if (!$publicationModel->find($report['publication_id'])) {
                    return false;
                }
                $query = $this->db->prepare("UPDATE publications SET status = 'hidden',
                    updated_at = CURRENT_TIMESTAMP WHERE id = :id");
                $query->execute(['id' => $report['publication_id']]);
                $status = 'resolved';
                break;
            case 'keep':
                $status = 'dismissed';
                break;
            default:
                return false;
        }
        $query = $this->db->prepare('UPDATE signalements SET status = :status,
            decision = :decision, moderator_note = :note, handled_at = CURRENT_TIMESTAMP WHERE id = :id');
        return $query->execute(['status' => $status, 'decision' => $action, 'note' => $note, 'id' => $id]);
    }
}
