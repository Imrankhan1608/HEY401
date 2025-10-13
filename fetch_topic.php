<?php
session_start();
$db = new PDO('mysql:host=localhost;dbname=hey401;charset=utf8','root','');
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

if(isset($_GET['id'])){
    $topicId = $_GET['id'];

    $stmt = $db->prepare("SELECT t.*, u.pseudo FROM topics t JOIN mpampiasa u ON t.user_id=u.id WHERE t.id=?");
    $stmt->execute([$topicId]);
    $t = $stmt->fetch(PDO::FETCH_ASSOC);

    if($t){
        echo "<div class='topic-card'>";
        echo "<div class='topic-title'>".htmlspecialchars($t['title'])." - <em>".htmlspecialchars($t['pseudo'])."</em></div>";
        echo "<div class='discussion' style='display:block;'>";
        echo "<p>".nl2br(htmlspecialchars($t['description']))."</p>";

        // messages
        $stmt2=$db->prepare("SELECT m.*, u.pseudo FROM messages m JOIN mpampiasa u ON m.user_id=u.id WHERE topic_id=? ORDER BY created_at ASC");
        $stmt2->execute([$topicId]);
        $messages=$stmt2->fetchAll(PDO::FETCH_ASSOC);
        echo "<div class='messages'>";
        foreach($messages as $m){
            echo "<div class='message-item'><strong>".htmlspecialchars($m['pseudo'])."</strong>: ".nl2br(htmlspecialchars($m['content']))."</div>";
        }
        echo "</div>";

        // formulaire réponse
        echo "<form class='replyForm'>
                <input type='hidden' name='topic_id' value='{$t['id']}'>
                <textarea name='message' placeholder='Répondre...' required></textarea>
                <button class='submitMsg' type='submit'>Envoyer</button>
              </form>";

        echo "</div></div>";
    }
}
?>
