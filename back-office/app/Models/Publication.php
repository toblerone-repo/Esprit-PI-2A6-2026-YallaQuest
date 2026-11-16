<?php
require_once __DIR__ . '/../../config.php';

class Publication
{
    private $db;

    public function __construct()
    {
        $this->db = Config::getConnexion();
    }

    public function getPublications($publishedOnly = false)
    {
        if ($this->db == null) {
            return [];
        }
        $sql = 'SELECT * FROM publications';
        if ($publishedOnly) {
            $sql .= " WHERE status = 'published'";
        }
        $sql .= ' ORDER BY id DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function find($id)
    {
        if ($this->db == null) {
            return false;
        }
        $query = $this->db->prepare('SELECT * FROM publications WHERE id = :id');
        $query->execute(['id' => $id]);
        return $query->fetch();
    }

    private function values($data)
    {
        return [
            'author' => $data['author'],
            'initials' => mb_strtoupper(mb_substr($data['author'], 0, 2, 'UTF-8'), 'UTF-8'),
            'category' => $data['category'],
            'type' => $data['type'],
            'title' => $data['title'],
            'content' => $data['content'],
            'more' => $data['more'],
            'image' => $data['image'],
            'status' => $data['status'],
        ];
    }

    public function create($data)
    {
        if ($this->db == null) {
            return false;
        }
        $query = $this->db->prepare('INSERT INTO publications
            (author, initials, category, type, title, content, more, image, status, created_at, updated_at)
            VALUES (:author, :initials, :category, :type, :title, :content, :more, :image, :status,
                CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
        return $query->execute($this->values($data));
    }

    public function update($id, $data)
    {
        if ($this->db == null) {
            return false;
        }
        $values = $this->values($data);
        $values['id'] = $id;
        $query = $this->db->prepare('UPDATE publications SET author = :author,
            initials = :initials, category = :category, type = :type, title = :title,
            content = :content, more = :more, image = :image, status = :status,
            updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        return $query->execute($values);
    }

    public function delete($id)
    {
        if (!$this->find($id)) {
            return false;
        }
        // Clôturer les signalements ouverts, puis conserver leur copie du récit.
        $query = $this->db->prepare("UPDATE signalements SET status = 'resolved',
            decision = 'delete', moderator_note = 'La publication a été supprimée par la modération.',
            handled_at = CURRENT_TIMESTAMP WHERE publication_id = :id AND status IN ('pending', 'reviewing')");
        $query->execute(['id' => $id]);
        $query = $this->db->prepare('UPDATE signalements SET publication_id = NULL WHERE publication_id = :id');
        $query->execute(['id' => $id]);
        $query = $this->db->prepare('DELETE FROM publications WHERE id = :id');
        $query->execute(['id' => $id]);
        return $query->rowCount() > 0;
    }
}
