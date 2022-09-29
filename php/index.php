<?php
// 必要テーブル
// drink_information_table: ドリンク情報
// stock_table: 在庫数管理
// buy_history_table: 購入履歴

// MySQL接続情報
$host = ''; //データベースのホスト名又はIPアドレス
$username = '';  //MySQLのユーザ名
$passwd   = '';    //MySQLのパスワード
$dbname   = '';    //データベース名
$img_dir = '../img/';

//初期化
$drink_information_list = array(); // エラーメッセージ

// コネクション取得
if ($link = mysqli_connect($host, $username, $passwd, $dbname)) {
    // 文字コードセット
    mysqli_set_charset($link, 'UTF8');

    // ステータスが公開になっているもののみを表示
    $sql = "SELECT drink_information_table.drink_id, drink_name, price, img, status, stock_table.num_stocks
            FROM drink_information_table
            JOIN stock_table
            ON drink_information_table.drink_id = stock_table.drink_id
            WHERE status = 1;";

    if ($result = mysqli_query($link, $sql)) {
        $i = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $drink_info_list[$i]['drink_id']
                = htmlspecialchars($row['drink_id'], ENT_QUOTES, 'UTF-8');
            $drink_info_list[$i]['drink_name']
                = htmlspecialchars($row['drink_name'], ENT_QUOTES, 'UTF-8');
            $drink_info_list[$i]['price']
                = htmlspecialchars($row['price'], ENT_QUOTES, 'UTF-8');
            $drink_info_list[$i]['img']
                = htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8');
            $drink_info_list[$i]['status']
                = htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8');
            $drink_info_list[$i]['num_stocks']
                = htmlspecialchars($row['num_stocks'], ENT_QUOTES, 'UTF-8');
            $i++;
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
    <link rel="stylesheet" href="../css/index.css">
    <title>自動販売機</title>
</head>

<body>
    <h1>自動販売機</h1>
    <form action="result.php" method="post">
        <div>金額<input type="text" name="money" value=""></div>
        <div id="flex">
            <?php foreach ($drink_info_list as $values) { ?>
                <div class="drink">
                    <span><img class="img_size" src="<?php print $img_dir . $values['img']; ?>"></span>
                    <span><?php print($values['drink_name']); ?></span>
                    <span><?php print($values['price']); ?>円</span>
                    <?php if ($values['num_stocks'] === "0") { ?></span>
                        <span class="red">売り切れ</span>
                    <?php } else { ?>
                        <input type="radio" name="drink_id" value="<?php print($values['drink_id']); ?>">
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
        <div id="submit">
            <input type="submit" value="■□■□■ 購入 ■□■□■">
        </div>
    </form>
</body>

</html>
