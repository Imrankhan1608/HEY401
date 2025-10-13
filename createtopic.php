<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit();
}

try {
    $db = new PDO('mysql:host=localhost;dbname=hey401;charset=utf8','root','');
    $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("Erreur DB: ".$e->getMessage());
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);

    if($title && $desc){
        $stmt = $db->prepare("INSERT INTO topics (title, description) VALUES (?, ?)");
        $stmt->execute([$title, $desc]);
    }
}

header('Location: dash.php');
exit();
?>
