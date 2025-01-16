<?php 
session_start();
if (empty($_SESSION['login_user_id'])) {
  header("HTTP/1.1 302 Found");
  header("Location: /login.php");
  return;
}

// DB接続
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');
// セッションにあるログインIDから、ログインしている対象の会員情報を引く
$select_sth = $dbh->prepare("SELECT * FROM users WHERE id = :id");
$select_sth->execute([
    ':id' => $_SESSION['login_user_id'],
]);
$user = $select_sth->fetch();
?>

<a href="/bbs.php">掲示板に戻る</a>
<h1>設定画面</h1>
<p>
現在の設定
</p>
<dl> <!-- 登録情報を出力する際はXSS防止のため htmlspecialchars() を必ず使いましょう -->
<dt>ID</dt>
<dd><?= htmlspecialchars($user['id']) ?></dd>
<dt>メールアドレス</dt>
<dd><?= htmlspecialchars($user['email']) ?></dd>
<dt>名前</dt>
<dd><?= htmlspecialchars($user['name']) ?></dd>
<dt>年齢</dt>
<dd>
<?php if(!empty($user['birthday'])): ?>
<?php
  $birthday = DateTime::createFromFormat('Y-m-d', $user['birthday']);
    $today = new DateTime('now');
    ?>
      <?= $today->diff($birthday)->y ?>歳
<?php endif; ?>
</dd>
</dl>
<ul>
<li><a href="./icon.php">アイコン設定</a></li>
<li><a href="./selfIntroduction.php">自己紹介文設定</a></li>
<li><a href="./cover.php">カバー画像設定</a></li>
<li><a href="./introduction_age.php">生年月日設定</a></li>
</ul>
