<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$db = new PDO('mysql:host=localhost;dbname=hey401;charset=utf8', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_POST['email'], $_POST['pseudo'], $_POST['pass'], $_POST['passConfirm'])) {
    $email = htmlspecialchars($_POST['email']);
    $pseudo = htmlspecialchars($_POST['pseudo']);
    $pass = $_POST['pass'];
    $passConfirm = $_POST['passConfirm'];

    if ($pass !== $passConfirm) {
        $error = "Les mots de passe ne correspondent pas !";
    } else {
        // Vérifier si email ou pseudo existe
        $stmt = $db->prepare("SELECT * FROM mpampiasa WHERE email=:email OR pseudo=:pseudo");
        $stmt->execute(['email'=>$email, 'pseudo'=>$pseudo]);
        if ($stmt->rowCount() > 0) {
            $error = "Email ou pseudo déjà utilisé !";
        } else {
            $passHash = password_hash($pass, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO mpampiasa (email, pseudo, pass) VALUES (:email, :pseudo, :pass)");
            if ($stmt->execute(['email'=>$email, 'pseudo'=>$pseudo, 'pass'=>$passHash])) {
                $_SESSION['user'] = $pseudo; // connexion automatique après inscription
                header('Location: configure.php'); // page après inscription
                exit();
            } else {
                $error = "Erreur lors de l'inscription.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In</title>
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
<form method="POST" onsubmit="return verifyPassword()">
    <h2>Créer un compte</h2>

    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="pseudo" placeholder="@user1234" required>

    <!-- Mot de passe -->
    <input type="password" id="pass" name="pass" placeholder="Mot de passe" required>
    <input type="password" id="passConfirm" name="passConfirm" placeholder="Vérifier mot de passe" required>

    <button>Inscription</button>
</form>

<style>
input {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 10px;
    border: 1px solid #ccc;
    transition: 0.3s;
}
input.valid {
    border-color: green;
}
input.invalid {
    border-color: red;
}
</style>

<script>
const pass = document.getElementById('pass');
const passConfirm = document.getElementById('passConfirm');

function verifyPassword() {
    if(pass.value !== passConfirm.value) {
        alert("Les mots de passe ne correspondent pas !");
        return false;
    }
    return true;
}

// Vérification en temps réel
passConfirm.addEventListener('input', () => {
    if(passConfirm.value === '') {
        passConfirm.classList.remove('valid','invalid');
        pass.classList.remove('valid','invalid');
        return;
    }
    if(pass.value === passConfirm.value){
        pass.classList.add('valid');
        pass.classList.remove('invalid');
        passConfirm.classList.add('valid');
        passConfirm.classList.remove('invalid');
    } else {
        pass.classList.add('invalid');
        pass.classList.remove('valid');
        passConfirm.classList.add('invalid');
        passConfirm.classList.remove('valid');
    }
});
</script>


</body>
</html>
