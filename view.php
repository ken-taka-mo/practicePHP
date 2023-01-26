<?php
session_start();
require('dbconnect.php');

if (empty($_GET["id"])) {
    header('Location: index.php');
    exit();
}

$posts = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=? ORDER BY p.created DESC');
$posts->execute(array($_GET["id"]));

function h($value)
{
    return htmlspecialchars($value, ENT_QUOTES);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ひとこと掲示板</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="wrap">
        <div id="head">
            <h1>ひとこと掲示板</h1>
        </div>
        <div id="content">
            <p>&laquo;<a href="index.php">一覧に戻る</a></p>
        </div>
        <?php if ($post = $posts->fetch()) :?>
            <div class="msg">
                <img src="member_picture/<?php echo h($post['picture'])?>" width="48" height="48" alt="<?php echo h($post['name'])?>">
                <p><?php echo h($post['message'])?><span><?php echo h($post['name'])?></span></p>
                <p class="day"><?php echo h($post['created'])?></p>
            </div>
        <?php else :?>
            <p>その投稿は削除されたか、URLが間違っています。</p>
        <?php endif ?>
    </div>
</body>
</html>