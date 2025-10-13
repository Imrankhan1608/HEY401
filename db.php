<?php
$host = "localhost";
$user = "root";
$pass = ""; // ton mot de passe MySQL (souvent vide sur XAMPP)
$dbname = "hey401"; // remplace par le nom réel de ta base de données

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}
?>
