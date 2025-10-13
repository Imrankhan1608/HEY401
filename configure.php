<?php
session_start();
if(!isset($_SESSION['user'])){
    header('Location: login.php');
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$db = new PDO('mysql:host=localhost;dbname=hey401;charset=utf8', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$userPseudo = $_SESSION['user'];

// Gérer upload PDP
if(isset($_FILES['pdp']) && $_FILES['pdp']['error'] == 0){
    $allowed = ['jpg','jpeg','png','gif'];
    $fileName = $_FILES['pdp']['name'];
    $fileTmp = $_FILES['pdp']['tmp_name'];
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if(in_array($ext, $allowed)){
        $newName = 'uploads/'.$userPseudo.'_profile.'.$ext;
        if(!is_dir('uploads')) mkdir('uploads', 0755);
        move_uploaded_file($fileTmp, $newName);

        $stmt = $db->prepare("UPDATE mpampiasa SET pdp=:pdp WHERE pseudo=:pseudo");
        $stmt->execute(['pdp'=>$newName, 'pseudo'=>$userPseudo]);
        $success = "Photo de profil mise à jour !";
    } else {
        $error = "Format non autorisé ! jpg, jpeg, png, gif seulement.";
    }
}

// Récupérer PDP actuel
$stmt = $db->prepare("SELECT pdp FROM mpampiasa WHERE pseudo=:pseudo");
$stmt->execute(['pseudo'=>$userPseudo]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js"></script>



<style>
body{font-family:sans-serif;background:#f0f2f5;display:flex;justify-content:center;align-items:center;flex-direction:column;min-height:100vh;}
form{background:white;padding:30px;border-radius:15px;box-shadow:0 4px 10px rgba(0,0,0,0.2);width:350px;margin-bottom:20px;}
input{width:100%;padding:10px;margin:10px 0;border-radius:10px;border:1px solid #ccc;}
button{width:100%;padding:10px;background:royalblue;color:white;font-weight:bold;border:none;border-radius:10px;cursor:pointer;}
button:hover{background:blue;}
.error{color:red;text-align:center;}
.success{color:green;text-align:center;}
img.pdp{width:120px;height:120px;border-radius:50%;margin-bottom:10px;object-fit:cover;}
</style>
</head>
<body>

<h2>Configurer votre profil</h2>

<?php 
if(isset($error)) echo "<p class='error'>$error</p>"; 
if(isset($success)) echo "<p class='success'>$success</p>"; 
?>

<!-- Afficher PDP actuel -->
<?php
if(!empty($user['pdp']) && file_exists($user['pdp'])){
    echo "<img src='".$user['pdp']."' class='pdp'>";
} else {
    echo "<img src='default.png' class='pdp'>";
}
?>

<!-- Formulaire PDP -->
<form method="POST" enctype="multipart/form-data">
    <label>Changer votre photo de profil</label>
    <input type="file" name="pdp" required>
    <button>Upload</button>
</form>
<script>
    let cropper;
const fileInput = document.getElementById('fileInput');
const preview = document.getElementById('preview');
const uploadBtn = document.getElementById('uploadBtn');

fileInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if(!file) return;

    const reader = new FileReader();
    reader.onload = () => {
        preview.src = reader.result;
        preview.style.display = 'block';
        if(cropper) cropper.destroy();
        cropper = new Cropper(preview, {
            aspectRatio: 1,
            viewMode: 1
        });
    };
    reader.readAsDataURL(file);
});

uploadBtn.addEventListener('click', () => {
    if(!cropper) return;
    cropper.getCroppedCanvas().toBlob((blob) => {
        const formData = new FormData();
        formData.append('pdp', blob, 'profile.png');

        fetch('configure.php', {
            method: 'POST',
            body: formData
        }).then(res => res.text()).then(console.log).catch(console.error);
    });
});

</script>

<a href="dash.php"><button style="margin-top:10px;">Aller au tableau de bord</button></a>
</body>
</html>
