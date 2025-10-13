<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit();
}

ini_set('display_errors',1);
error_reporting(E_ALL);


try {
    $db = new PDO('mysql:host=localhost;dbname=hey401;charset=utf8','root','');
    $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("Erreur DB: ".$e->getMessage());
}

// --- Création d'un nouveau topic si soumis ---
if(isset($_POST['title'], $_POST['description'])){
    $stmt = $db->prepare("INSERT INTO topics (title, description, user_id, created_at) VALUES (:title, :desc, :user, NOW())");
    $stmt->execute([
        'title' => $_POST['title'],
        'desc'  => $_POST['description'],
        'user'  => $_SESSION['user_id']
    ]);
}

// Récupérer tous les topics avec popularité
$stmt = $db->query("
    SELECT t.*, COUNT(m.id) AS popularity 
    FROM topics t 
    LEFT JOIN messages m ON t.id = m.topic_id 
    GROUP BY t.id 
    ORDER BY popularity DESC, t.created_at DESC
");
$topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul max popularité pour les étoiles
$pop_values = array_column($topics, 'popularity');
$max_pop = max($pop_values) ?: 1;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hey401 - Forum</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://twemoji.maxcdn.com/v/latest/twemoji.min.js"></script>

<style>
* { box-sizing:border-box; margin:0; padding:0;}
body {font-family:'Poppins',sans-serif; background:#f0f2f5; height:100vh; display:flex; overflow:hidden;}

/* --- SIDEBAR --- */
.sidebar {
    background:#111827;
    width:70px;
    transition:width 0.3s;
    overflow:hidden;
    color:#fff;
    display:flex;
    flex-direction:column;
    align-items:center;
}
.sidebar:hover { width:240px; box-shadow:0 0 15px rgba(0,0,0,0.3);}
.sidebar .logo { text-align:center; padding:20px 0; font-size:22px; font-weight:bold; color:#3b82f6; white-space:nowrap; transition:opacity 0.3s; }
.sidebar.collapsed .logo { opacity:0; }
.sidebar ul { list-style:none; width:100%; margin-top:10px;}
.sidebar ul li { padding:12px 20px; cursor:pointer; display:flex; align-items:center; gap:15px; transition:background 0.2s;}
.sidebar ul li:hover { background: rgba(59,130,246,0.15);}
.sidebar ul li i { font-size:20px; color:#60a5fa;}
.sidebar ul li span { white-space:nowrap; opacity:0; transition:opacity 0.2s;}
.sidebar:hover ul li span { opacity:1; }

/* --- MAIN --- */
.main { flex:1; display:flex; flex-direction:column;}
.header { background:#fff; height:55px; display:flex; justify-content:space-between; align-items:center; padding:0 20px; box-shadow:0 2px 6px rgba(0,0,0,0.05);}
.header h1 { font-size:18px; color:#111;}
.header .actions { display:flex; align-items:center; gap:15px;}
.header .actions i { font-size:22px; color:#3b82f6; cursor:pointer; transition:transform 0.2s;}
.header .actions i:hover { transform:scale(1.2); }

/* --- TOPICS --- */
.topics { flex:1; overflow-y:auto; padding:25px; display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:20px;}
.topic-card { background:#fff; border-radius:14px; padding:18px; box-shadow:0 2px 10px rgba(0,0,0,0.05); cursor:pointer; transition:transform 0.2s, box-shadow 0.2s;}
.topic-card:hover { transform:translateY(-4px); box-shadow:0 6px 18px rgba(0,0,0,0.1);}
.topic-card h3 { font-size:16px; margin-bottom:8px; color:#111;}
.topic-card p { font-size:13px; color:#555; line-height:1.4em; }
.stars { margin-top:8px;}
.stars i { color:gold; margin-right:2px; }

/* --- NEW TOPIC FORM --- */
#newTopicForm {
    display:none;
    position:fixed;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);
    background:#fff;
    padding:25px;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,0.3);
    z-index:100;
    width:300px;
}
#newTopicForm input, #newTopicForm textarea { width:100%; margin-bottom:10px; padding:10px; border-radius:10px; border:1px solid #ccc; resize:none; }
#newTopicForm button { width:100%; padding:10px; background:#3b82f6; color:#fff; border:none; border-radius:10px; cursor:pointer;}
#overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.3); z-index:90; }

/* --- BUTTON + --- */
.new-topic-btn { position:fixed; bottom:25px; right:25px; background:#3b82f6; border:none; color:white; font-size:28px; border-radius:50%; width:55px; height:55px; cursor:pointer; box-shadow:0 5px 15px rgba(59,130,246,0.4); transition:background 0.3s, transform 0.3s;}
.new-topic-btn:hover { background:#2563eb; transform:scale(1.1); }

/* --- SCROLL --- */
::-webkit-scrollbar { width:6px;}
::-webkit-scrollbar-thumb { background:#a1a1aa; border-radius:10px;}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    
    <ul>
        <li><i class="fa-solid fa-envelope"></i><span></span></li>
        <li><i class="fa-solid fa-bell"></i><span></span></li>
        <li><i class="fa-solid fa-user"></i><span></span></li>
        <li><i class="fa-solid fa-gear"></i><span></span></li>
    </ul>
</div>

<!-- MAIN -->
<div class="main">
    <div class="header">
        <h1>Discussions récentes</h1>
        <div class="actions">
            <i class="fa-solid fa-envelope"></i>
            <i class="fa-solid fa-bell"></i>
            <i class="fa-solid fa-user"></i>
        </div>
    </div>

    <div class="topics">
        <?php foreach($topics as $t): 
            $stars = round(($t['popularity']/$max_pop)*5);
            $stars = max(1,min($stars,5));
        ?>
        <div class="topic-card" onclick="window.location.href='topic.php?id=<?= $t['id'] ?>'">
            <h3><?= htmlspecialchars($t['title']) ?></h3>
            <p><?= htmlspecialchars(substr($t['description'],0,80)) ?>...</p>
            <div class="stars">
                <?php for($i=0;$i<$stars;$i++): ?>
                    <i class="fa-solid fa-star"></i>
                <?php endfor; ?>
                <?php for($i=$stars;$i<5;$i++): ?>
                    <i class="fa-regular fa-star"></i>
                <?php endfor; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- BUTTON + -->
<button class="new-topic-btn" id="btnNewTopic" title="Créer un nouveau topic">+</button>

<!-- NEW TOPIC FORM -->
<div id="overlay"></div>
<div id="newTopicForm">
    <h3>Nouveau Topic</h3>
    <form method="POST">
        <input type="text" name="title" placeholder="Titre" required>
        <textarea name="description" placeholder="Description" rows="4" required></textarea>
        <button type="submit">Créer</button>
    </form>
</div>

<script>
const btn = document.getElementById('btnNewTopic');
const form = document.getElementById('newTopicForm');
const overlay = document.getElementById('overlay');
const sidebar = document.getElementById('sidebar');

btn.addEventListener('click', ()=>{
    form.style.display='block';
    overlay.style.display='block';
});
overlay.addEventListener('click', ()=>{
    form.style.display='none';
    overlay.style.display='none';
});

// Texte logo disparaît si sidebar est petit
sidebar.addEventListener('transitionend', ()=>{
    if(sidebar.offsetWidth < 100){
        sidebar.classList.add('collapsed');
    } else {
        sidebar.classList.remove('collapsed');
    }
});
</script>

</body>
</html>
