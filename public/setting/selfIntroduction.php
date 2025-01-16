<?php
session_start();
if (empty($_SESSION['login_user_id'])) {
  header("HTTP/1.1 302 Found");
  header("Location: /selfIntroduction.php");
  return;
}

$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

$select_sth = $dbh->prepare("SELECT * FROM users WHERE id = :id");
$select_sth->execute([
    ':id' => $_SESSION['login_user_id'],
]);
$user = $select_sth->fetch();

if (isset($_POST['self_introduction'])) {
$update_sth = $dbh->prepare("UPDATE users SET self_introduction = :self_introduction WHERE id = :id");
$update_sth->execute([
    ':id' => $user['id'],
    ':self_introduction' => $_POST['self_introduction'],
]);

header("HTTP/1.1 302 Found");
header("Location: ./selfIntroduction.php?success=1");
return;
}
?>

<a href="./index.php">設定一覧に戻る</a>

<h1>自己紹介追加</h1>
<form method="POST">
<textarea name="self_introduction">
<?= htmlspecialchars($user['self_introduction'] ?? '') ?>
</textarea>
<button>送信</button>
</form>

<?php if(!empty($_GET['success'])): ?>
<div>
  自己紹介文の設定処理が完了しました。
  </div>
  <?php endif; ?>
