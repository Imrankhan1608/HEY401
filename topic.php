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

if(!isset($_GET['id'])){
    header('Location: dash.php');
    exit();
}

$topic_id = intval($_GET['id']);
$stmt = $db->prepare("SELECT * FROM topics WHERE id=?");
$stmt->execute([$topic_id]);
$topic = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$topic){
    echo "Topic introuvable";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://twemoji.maxcdn.com/v/latest/twemoji.min.js"></script>

<title><?= htmlspecialchars($topic['title']) ?></title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f0f2f5;
    margin: 0;
    display: flex;
    flex-direction: column;
    height: 100vh;
}

/* --- HEADER --- */
#messages-header {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding: 6px 12px;
    height: 45px;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 10;
}
#backBtn {
    margin-right: 10px;
    color: royalblue;
    font-weight: bold;
    border: none;
    background: transparent;
    font-size: 22px;
    cursor: pointer;
}
img{
    width: 20px;
    height: 20px;
}
#messages-header h2 {
    font-size: 16px;
    margin: 0;
    color: #333;
}

/* --- MESSAGES --- */
#messages {
    flex: 1;
    padding: 10px 12px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.message-container {
    display: flex;
    align-items: flex-end;
    gap: 6px;
    max-width: 75%;
}

.message-container.self {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.message-container .avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    object-fit: cover;
}

.message-bubble {
    background: #fff;
    padding: 8px 12px;
    border-radius: 18px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    word-wrap: break-word;
    font-size: 14px;
    color: #333;
    max-width: 100%;
}

.message-container.self .message-bubble {
    background: royalblue;
    color: white;
}

.pseudo {
    font-size: 11px;
    color: #666;
    margin-bottom: 3px;
}

/* --- FORMULAIRE --- */
form {
    display: flex;
    align-items: center;
    padding-top: 10px;
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(6px);
    border-top: 1px solid rgba(0,0,0,0.05);
}

form textarea {
    flex: 1;
    border: none;
    border-radius: 20px;
    padding-top: 12px;
    padding-bottom: 8px;
    padding-left: 12px;
    padding-right: 12px;
    font-size: 14px;
    resize: none;
    outline: none;
    background: transparent;
    color: #333;
}

form button {
    margin-left: 8px;
    padding: 8px 16px;
    border: none;
    border-radius: 30px;
    background: #ffff;
    color: white;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.2s;
}

form button:hover {
    background: trasnparent;
}
</style>
</head>
<body>

<div id="messages-header">
    <button id="backBtn"><img src="assests/chevron-gauche.png" alt="" ></button>
    <h2><?= htmlspecialchars($topic['title']) ?></h2>
</div>

<div id="messages"></div>

<form id="msgForm">
    <textarea name="message" placeholder="Écrire un message..." required></textarea>
    <button><img src="assests/envoyer-le-message.png" alt=""></button>
</form>

<script>
const topicId = <?= $topic_id ?>;
const userId = <?= $_SESSION['user_id'] ?>;
const messagesDiv = document.getElementById('messages');

document.getElementById('backBtn').addEventListener('click', () => {
    window.location.href = 'dash.php';
});

let autoScroll = true;
messagesDiv.addEventListener('scroll', () => {
    const threshold = 20;
    autoScroll = messagesDiv.scrollTop + messagesDiv.clientHeight >= messagesDiv.scrollHeight - threshold;
});

function fetchMessages(){
    fetch('fetch_messages.php?topic_id=' + topicId)
    .then(res => res.json())
    .then(data => {
        messagesDiv.innerHTML = '';
        data.forEach(m => {
            const container = document.createElement('div');
            container.className = 'message-container ' + (m.uid == userId ? 'self' : 'other');

            const avatar = document.createElement('img');
            avatar.src = m.pdp ? m.pdp : 'uploads/default.png';
            avatar.className = 'avatar';

            const messageContent = document.createElement('div');
            const pseudo = document.createElement('div');
            pseudo.className = 'pseudo';
            pseudo.textContent = m.pseudo;

            const bubble = document.createElement('div');
            bubble.className = 'message-bubble';
            bubble.textContent = m.content;

            if(m.uid == userId) pseudo.style.textAlign = 'right';

            messageContent.appendChild(pseudo);
            messageContent.appendChild(bubble);

            container.appendChild(avatar);
            container.appendChild(messageContent);
            messagesDiv.appendChild(container);
        });

        if(autoScroll) {
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
    });
}

document.getElementById('msgForm').addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('topic_id', topicId);
    fetch('post_message.php', {
        method: 'POST',
        body: formData
    }).then(() => {
        this.reset();
        fetchMessages();
    });
});

setInterval(fetchMessages, 1000);
fetchMessages();
</script>

</body>
</html>
