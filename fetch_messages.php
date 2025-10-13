<?php
session_start();
if(!isset($_SESSION['user_id'])) exit();

header('Content-Type: application/json');

try {
    $db = new PDO('mysql:host=localhost;dbname=hey401;charset=utf8','root','');
    $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    echo json_encode([]);
    exit();
}

if(!isset($_GET['topic_id'])) {
    echo json_encode([]);
    exit();
}

$topic_id = intval($_GET['topic_id']);

$stmt = $db->prepare("SELECT m.*, u.pseudo, u.id as uid, u.pdp FROM messages m 
                      JOIN mpampiasa u ON m.user_id=u.id 
                      WHERE topic_id=? 
                      ORDER BY created_at ASC");
$stmt->execute([$topic_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($messages as &$m){
    $m['pdp'] = $m['pdp'] ? $m['pdp'] : 'uploads/default.png';
}

echo json_encode($messages);
?>
