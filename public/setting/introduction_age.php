<?php
session_start();
if (empty($_SESSION['login_user_id'])) {
  header("HTTP/1.1 302 Found");
  header("Location: /login.php");
  return;
}

$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');
$select_sth = $dbh->prepare("SELECT * FROM users WHERE id = :id");
$select_sth->execute([
    ':id' => $_SESSION['login_user_id'],
]);
$user = $select_sth->fetch();

if(isset($_POST['birthday'])){

  //$birthday = $_POST['birthday'];
  //$now = date('Ymd');

  //$birthday = str_replace("-", "", $birthday);

  //$age = floor(($now - $birthday) / 10000);

  $update_sth = $dbh->prepare("UPDATE users SET birthday = :birthday WHERE id = :id");
  $update_sth->execute([
      ':id' => $user['id'],
      ':birthday' => $_POST['birthday'],
  ]);

  // 成功したら成功したことを示すクエリパラメータつきのURLにリダイレクト
  header("HTTP/1.1 302 Found");
  header("Location: ./introduction_age.php?success=1");
  return;
}
?>

<a href="./index.php">設定一覧に戻る</a>

<form method="POST">
<input name="birthday" type="date">
<button type="submit">送信</button>
</form>

<?php if(!empty($_GET['success'])): ?>
<div>
生年月日の変更処理が完了しました。
</div>
<?php endif; ?>

