<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

$db = new PDO('mysql:host=localhost;dbname=hey401;charset=utf8','root','');
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

if(isset($_POST['pseudo'], $_POST['pass'])){
    $pseudo = htmlspecialchars($_POST['pseudo']);
    $pass = $_POST['pass'];

    $stmt = $db->prepare("SELECT * FROM mpampiasa WHERE pseudo=:pseudo");
    $stmt->execute(['pseudo'=>$pseudo]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($pass,$user['pass'])){
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['pseudo'] = $user['pseudo'];
        header('Location: dash.php');
        exit();
    }else{
        $error = "Pseudo ou mot de passe incorrect !";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion</title>
<style>
body{font-family:sans-serif;background:#f0f2f5;display:flex;justify-content:center;align-items:center;height:100vh;}
form{background:white;padding:30px;border-radius:15px;box-shadow:0 4px 10px rgba(0,0,0,0.2);width:300px;}
input{width:100%;padding:10px;margin:10px 0;border-radius:10px;border:1px solid #ccc;}
button{width:100%;padding:10px;background:royalblue;color:white;font-weight:bold;border:none;border-radius:10px;cursor:pointer;}
button:hover{background:blue;}
.error{color:red;text-align:center;}
h2{text-align:center;margin-bottom:20px;}
</style>
</head>
<body>
<form method="POST">
<h2>Connexion</h2>
<?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
<input type="text" name="pseudo" placeholder="@user1234" required>
<input type="password" name="pass" placeholder="Mot de passe" required>
<button>Se connecter</button>
<p style="text-align:center;margin-top:10px;"><a href="signin.php">Créer un compte</a></p>
</form>
</body>
</html>
