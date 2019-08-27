<?php
    session_start();
    session_regenerate_id(true);
    if (isset($_SESSION['login']) == false) {
        echo 'ログインされていません<br>';
        print '<a href="../staff_login/staff_login.html">ログイン画面へ</a>';
        exit();
    } else {
        echo $_SESSION['staff_name'].'さんログイン中<br><br>';
    }
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>ろくまる農園</title>
  </head>
  <body>
    <?php
    try {
        $year = $_POST['year'];
        $month = $_POST['month'];
        $day = $_POST['day'];

        $dsn = 'mysql:dbname=shop;host=localhost;charset=utf8';
        $user = 'root';
        $password = '';
        $dbh = new PDO($dsn, $user, $password);
        $dbh -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = '
            SELECT
                dat_sales.code,
                dat_sales.date,
                dat_sales.code_member,
                dat_sales.name AS dat_sales_name,
                dat_sales.email,
                dat_sales.postal1,
                dat_sales.postal2,
                dat_sales.address,
                dat_sales.tel,
                dat_sales_product.code_product,
                mst_product.name AS mst_product_name,
                dat_sales_product.price,
                dat_sales_product.quantity
            FROM
                dat_sales, dat_sales_product, mst_product
            WHERE
                dat_sales.code = dat_sales_product.code_sales
                AND dat_sales_product.code_product = mst_product.code
                AND substr(dat_sales.date, 1, 4) = ?
                AND substr(dat_sales.date, 6, 2) = ?
                AND substr(dat_sales.date, 9, 2) = ?
            ';

        $stmt = $dbh -> prepare($sql);
        $data[] = $year;
        $data[] = $month;
        $data[] = $day;
        $stmt -> execute($data);

        $dbh = null;


        $csv = '注文コード,注文日時,会員番号,氏名,メール,郵便番号,住所,TEL,商品コード,商品名,価格,数量';
        $csv .= "\n";
        while (true) {
            $rec = $stmt -> fetch(PDO::FETCH_ASSOC);
            if ($rec == false) {
                break;
            }
            $csv .= $rec['code'].',';
            $csv .= $rec['date'].',';
            $csv .= $rec['code_member'].',';
            $csv .= $rec['dat_sales_name'].',';
            $csv .= $rec['email'].',';
            $csv .= $rec['postal1'].'-'.$rec['postal2'].',';
            $csv .= $rec['address'].',';
            $csv .= $rec['tel'].',';
            $csv .= $rec['code_product'].',';
            $csv .= $rec['mst_product_name'].',';
            $csv .= $rec['price'].',';
            $csv .= $rec['quantity'];
            $csv .= "\n";
        }

        // print nl2br($csv);

        $file = fopen('./chumon.csv', 'w');
        fputs($file, $csv);
        fclose($file);

    }
    catch (Exception $e) {
        print 'ただいま障害により大変ご迷惑をお掛けしておりますstaff_list.php';
        exit();
    }
    ?>

    <a href="chumon.csv">注文データのダウンロード</a><br>
    <br>
    <a href="order_download.php">日付選択へ</a><br>
    <br>
    <a href='../staff_login/staff_top.php'>トップメニューに戻る</a><br>
  </body>
</html>