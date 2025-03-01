<?php

session_start();

$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');
if (!empty($_POST['email']) && !empty($_POST['password'])) {
  // POSTで email と password が送られてきた場合のみログイン処理をする
  // email から会員情報を引く
  $select_sth = $dbh->prepare("SELECT * FROM users WHERE email = :email ORDER BY id DESC LIMIT 1");
  $select_sth->execute([
      ':email' => $_POST['email'],
  ]);
  $user = $select_sth->fetch();
  if (empty($user)) {
    // 入力されたメールアドレスに該当する会員が見つからなければ、処理を中断しエラー用クエリパラメータ付きのログイン画面URLにリダイレクト
    header("HTTP/1.1 302 Found");
    header("Location: ./login.php?error=1");
    return;
  }
  /*
  // パスワードが正しいかチェック
  //
  $password_hash = mb_substr($user['password'], 0, 64); // 0文字目から64文字分がハッシュ
  $salt = mb_substr($user['password'], 64, 64); // 64文字目から64文字分がソルト
                                                // $correct_password = hash('sha256', $_POST['password'] . $salt) === $password_hash;

                                                $input_password_hash = $_POST['password'];
                                                for($i = 1; $i <= 10000; $i++){
                                                $input_password_hash = hash('sha256', $input_password_hash . $salt);
                                                }
                                                $correct_password = $input_password_hash === $password_hash;
   */
/*
  $hash = '$2y$10$nD0.1tTQVbq4AkakLwEO6Oqi6qOd8OU3koxUbefZ0aVKkmXdY4sMWbc54d079a09e83566d4e034a238a93a6ffced56d083218af303bc5517ee472b8';
if (password_verify($_POST['password'], $hash)) {
  $correct_password = 'true';
}else{
  $correct_password = 'false';
}
*/
//$input_password_hash = $_POST['password'];
  //foreach (range(1, 10000) as $count) {
  // ソルトを追加してハッシュ化... を10,000回繰り返す
  // $input_password_hash = hash('sha256', $input_password_hash . $salt);
  //}
  //$correct_password = $input_password_hash === $password_hash;

$correct_password = password_verify($_POST['password'], $user['password']);

  if (!$correct_password) {
    // パスワードが間違っていれば、処理を中断しエラー用クエリパラメータ付きのログイン画面URLにリダイレクト
    header("HTTP/1.1 302 Found");
    header("Location: ./login.php?error=1");
    return;
  }
  /*
# ここからセッションの独自実装 (詳細は後期第2回授業参照) ##############
  // セッションIDの取得(なければ新規で作成&設定)
  $session_cookie_name = 'session_id';
  $session_id = $_COOKIE[$session_cookie_name] ?? base64_encode(random_bytes(64));
  if (!isset($_COOKIE[$session_cookie_name])) {
  setcookie($session_cookie_name, $session_id);
  }
  // 接続 (redisコンテナの6379番ポートに接続)
  $redis = new Redis();
  $redis->connect('redis', 6379);
  // Redisにセッション変数を保存しておくキー
  $redis_session_key = "session-" . $session_id;
  // Redisからセッションのデータを読み込み
  // 既にセッション変数(の配列)が何かしら格納されていればそれを，なければ空の配列を $session_values変数に保存
  $session_values = $redis->exists($redis_session_key)
  ? json_decode($redis->get($redis_session_key), true)
  : [];

  // セッションにログインできた会員情報の主キー(id)を設定
  $session_values["login_user_id"] = $user['id'];
  // セッションをRedisに保存
  $redis->set($redis_session_key, json_encode($session_values));
# セッションここまで ###################################################
   */
  $_SESSION["login_user_id"] = $user['id'];

  // ログインが成功したらログイン完了画面にリダイレクト
  header("HTTP/1.1 302 Found");
  header("Location: ./login_finish.php");
  return;
}
?>
<h1>ログイン</h1>
<!-- ログインフォーム -->
<form method="POST">
<!-- input要素のtype属性は全部textでも動くが、適切なものに設定すると利用者は使いやすい -->
<label>
メールアドレス:
<input type="email" name="email">

</label>
<br>
<label>
パスワード:
<input type="password" name="password" minlength="6">
</label>
<br>
<button type="submit">決定</button>
</form>
<?php if(!empty($_GET['error'])): // エラー用のクエリパラメータがある場合はエラーメッセージ表示 ?>
<div style="color: red;">
メールアドレスかパスワードが間違っています。
</div>
<?php endif; ?>
<a href="./signup.php">新規登録はこちら</a>

