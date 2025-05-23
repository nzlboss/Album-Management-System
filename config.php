<?php
// 数据库配置
define('DB_HOST', 'localhost');
define('DB_NAME', 'photo_gallery');
define('DB_USER', 'username');
define('DB_PASS', 'password');

// 创建数据库连接
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("set names utf8mb4");
} catch(PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}
?>  