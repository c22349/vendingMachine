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
$link = mysqli_connect($host, $username, $passwd, $dbname);

$img_dir = '../img/';

//初期化
$success_msg = array(); //成功メッセージ
$err_msg = array(); // エラーメッセージ
$sql_kind = ''; //押されたボタンの種類
$new_name = '';
$new_price = '';
$new_stock = '';
$new_status = '';
$date = '';
$drink_id = '';
$drink_data = array();
$chenge = '';

// コネクション取得
if ($link) {
    // 文字コードセット
    mysqli_set_charset($link, 'UTF8');
    //POST送信チェック
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        //sql_kindのどのボタンが押されたか
        if (isset($_POST['sql_kind']) === TRUE) {
            $sql_kind = $_POST['sql_kind'];
        }
        //もし$sql_kindがinsertの時
        if ($sql_kind === 'insert') {
            //名前チェック
            if (isset($_POST['new_name']) === TRUE) {
                $new_name = $_POST['new_name'];
                $new_name = trim($new_name);
                $new_name = str_replace('　', '', $new_name); //全角スペースをトリム
                if (preg_match('/[ ]{2,}/', $new_name) === 1 || preg_match('/[　]{2,}/', $new_name) === 1) {
                    $err_msg[] = '不正な名前の入力です';
                } else if (mb_strlen($new_name) === 0) {
                    $err_msg[] = '名前の入力を再度お願いします';
                }
            }
            //値段チェック
            if (isset($_POST['new_price']) === TRUE) {
                $new_price = $_POST['new_price'];
                $new_price = trim($new_price);
                if (preg_match("/^[0-9]+$/", $new_price) !== 1) {
                    $err_msg[] = '値段を入力を再度お願いします';
                }
            }

            //個数チェック
            if (isset($_POST['new_stock']) === TRUE) {
                $new_stock = $_POST['new_stock'];
                $new_stock = trim($new_stock);
                if (preg_match("/^[0-9]+$/", $new_stock) !== 1) {
                    $err_msg[] = '個数を入力を再度お願いします';
                }
            }

            //商品画像チェック
            if (isset($_FILES['new_img']) === TRUE) {
                if (is_uploaded_file($_FILES['new_img']['tmp_name']) === TRUE) {
                    //画像の拡張子を取得
                    $extension = pathinfo($_FILES['new_img']['name'], PATHINFO_EXTENSION);
                    //MIMEタイプの取得
                    $path = $_FILES['new_img']['tmp_name'];
                    $mime = shell_exec('file -bi ' . escapeshellcmd($path));
                    $mime = trim($mime);
                    $mime_type = preg_replace("/ [^ ]*/", "", $mime);

                    //拡張子の配列
                    $extension_array = array(
                        'gif' => 'image/gif;',
                        'jpg' => 'image/jpeg;',
                        'png' => 'image/png;'
                    );
                    //MIMEタイプから拡張子を出力
                    if ($img_extension = array_search($mime_type, $extension_array, true)) {
                        //指定の拡張子であるかどうかチェック
                        //if ($img_extension === 'jpg' || $img_extension === 'jpeg' || $img_extension === 'png') {


                        //ファイルのサイズチェック(2MBまで)
                        if ($_FILES['new_img']['size'] > (2 * 1024 * 1024)) {
                            $err_msg[] = 'ファイルのサイズが大きいです(2MB迄)';
                        } else {
                            //保存する新しいファイル名の生成（ユニークな値を設定する）
                            $new_img_filename = sha1(uniqid(mt_rand(), true)) . '.' . $extension;
                            //同名ファイルが存在するかどうかチェック
                            if (is_file($img_dir . $new_img_filename) !== TRUE) {
                                if (move_uploaded_file($_FILES['new_img']['tmp_name'], $img_dir . $new_img_filename) !== TRUE) {
                                    $err_msg[] = 'ファイルアップロードに失敗しました';
                                }
                            }
                        }


                        // } else {
                        //     $err_msg[] = 'ファイル形式が異なります。画像ファイルはJPEG又はPNGのみ利用可能です。';
                        // }
                    } else {
                        $err_msg[] = 'ファイル形式が異なるか、画像ではありません。画像ファイルはJPEG又はPNGのみ利用可能です。';
                    }
                } else {
                    $err_msg[] = '商品画像を選択してください';
                }

                //status変更チェック
                if (isset($_POST['new_status']) === TRUE) {
                    $new_status = $_POST['new_status'];
                    if ($new_status != "0" && $new_status != "1") {
                        $err_msg[] = '不正なステータスです';
                        $new_status = 0;
                    }
                }
            } else {
                $err_msg[] = '商品画像を選択してください';
            }

            //err_msgがない時
            if (count($err_msg) === 0) {
                //商品情報を入力し、商品を追加できる。
                //日付情報取得
                $date = date('Y-m-d H:i:s');
                //更新系の処理を行う前にトランザクション開始(オートコミットをオフ）
                mysqli_autocommit($link, false);
                $sql = "INSERT INTO
                        drink_information_table (drink_name, price, img, creat_date, update_date, status)
                        VALUES
                        ( '" . $new_name . "','" . $new_price . "','" . $new_img_filename . "','" . $date . "','" . $date . "','" . $new_status . "')";
                if (mysqli_query($link, $sql) === TRUE) {
                    //A_Iを取得
                    $drink_id = mysqli_insert_id($link);
                    $sql = "INSERT INTO
                            stock_table (drink_id, num_stocks, creat_date, update_date)
                            VALUES
                            ( '" . $drink_id . "','" . $new_stock . "','" . $date . "','" . $date . "')";

                    if (mysqli_query($link, $sql) !== TRUE) {
                        $err_msg[] = '商品追加できませんでした';
                    }
                } else {
                    $err_msg[] = '商品追加できませんでした';
                }
                // トランザクション成否判定
                if (count($err_msg) === 0) {
                    $success_msg[] = '商品の追加成功';
                    // 処理確定
                    mysqli_commit($link);
                } else {
                    // 処理取消
                    mysqli_rollback($link);
                }
            }
        }
        //sql_kindがupdateの時
        else if ($sql_kind === 'update') {
            //変更後の在庫数を取得する
            if (isset($_POST['update_stock']) === TRUE) {
                $update_stock = $_POST['update_stock'];
                $drink_id = $_POST['drink_id'];
            }
            //入力チェック
            if ($update_stock === '') {
                $err_msg[] = '個数を入力してください。';
            } else if (preg_match('/\A\d+\z/', $update_stock) !== 1) {
                $err_msg[] = '個数は半角数字を入力してください';
            }
            if (count($err_msg) === 0) {
                $date = date('Y-m-d H:i:s');
                $sql = 'UPDATE stock_table SET num_stocks = ' . $update_stock . ', update_date = "' . $date . '" WHERE drink_id=' . $drink_id;
                $success_msg[] = '在庫数変更完了';
                if (mysqli_query($link, $sql) !== TRUE) {
                    $err_msg[] = '変更ができませんでした';
                }
            } else {
                $err_msg[] = '在庫数変更できませんでした';
            }
        }
        //sql_kindがchangeの時
        else if ($sql_kind === 'change') {
            //公開、非公開の情報取得
            if (isset($_POST['change_status']) === TRUE) {
                $status = $_POST['change_status'];
                $drink_id = $_POST['drink_id'];
            }
            $date = date('Y-m-d H:i:s');
            //入力チェック
            if (preg_match("/^[0-1]$/", $status) !== 1) {
                $err_msg[] = '不正な情報です';
            }
            if (count($err_msg) === 0) {
                if ($status === "1") {
                    $sql = 'UPDATE drink_information_table SET status = 0, update_date = "' . $date . '" WHERE drink_id = ' . $drink_id;
                } else {
                    $sql = 'UPDATE drink_information_table SET status = 1, update_date = "' . $date . '" WHERE drink_id = ' . $drink_id;
                }
                $success_msg[] = 'ステータス変更成功';
                if (mysqli_query($link, $sql) !== TRUE) {
                    $err_msg[] = 'ステータス変更できませんでした';
                }
            }
        }
    }
    //商品情報を取得
    $sql = 'SELECT drink_information_table.drink_id, drink_information_table.drink_name, drink_information_table.price, drink_information_table.img, stock_table.num_stocks, drink_information_table.status
        FROM drink_information_table JOIN stock_table
        ON drink_information_table.drink_id = stock_table.drink_id';
    // クエリ実行
    if ($result = mysqli_query($link, $sql)) {
        $i = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $drink_data[$i]['drink_id']   = htmlspecialchars($row['drink_id'], ENT_QUOTES, 'UTF-8');
            $drink_data[$i]['drink_name']   = htmlspecialchars($row['drink_name'],   ENT_QUOTES, 'UTF-8');
            $drink_data[$i]['price'] = htmlspecialchars($row['price'], ENT_QUOTES, 'UTF-8');
            $drink_data[$i]['img'] = htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8');
            $drink_data[$i]['status']   = htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8');
            $drink_data[$i]['num_stocks']   = htmlspecialchars($row['num_stocks'],   ENT_QUOTES, 'UTF-8');
            $i++;
        }
    } else {
        $err_msg[] = '変更できませんでした';
    }
    // 結果セットを開放します
    mysqli_free_result($result);
    // 接続を閉じます
    mysqli_close($link);
} else {
    $err_msg[] = '接続失敗しました';
}
//var_dump($err_msg); // エラーの確認が必要ならばコメントを外す
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../css/tool.css">
    <title>自動販売機</title>
