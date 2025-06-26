<?php
class TodoList {
    private $id;
    private $user_id;
    private $title;

    public function __construct($title, $user_id) {
        $this->setTitle($title);
        $this->user_id = $user_id;
    }

    public function setTitle($title) {
        if (empty($title)) {
            throw new Exception("Titel mag niet leeg zijn.");
        }
        $this->title = htmlspecialchars($title);
    }

    public function save($conn) {
        $stmt = $conn->prepare("INSERT INTO lists (title, user_id) VALUES (?, ?)");
        $stmt->execute([$this->title, $this->user_id]);
    }
}
?>
