<?php
require('../dbconnect.php');
session_start();


if (!empty($_POST)) {
    // inputのバリデーション
    if ($_POST["name"] == "") {
        $error["name"] = "blank";
    }
    if ($_POST["email"] == "") {
        $error["email"] = "blank";
    }
    if (strlen($_POST["password"]) < 4) {
        $error["password"] = "length";
    }
    if ($_POST["password"] == "") {
        $error["password"] = "blank";
    }

    // ファイル拡張子のバリデーション
    $fileName = $_FILES['image']['name'];
    if (!empty($fileName)) {
        $ext = substr($fileName, -3);
        if ($ext != 'jpg' && $ext != 'gif' && $ext != 'png') {
            $error['image'] = 'type';
        }
    }

    // 投稿処理
    if (empty($error)) {
        $member = $db->prepare('SELECT COUNT(*) AS cnt FROM members WHERE email=?');
        $member->execute(array($_POST['email']));
        $recode = $member->fetch();
        if ($recode['cnt'] > 0) {
            $error['email'] = 'duplicate';
        }
        $image = date('YmdHis') . $_FILES['image']['name'];
        var_dump($_FILES['image']['tmp_name']);
        move_uploaded_file($_FILES['image']['tmp_name'], '../member_picture/' . $image);
        $_SESSION["join"] = $_POST;
        $_SESSION["join"]['image'] = $image;
        header("Location: check.php");
        exit();
    }
}


// check.phpから書き直しでページ遷移してきたときの処理
if (isset($_GET["action"]) && $_GET["action"] == "rewrite") {
    $_POST = $_SESSION["join"];
    $error["rewrite"] = true;
}


// バリデーション時にクリアした値は書き直し不要にするための処理
$name = "";
$email = "";
$pass = "";
if (isset($_POST["name"])) {
    $name = $_POST["name"];
}
if (isset($_POST["email"])) {
    $email = $_POST["email"];
}
if (isset($_POST["password"])) {
    $pass = $_POST["password"];
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>入会手続きページ</title>
</head>
<body>
    <div class="signup">
        <p>次のフォームに必要事項を記入ください</p>
        <form action="" method="post" enctype="multipart/form-data">
            <dl>
                <dt>ニックネーム<span class="required">※必須</span></dt>
                <dd>
                    <input type="text" name="name" size="35" maxlength="255" value="<?php echo htmlspecialchars($name, ENT_QUOTES)?>" />
                    <?php if (isset($error["name"]) && $error["name"] == "blank") : ?>
                        <p class="error">※ニックネームを入力してください</p>
                    <?php endif ?> 
                </dd>
                <dt>メールアドレス<span class="required">※必須</span></dt>
                <dd>
                    <input type="text" name="email" size="35" maxlength="255" value="<?php echo htmlspecialchars($email, ENT_QUOTES)?>" />
                    <?php if (isset($error["email"]) && $error["email"] == "blank") : ?>
                        <p class="error">※メールアドレスを入力してください</p>
                    <?php endif ?>
                    <?php if (isset($error['email']) && $error['email'] == 'duplicate') :?>
                        <p class="error">※指定されたメールアドレスはすでに登録されています</p>
                    <?php endif ?>
                </dd>
                <dt>パスワード（４文字以上）<span class="required">※必須</span></dt>
                
                <dd>
                    <input type="password" name="password" size="10" maxlength="20" value="<?php echo htmlspecialchars($pass, ENT_QUOTES)?>" />
                    <?php if (isset($error["password"]) && $error["password"] == "length") : ?>
                        <p class="error">※4文字以上のパスワードを入力してください</p>
                    <?php endif ?> 
                    <?php if (isset($error["password"]) && $error["password"] == "blank") : ?>
                        <p class="error">※パスワードを入力してください</p>
                    <?php endif ?> 
                </dd>
                <dt>写真など</dt>
                <dd>
                    <input type="file" name="image" size="35">
                    <?php if (isset($error['image']) && $error['image'] == 'type') :?>
                        <p class="error">※写真などは「.gif」,「.jpg」,「.png」の画像を指定してください</p>
                    <?php endif ?>
                    <?php if (!empty($error)) :?>
                        <p class="error">※恐れ入りますが、画像を改めて指定してください</p>
                    <?php endif ?>
                </dd>
                <div><input type="submit" value="入力内容を確認する" class="submit"></div>
            </dl>
        </form>
    </div>
</body>
</html>