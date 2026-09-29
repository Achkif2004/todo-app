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
        $title = trim((string) $title);
        if ($title === '') {
            throw new Exception("Titel mag niet leeg zijn.");
        }
        if (preg_match_all('/./us', $title) > 100) {
            throw new Exception("Titel mag maximaal 100 tekens zijn.");
        }
        // Ruwe tekst bewaren; escapen gebeurt bij het tonen
        $this->title = $title;
    }

    public function save($conn) {
        $stmt = $conn->prepare("INSERT INTO lists (title, user_id) VALUES (?, ?)");
        $stmt->execute([$this->title, $this->user_id]);
        $this->id = (int) $conn->lastInsertId();
    }
}
