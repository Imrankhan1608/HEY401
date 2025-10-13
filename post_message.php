<?php
session_start();
if(!isset($_SESSION['user_id'])) exit();

try {
    $db = new PDO('mysql:host=localhost;dbname=hey401;charset=utf8','root','');
    $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    exit();
}

if(!isset($_POST['topic_id'], $_POST['message'])) exit();

$topic_id = intval($_POST['topic_id']);
$content = htmlspecialchars($_POST['message']);

$stmt = $db->prepare("INSERT INTO messages (topic_id, user_id, content, created_at) VALUES (:topic, :user, :content, NOW())");
$stmt->execute([
    'topic'=>$topic_id,
    'user'=>$_SESSION['user_id'],
    'content'=>$content
]);

echo 'success';
