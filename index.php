<?php
session_start();
require('dbconnect.php');

// ページング
$page = 1;
if (isset($_GET['page'])) {
    $page = $_GET["page"];
}
$page = max($page, 1);
$counts = $db->query('SELECT COUNT(*) AS cnt FROM posts');
$cnt = $counts->fetch();
$maxPage = ceil($cnt['cnt'] / 5);
if ($maxPage == 0) {
    $maxPage = 1;
}
$page = min($page, $maxPage);
$start = ($page - 1) * 5;

// ログイン中かどうかの確認
if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
    $_SESSION['time'] = time();

    $members = $db->prepare('SELECT * FROM members WHERE id=?');
    $members->execute(array($_SESSION['id']));
    $member = $members->fetch();
} else {
    header('Location: login.php');
    exit();
}



if (!empty($_POST)) {
    if ($_POST['message'] != "") {
        $message = $db->prepare('INSERT INTO posts SET member_id=?, message=?, reply_post_id=?, created=NOW()');
        $message->execute(array($member['id'], $_POST['message'], $_POST['reply_post_id']));
        header('Location: index.php');
        exit();
    }
}

$posts = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC LIMIT ?, 5');
$posts->bindParam(1, $start, PDO::PARAM_INT);
$posts->execute();

if (isset($_GET['res'])) {
    $response = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=? ORDER BY p.created DESC');
    $response->execute(array($_GET['res']));

    $table = $response->fetch();
    $hostmessage = '@' . $table['name'] . ' ' . $table['message'];
}

$message = "";

if (isset($hostmessage)) {
    $message = $hostmessage;
}

function h($value)
{
    return htmlspecialchars($value, ENT_QUOTES);
}

function makelink($value)
{
    return mb_ereg_replace("(https?)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)", '<a href="\1\2">\1\2</a>', $value);
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>投稿・一覧ページ</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="message">
        <div style="text-align: right"><a href="logout.php">ログアウト</a></div>
        <form action="" method="post">
            <dl>
                <dt><?php echo h($member['name']); ?>さん、メッセージをどうぞ</dt>
                <dd>
                    <textarea class="message-text" name="message" cols="50" rows="5" ><?php  echo h($message) ?></textarea>
                    <?php if (isset($_GET["res"])) :?>
                        <input type="hidden" name="reply_post_id" value="<?php echo h($_GET["res"])?>">
                    <?php endif?>
                </dd>
            </dl>
            <div>
                <input type="submit" value="投稿する">
            </div>
        </form>
        <?php foreach ($posts as $post) :?>
            <div class="msg">
                <img src="member_picture/<?php echo h($post["picture"]) ?>" alt="<?php echo h($post["name"]) ?>" width="48" height="48" >

                <p><?php echo makeLink(h($post["message"])) ?><span class="name">（<?php echo h($post["name"]) ?>）</span>[<a href="index.php?res=<?php echo h($post['id']) ?>">Re</a>]</p>
                <p class="day">
                    <a href="view.php?id=<?php echo h($post['id'])?>"><?php echo h($post["created"]) ?></a>
                    <?php if ($post['reply_post_id'] > 0) :?>
                        <a href="view.php?id=<?php echo h($post['reply_post_id'])?>">返信元のメッセージ</a>
                    <?php endif ?>
                    <?php if ($_SESSION['id'] == $post['member_id']) :?>
                        [<a href="delete.php?id=<?php echo h($post['id'])?>" style="color: #F33;">削除</a>]
                    <?php endif ?>
                </p>
            </div>
        <?php endforeach?>

        <ul>
            <?php if ($page > 1) :?>
                <a href="index.php?page=<?php echo ($page - 1)?>">前ページへ</a>
            <?php else :?>
                <li>前ページへ</li>
            <?php endif ?>
            <?php if ($page < $maxPage) :?>
                <a href="index.php?page=<?php echo ($page + 1)?>">次ページへ</a>
            <?php else :?>
                <li>次ページへ</li>
            <?php endif ?>
        </ul>
    </div>
</body>
</html>