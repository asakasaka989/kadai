<?php
$user = null;
if(!empty($_GET['user_id'])){
  $user_id = $_GET['user_id'];
  // DB接続
  $dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');
  // 対象の会員情報を引く
  $select_sth = $dbh->prepare("SELECT * FROM users WHERE id = :id");
  $select_sth->execute([
      ':id' => $user_id,
  ]);
  $user = $select_sth->fetch();

}
if(empty($user)){
  header("HTTP/1.1 404 Found");
  print('そのようなユーザーIDの会員情報は存在しません');
  return;
}
// この人の投稿データを取得
$select_sth = $dbh->prepare(
    'SELECT bbs_entries.*, users.name AS user_name, users.icon_filename AS user_icon_filename, users.cover_filename AS user_cover_filename'
    . ' FROM bbs_entries INNER JOIN users ON bbs_entries.user_id = users.id'
    . ' WHERE user_id = :user_id'
    . ' ORDER BY bbs_entries.created_at DESC'
    );
$select_sth->execute([
    ':user_id' => $user_id,
]);

if(empty($user['birthday'])){
  $birthday = null;
}else{
  $birthday = $user['birthday'];
  $now = date('Ymd');

  $birthday = str_replace("-", "", $birthday);

  $age = floor(($now - $birthday) / 10000);
}

// フォロー状態を取得
$relationship = null;
session_start();
if (!empty($_SESSION['login_user_id'])) { // ログインしている場合
                                          // フォロー状態をDBから取得
  $select_sth = $dbh->prepare(
      "SELECT * FROM user_relationships"
      . " WHERE follower_user_id = :follower_user_id AND followee_user_id = :followee_user_id"
      );
  $select_sth->execute([
      ':followee_user_id' => $user['id'], // フォローされる側は閲覧しようとしているプロフィールの会員
      ':follower_user_id' => $_SESSION['login_user_id'], // フォローする側はログインしている会員
  ]);
  $relationship = $select_sth->fetch();
}

$followe = null;
if (!empty($_SESSION['login_user_id'])) { // ログインしている場合
                                          // フォロー状態をDBから取得
  $select_sth = $dbh->prepare(
      "SELECT * FROM user_relationships INNER JOIN users ON user_relationships.followee_user_id = users.id"
      . " WHERE followee_user_id = :followee_user_id AND follower_user_id = :follower_user_id"
      );
  $select_sth->execute([
      ':follower_user_id' => $user['id'], // フォローされる側は閲覧しようとしているプロフィールの会員
      ':followee_user_id' => $_SESSION['login_user_id'], // フォローする側はログインしている会員
  ]);
  $followee = $select_sth->fetch();
}

?>

<a href="/bbs.php">掲示板に戻る</a><br>
<?php if(empty($user['cover_filename'])): ?>
現在未設定
<?php else: ?>
<div style="height: 15em; weight: 100%; background: url('/image/<?= $user['cover_filename'] ?>') center; backgrond-size: cover;)"> </div>
<?php endif; ?>

<h1><?= htmlspecialchars($user['name']) ?> さん のプロフィール</h1>
<div>
<?php if(empty($user['icon_filename'])): ?>
現在未設定
<?php else: ?>
<img src="/image/<?= $user['icon_filename'] ?>"
style="height: 5em; width: 5em; border-radius: 50%; object-fit: cover;">
<?php endif; ?>


<?php if(empty($relationship)): // フォローしていない場合 ?>
<div>
<a href="./follow.php?followee_user_id=<?= $user['id'] ?>">フォローする</a>
</div>
<?php else: // フォローしている場合 ?>
<div>
<?= $relationship['created_at'] ?> にフォローしました。
</div>
<?php endif; ?>


<?php if(empty($followee)): // フォローされていない場合 ?>
<div>　</div>
<?php else: // フォロ-されている場合 ?>
<div>
<?= $followee['name'] ?>さんに<?= $followee['created_at'] ?> フォローされました。
</div>

<?php endif; ?>



<p><a href="./follow_list.php">フォローしている一覧</a></p>
<p><a href="./folowee_list.php">フォローされている一覧</a></p>

<?php if(empty($user['birthday'])): ?>
未設定
<?php else: ?>
<p><?= $age ?>歳</p>
<?php endif; ?>

<div>
<h2>自己紹介</h2>
<?= nl2br(htmlspecialchars($user['self_introduction'] ?? '')) ?>
</div>
</div>

<hr>
<?php foreach($select_sth as $entry): ?>
<dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
<dt>日時</dt>
<dd><?= $entry['created_at'] ?></dd>
<dt>内容</dt>
<dd>
<?= htmlspecialchars($entry['body']) ?>
<?php if(!empty($entry['image_filename'])): ?>
<div>
<img src="/image/<?= $entry['image_filename'] ?>" style="max-height: 10em;">
</div>
<?php endif; ?>
</dd>
</dl>
<?php endforeach ?>
