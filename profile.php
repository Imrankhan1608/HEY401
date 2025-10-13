<?php
session_start();
if(!isset($_GET['id'])) exit();

$db = new PDO('mysql:host=localhost;dbname=hey401;charset=utf8','root','');
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

$user_id = intval($_GET['id']);
$stmt = $db->prepare("SELECT * FROM mpampiasa WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$user){ echo "Utilisateur introuvable"; exit(); }

$pdp = !empty($user['pdp']) && file_exists('uploads/'.$user['pdp']) ? 'uploads/'.$user['pdp'] : 'uploads/default.png';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($user['pseudo']) ?></title>
<style>
body{font-family:Arial,sans-serif;background:#f0f2f5;margin:0;padding:0;}
.profile{max-width:500px;margin:50px auto;background:#fff;padding:20px;border-radius:15px;box-shadow:0 2px 5px rgba(0,0,0,0.1);text-align:center;}
.profile img{width:120px;height:120px;border-radius:50%;object-fit:cover;margin-bottom:15px;}
.profile h2{margin:10px 0;}
.profile p{color:#555;}
</style>
</head>
<body>

<div class="profile">
<img src="<?= $pdp ?>" alt="Profile">
<h2><?= htmlspecialchars($user['pseudo']) ?></h2>
<p><?= !empty($user['bio']) ? nl2br(htmlspecialchars($user['bio'])) : "Cette personne n'a pas encore de bio." ?></p>
</div>

</body>
</html>
