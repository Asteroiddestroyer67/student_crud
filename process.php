<?php
session_start();
include 'db_connect.php';
include 'functions.php';

// CREATE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create'])) {
    $student_id = clean_input($_POST['student_id']);
    $name = clean_input($_POST['name']);
    $program = clean_input($_POST['program']);

    if (empty($student_id) || empty($name) || empty($program)) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'All fields are required!'];
        header("Location: index.php");
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO students (student_id, name, program) VALUES (:sid, :name, :prog)");
        $stmt->bindParam(':sid', $student_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':prog', $program);
        $stmt->execute();
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Student record added successfully!'];
    } catch(PDOException $e) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error: Student ID may already exist.'];
    }
    header("Location: index.php");
    exit;
}

// UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = (int)$_POST['record_id'];
    $student_id = clean_input($_POST['student_id']);
    $name = clean_input($_POST['name']);
    $program = clean_input($_POST['program']);

    if (empty($student_id) || empty($name) || empty($program)) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'All fields required!'];
        header("Location: index.php");
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE students SET student_id=:sid, name=:name, program=:prog WHERE id=:id");
        $stmt->bindParam(':sid', $student_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':prog', $program);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Record updated!'];
    } catch(PDOException $e) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Update failed.'];
    }
    header("Location: index.php");
    exit;
}

// SOFT DELETE
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $conn->prepare("UPDATE students SET is_deleted=1 WHERE id=:id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Record archived!'];
    } catch(PDOException $e) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Delete failed.'];
    }
    header("Location: index.php");
    exit;
}

// RESTORE
if (isset($_GET['action']) && $_GET['action'] == 'restore' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $conn->prepare("UPDATE students SET is_deleted=0 WHERE id=:id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Record restored!'];
    } catch(PDOException $e) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Restore failed.'];
    }
    header("Location: index.php");
    exit;
}
?>