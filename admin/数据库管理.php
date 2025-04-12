<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>数据库备份和恢复</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            padding-top: 50px;
        }
        h1 {
            color: #333;
        }
        form {
            margin-top: 20px;
        }
        button {
            padding: 10px 20px;
            background-color: #008CBA;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .button {
            text-align: center;
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #008CBA;
            color: white;
            text-decoration: none;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <h1>数据库备份和恢复</h1>
    <form method="post">
        <button type="submit" name="backup">备份数据库</button>
    </form>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="sql_file" accept=".sql">
        <button type="submit" name="restore">恢复数据库</button>
    </form>
    <a class="button" href="admin.html">返回到管理员仪表盘</a>
    <a class="button" href="logout.php">注销登录</a>
</body>
</html>

<?php
include 'db_connection.php';

// 备份数据库
if (isset($_POST['backup'])) {
    $backupFile = 'E:/download/' . $dbname . '_backup_' . date("Y-m-d-H-i-s") . '.sql';
    $command = "mysqldump --user={$username} --password={$password} --host={$servername} {$dbname} > {$backupFile}";

    system($command, $output);

    if ($output == 0) {
        echo "备份成功！备份文件已保存到 {$backupFile}";
    } else {
        echo "备份失败！";
    }
}

// 恢复数据库
if (isset($_POST['restore'])) {
    if ($_FILES['sql_file']['error'] == UPLOAD_ERR_OK) {
        $tmpName = $_FILES['sql_file']['tmp_name'];
        $command = "mysql --user={$username} --password={$password} --host={$servername} {$dbname} < {$tmpName}";

        system($command, $output);

        if ($output == 0) {
            echo "恢复成功！";
        } else {
            echo "恢复失败！";
        }
    } else {
        echo "文件上传失败！";
    }
}
?>
