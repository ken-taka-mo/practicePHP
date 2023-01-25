<?php
require('dbconnect.php');

session_start();


if (isset($_COOKIE['email']) && $_COOKIE['email'] != '') {
    $_POST['email'] = $_COOKIE['email'];
    $_POST['password'] = $_COOKIE['password'];
    $_POST['save'] = 'on';
}

if (!empty($_POST)) {
    if ($_POST['email'] != '' && $_POST['password'] != '') {
        $login = $db->prepare('SELECT * FROM members WHERE email=? AND password=?');
        $login->execute(array($_POST['email'], sha1($_POST['password'])));
        $member = $login->fetch();

        if ($member) {
            $_SESSION['id'] = $member['id'];
            $_SESSION['time'] = time();
            if ($_POST['save'] == 'on') {
                setcookie('email', $_POST['email'], time() + 24 * 60 * 60 * 14);
                setcookie('password', $_POST['password'], time() + 24 * 60 * 60 * 14);
            }
            header('Location: index.php');
            exit();
        } else {
            $error['login'] = 'failed';
        }
    } else {
        $error['login'] = 'blank';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>ログインページ</title>
</head>
<body>
    <div class="login">
        <div id="lead">
            <p>メールアドレスとパスワードを記入してログインしてください。</p>
            <p>入会手続きがまだの方はこちらからどうぞ。</p>
            <p>&raquo;<a href="join/index.php">入会手続きをする</a></p>
        </div>
        <form action="" method="post" class="login-forms">
            <dl>
                <div class="form">
                    <dt class="text-title">メールアドレス</dt>
                    <dd>
                        <input class="text" type="text" name="email" size="35" maxlength="255" value="<?php echo htmlspecialchars(filter_input(INPUT_POST, 'email'), ENT_QUOTES)?>" />
                        <?php if (isset($error['login']) && $error['login'] == 'blank') :?>
                            <p class="error">※メールアドレスとパスワードをご記入ください</p>
                        <?php endif ?>
                        <?php if (isset($error['login']) && $error['login'] == 'failed') :?>
                            <p class="error">※ログインに失敗しました。正しくご記入ください。</p>
                        <?php endif ?>
                    </dd>
                </div>
                <div class="form">
                    <dt class="text-title">パスワード</dt>
                    <dd>
                        <input class="text" type="password" name="password" size="35" maxlength="255" value="<?php echo htmlspecialchars(filter_input(INPUT_POST, 'password'), ENT_QUOTES) ?>" />
                    </dd>
                </div>
                <div class="form">
                    <dt>ログイン情報の記録</dt>
                    <dd>
                        <input class="save" id="save" type="checkbox" name="save" value="on"><label for="save">次回からは自動的にログインする</label>
                    </dd>
                    <div class="btn-login"><input type="submit" value="ログインする" /></div>
                </div>
            </dl>
        </form>
    </div>
</body>
</html>