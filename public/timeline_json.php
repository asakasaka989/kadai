<?php
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

session_start();
if(empty($_SESSION['login_user_id'])){
  header("HTTP/1.1 401 Unauthorized");
  header("Content-Type: application/json");
  print(json_encode(['entries' => []]));
  return;
}

// 現在のログイン情報を取得する 
$user_select_sth = $dbh->prepare("SELECT * from users WHERE id = :id");
$user_select_sth->execute([':id' => $_SESSION['login_user_id']]);
$user = $user_select_sth->fetch();

// 投稿データを取得
$sql = 'SELECT bbs_entries.*, users.name AS user_name, users.icon_filename AS user_icon_filename'
. ' FROM bbs_entries'
. ' INNER JOIN users ON bbs_entries.user_id = users.id'
. ' WHERE'
. '   bbs_entries.user_id IN'
. '     (SELECT followee_user_id FROM user_relationships WHERE follower_user_id = :login_user_id)'
. '   OR bbs_entries.user_id = :login_user_id'
. ' ORDER BY bbs_entries.created_at DESC';
$select_sth = $dbh->prepare($sql);
$select_sth->execute([
    ':login_user_id' => $_SESSION['login_user_id'],
]);

// bodyのHTMLを出力するための関数を用意する
function bodyFilter (string $body): string
{
  $body = htmlspecialchars($body); // エスケープ処理
  $body = nl2br($body); // 改行文字を<br>要素に変換

  return $body;
}

// JSONに吐き出す用のentries
$result_entries = [];
foreach ($select_sth as $entry) {
  $result_entry = [
    'id' => $entry['id'],
    'user_name' => $entry['user_name'],
    'user_profile_url' => '/profile.php?user_id=' . $entry['user_id'],
    'image_filename' => empty($entry['image_filename']) ? '' : ('/image/'. $entry['image_filename']),
    'user_icon_file_url' => empty($entry['user_icon_filename']) ? '' : ('/image/' . $entry['user_icon_filename']),
    'body' => bodyFilter($entry['body']),
    'created_at' => $entry['created_at'],
  ];
    $result_entries[] = $result_entry;
}

header("HTTP/1.1 200 OK");
header("Content-Type: application/json");
print(json_encode(['entries' => $result_entries]));
