<?php
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');
if (isset($_POST['body'])) {
  // POSTで送られてくるフォームパラメータ body がある場合
  $image_filename = null;
  if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {
    // アップロードされた画像がある場合
    if (preg_match('/^image\//', mime_content_type($_FILES['image']['tmp_name'])) !== 1) {
                            // アップロードされたものが画像ではなかった場合
      header("HTTP/1.1 302 Found");
    header("Location: ./zenkikadai1.php");
  }
  // 元のファイル名から拡張子を取得
  $pathinfo = pathinfo($_FILES['image']['name']);
  $extension = $pathinfo['extension'];
  // 新しいファイル名を決める。他の投稿の画像ファイルと重複しないように時間+乱数で決める。
  $image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.' . $extension;
  $filepath =  '/var/www/upload/image/' . $image_filename;
  move_uploaded_file($_FILES['image']['tmp_name'], $filepath);
  }
  // insertする
  $insert_sth = $dbh->prepare("INSERT INTO zenkikadai (body, image_filename) VALUES (:body, :image_filename)");
  $insert_sth->execute([
      ':body' => $_POST['body'],
      ':image_filename' => $image_filename,
  ]);
  // 処理が終わったらリダイレクトする
  // リダイレクトしないと，リロード時にまた同じ内容でPOSTすることになる
  header("HTTP/1.1 302 Found");
  header("Location: ./zenkikadai1.php");
  return;
}
// いままで保存してきたものを取得
$select_sth = $dbh->prepare('SELECT * FROM zenkikadai ORDER BY created_at DESC');
$select_sth->execute();
?>


<!-- フォームのPOST先はこのファイル自身にする -->
<link rel="stylesheet" href="./css/zenkikadai1.css">
<form method="POST" action="./zenkikadai1.php" enctype="multipart/form-data">
<textarea name="body"></textarea>
<div style="margin: 1em 0;">
<input type="file" accept="image/*" name="image" id="imageInput">
</div>
<button type="submit">送信</button>
</form>
<hr>
<?php foreach($select_sth as $entry): ?>
<dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc; max-width: 800px; margin: 0 auto">
<!-- 個別ページへの遷移 -->
<a href="./zenkikadai1_view.php?id=<?= $entry['id'] ?>">
<div style="display: flex;">
<p style="margin-right: 20px;"><?= $entry['id'] ?></p>
<p><?= $entry['created_at'] ?></p>
</div>
<dt>内容</dt>
<dd>
<?= nl2br(htmlspecialchars($entry['body'])) // 必ず htmlspecialchars() すること ?>
<?php if(!empty($entry['image_filename'])): // 画像がある場合は img 要素を使って表示 ?>
<div>
<img src="/image/<?= $entry['image_filename'] ?>" style="max-height: 10em;">
</div>
<?php endif; ?>
</dd>
</a>
</dl>
<?php endforeach ?>
<!-- 5MB以下の画像をアップロードできないようにする -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const imageInput = document.getElementById("imageInput");
    imageInput.addEventListener("change", () => {
        if (imageInput.files.length < 1) {
        // 未選択の場合
        return;
        }
        if (imageInput.files[0].size > 5 * 1024 * 1024) {
        // ファイルが5MBより多い場合
        alert("5MB以下のファイルを選択してください。");
        imageInput.value = "";
        }
        });
    });
</script>

