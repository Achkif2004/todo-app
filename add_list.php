<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/../classes/List.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = $_POST['title'];
        $list = new TodoList($title, $_SESSION['user_id']);
        $list->save($conn);
        $_SESSION['message'] = "Lijst toegevoegd!";
        header("Location: dashboard.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: dashboard.php");
        exit;
    }
}