<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <tittle>Sample 8</title>
    <link rel="stylesheet" href="style.css" type="text/css">
</head>

<body>
    <?php
    if (isset(($_POST['name']))) {
        echo '送信された値: ' . $_POST['name'];
    }
    ?>
    <form action="sample8.php" method="post">
        名前 : <input type="text" name="name">
        <input type="submit" value="送信">
    </form>

    </body>
</html>