</head>

<body>
    <?php foreach ($success_msg as $read) { ?>
        <p><?php print $read; ?></p>
    <?php } ?>

    <?php foreach ($err_msg as $read) { ?>
        <p><?php print $read; ?></p>
    <?php } ?>
    <h1>自動販売機管理ツール</h1>
    <section>
        <h2>新規商品追加</h2>
        <form action="tool.php" form method="post" enctype="multipart/form-data">
            <div><label>名前: <input type="text" name="new_name" value=""></label></div>
            <div><label>値段: <input type="text" name="new_price" value=""></label></div>
            <div><label>個数: <input type="text" name="new_stock" value=""></label></div>
            <div><input type="file" name="new_img"></div>
            <div>
                <select name="new_status">
                    <option value="0">非公開</option>
                    <option value="1">公開</option>
                </select>
            </div>
            <input type="hidden" name="sql_kind" value="insert">
            <div><input type="submit" value="■□■□■商品追加■□■□■"></div>
        </form>
    </section>
    <section>
        <h2>商品情報変更</h2>
        <table>
            <caption>商品一覧</caption>
            <tr>
                <th>商品画像</th>
                <th>商品名</th>
                <th>価格</th>
                <th>在庫数</th>
                <th>ステータス</th>
            </tr>
            <?php foreach ($drink_data as $value) { ?>
                <?php if ($value['status'] === "0") { ?>
                    <tr class="status_false">
                    <?php } else { ?>
                    <tr>
                    <?php } ?>
                    <form method="post">
                        <td><img class="drink_img_width" src="<?php print $img_dir . $value['img']; ?>"></td>
                        <td class="drink_name_width"><?php print($value['drink_name']); ?></td>
                        <td class="text_align_right"><?php print($value['price']); ?>円</td>
                        <td><input type="text" class="input_text_width text_align_right" name="update_stock" value="<?php print($value['num_stocks']); ?>">個&nbsp;&nbsp;<input type="submit" value="変更"></td>
                        <input type="hidden" name="drink_id" value="<?php print($value['drink_id']); ?>">
                        <input type="hidden" name="sql_kind" value="update">
                    </form>
                    <form method="post">
                        <td>
                            <?php if ($value['status'] === "0") { ?>
                                <input type="submit" value="非公開 → 公開">
                            <?php } else { ?>
                                <input type="submit" value="公開 → 非公開">
                            <?php } ?>
                            <input type="hidden" name="change_status" value="<?php print($value['status']); ?>">
                            <input type="hidden" name="drink_id" value="<?php print($value['drink_id']); ?>">
                            <input type="hidden" name="sql_kind" value="change">
                        </td>
                    </form>
                    </tr>
                <?php } ?>
        </table>
    </section>
</body>

</html>
