<?php
session_start();
require_once '../includes/db.php';
require_once '../classes/List.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = $_POST['title'];
        $list = new TodoList($title, $_SESSION['user_id']);
        $list->save($conn);
        header("Location: dashboard.php");
    } catch (Exception $e) {
        echo "Fout: " . $e->getMessage();
    }
}
?>
