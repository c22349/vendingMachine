<?php
// MySQL接続情報
$host = ''; //データベースのホスト名又はIPアドレス
$username = '';  //MySQLのユーザ名
$passwd   = '';    //MySQLのパスワード
$dbname   = '';    //データベース名
$img_dir = '../img/';

//初期化
$err_msg = array();
$change = 0;
$drink_name = '';
$date = date('Y/m/d H:i:s');
$money = 0;

// コネクション取得
if ($link = mysqli_connect($host, $username, $passwd, $dbname)) {
    // 文字コードセット
    mysqli_set_charset($link, 'UTF8');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['money']) === TRUE) {
            $money = $_POST['money'];
        }

        //入力値チェック　isset
        if (isset($_POST['drink_id']) === FALSE) {
            $err_msg[] = '商品を選択してください';
        }
        if (ctype_digit($money) === FALSE) {
            $err_msg[] = 'お金を投入してください';
        }

        //エラーがなかった場合
        if (count($err_msg) === 0) {
            $drink_id = (int)$_POST['drink_id'];

            $sql = 'SELECT drink_name, price, img, status, num_stocks
                    FROM drink_information_table
                    JOIN stock_table
                    ON drink_information_table.drink_id = stock_table.drink_id
                    WHERE drink_information_table.drink_id = ' . $drink_id;

            if ($result = mysqli_query($link, $sql)) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $price =  htmlspecialchars($row['price'], ENT_QUOTES, 'UTF-8');
                    $drink_name =  htmlspecialchars($row['drink_name'], ENT_QUOTES, 'UTF-8');
                    $img =  htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8');
                    $status =  htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8');
                    $num_stocks =  htmlspecialchars($row['num_stocks'], ENT_QUOTES, 'UTF-8');
                }
                if ($money > $price) {
                    $change = $money - $price;
                } else if ($money === $price) {
                    $change = 0;
                } else {
                    $err_msg[] = 'お金が足りません';
                }
                //公開ステータスチェック
                if ($status === "0") {
                    $err_msg[] = '非公開になりました';
                }
                //在庫数チェック
                if ($num_stocks === "0") {
                    $err_msg[] = '売切れになりました';
                }
                //他の入力チェック
            } else {
                $err_msg[] = '商品の表示ができません';
            }
            if (count($err_msg) === 0) {
                //更新系の処理を行う前にトランザクション開始(オートコミットをオフ）
                mysqli_autocommit($link, false);
                $sql = 'UPDATE stock_table SET num_stocks = ' . $num_stocks . '- 1, update_date = now() WHERE drink_id = ' . $drink_id;
                if (mysqli_query($link, $sql) === FALSE) {
                    $err_msg[] = '購入時に不備が発生';
                }
                $sql = 'INSERT INTO buy_history_table(drink_id,buy_date) VALUES (' . $drink_id . ',\'' . $date . '\')';
                if (mysqli_query($link, $sql) === FALSE) {
                    $err_msg[] = '購入時に不備が発生';
                }
                // トランザクション成否判定
                if (count($err_msg) === 0) {
                    // 処理確定
                    mysqli_commit($link);
                } else {
                    // 処理取消
                    mysqli_rollback($link);
                }
            }
        }
    }
} else {
    $err_msg[] = '接続失敗しました';
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>自動販売機結果</title>
</head>

<body>
    <h1>自動販売機結果</h1>
    <?php foreach ($err_msg as $value) { ?>
        <p><?php print($value); ?></p>
    <?php } ?>
    <?php if (count($err_msg) === 0) { ?>
        <img src="<?php print $img_dir . $img; ?>">
        <p>がしゃん！【<?php print $drink_name; ?>】が買えました！ </p>
        <p>おつりは【<?php print($change); ?>円】です</p>
    <?php } ?>
    <footer>
        <a href="index.php">戻る</a>
    </footer>
</body>

</html>
