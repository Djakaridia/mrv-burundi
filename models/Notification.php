<?php
class Notification
{
    private $conn;
    private $table = 't_notifications';

    public $id;
    public $titre;
    public $message;
    public $type;
    public $entity_type;
    public $entity_id;
    public $user_id;
    public $add_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " (titre, message, type, entity_type, entity_id, user_id, add_by) VALUES 
                 (:titre, :message, :type, :entity_type, :entity_id, :user_id, :add_by)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':titre', $this->titre);
        $stmt->bindParam(':message', $this->message);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':entity_type', $this->entity_type);
        $stmt->bindParam(':entity_id', $this->entity_id);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':add_by', $this->add_by);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read()
    {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readById()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id=:id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readByUser()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id=:user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readByEntity()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id=:user_id AND entity_type=:entity_type ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':entity_type', $this->entity_type);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update()
    {
        $query = "UPDATE " . $this->table . " SET titre=:titre, message=:message, type=:type, 
                 entity_type=:entity_type, entity_id=:entity_id, user_id=:user_id, add_by=:add_by WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':titre', $this->titre);
        $stmt->bindParam(':message', $this->message);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':entity_type', $this->entity_type);
        $stmt->bindParam(':entity_id', $this->entity_id);
        $stmt->bindParam(':add_by', $this->add_by);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function markAsRead()
    {
        $query = "UPDATE " . $this->table . " SET is_read=TRUE WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function markAsStarred($status)
    {
        $query = "UPDATE " . $this->table . " SET is_starred=:status WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    public function markAsArchived($status)
    {
        $query = "UPDATE " . $this->table . " SET is_archived=:status WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    public function markAsMultiple($action, $user_id, $entity_type, $ids)
    {
        if (is_string($ids)) $ids = array_map('intval', array_filter(explode(',', $ids), 'is_numeric'));

        if (empty($ids) || !is_array($ids)) return false;

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            switch ($action) {
                case 'read':
                    $query = "UPDATE " . $this->table . " SET is_read = TRUE WHERE user_id = ? AND entity_type = ? AND id IN ($placeholders)";
                    break;

                case 'unread':
                    $query = "UPDATE " . $this->table . " SET is_read = FALSE WHERE user_id = ? AND entity_type = ? AND id IN ($placeholders)";
                    break;

                case 'star':
                    $query = "UPDATE " . $this->table . " SET is_starred = TRUE WHERE user_id = ? AND entity_type = ? AND id IN ($placeholders)";
                    break;

                case 'unstar':
                    $query = "UPDATE " . $this->table . " SET is_starred = FALSE WHERE user_id = ? AND entity_type = ? AND id IN ($placeholders)";
                    break;

                case 'archive':
                    $query = "UPDATE " . $this->table . " SET is_archived = TRUE WHERE user_id = ? AND entity_type = ? AND id IN ($placeholders)";
                    break;

                case 'unarchive':
                    $query = "UPDATE " . $this->table . " SET is_archived = FALSE WHERE user_id = ? AND entity_type = ? AND id IN ($placeholders)";
                    break;

                case 'delete':
                    $query = "DELETE FROM " . $this->table . " WHERE user_id = ? AND entity_type = ? AND id IN ($placeholders)";
                    break;

                default:
                    error_log("Action non reconnue: $action");
                    return false;
            }

            $stmt = $this->conn->prepare($query);
            $params = array_merge([$user_id, $entity_type], $ids);
            $result = $stmt->execute($params);

            if ($result) {
                $rowCount = $stmt->rowCount();
                error_log("Action en masse '$action' réussie : $rowCount notifications affectées");
                return $rowCount;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Erreur action en masse: " . $e->getMessage());
            return false;
        }
    }

    public function getUserCounts($user_id)
    {
        try {
            $query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN is_read = FALSE AND is_archived = FALSE THEN 1 ELSE 0 END) as unread,
                        SUM(CASE WHEN is_starred = TRUE AND is_archived = FALSE THEN 1 ELSE 0 END) as starred,
                        SUM(CASE WHEN is_archived = TRUE THEN 1 ELSE 0 END) as archived,
                        SUM(CASE WHEN entity_type = 'project' AND is_read = FALSE THEN 1 ELSE 0 END) as project_unread,
                        SUM(CASE WHEN entity_type = 'group' AND is_read = FALSE THEN 1 ELSE 0 END) as group_unread
                      FROM " . $this->table . " 
                      WHERE user_id = :user_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur compteurs: " . $e->getMessage());
            return false;
        }
    }

    public function delete()
    {
        $query = "DELETE FROM " . $this->table . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
}
