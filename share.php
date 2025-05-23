<?php
require_once 'config.php';
$token = $_GET['t'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($token)) {
    die("无效的共享链接");
}

try {
    // 查询共享链接信息
    $stmt = $pdo->prepare(
        "SELECT sl.*, m.file_path, m.file_type, m.title, m.description, u.username 
         FROM shared_links sl
         JOIN media m ON sl.media_id = m.id
         JOIN users u ON m.user_id = u.id
         WHERE sl.token = ? AND (sl.expires_at > NOW() OR sl.expires_at IS NULL)"
    );
    $stmt->execute([$token]);
    $share = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$share) {
        die("共享链接不存在或已过期");
    }
    
    // 检查密码保护
    if (!empty($share['password_hash'])) {
        if (empty($password)) {
            // 需要密码，显示密码表单
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <title>受保护的共享资源</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
                    input { width: 100%; padding: 10px; margin: 10px 0; }
                    button { width: 100%; padding: 10px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h3>此共享资源需要密码</h3>
                    <form method="post">
                        <input type="password" name="password" placeholder="请输入密码" required>
                        <button type="submit">访问</button>
                    </form>
                </div>
            </body>
            </html>
            <?php
            exit;
        }
        
        // 验证密码
        if (!password_verify($password, $share['password_hash'])) {
            die("密码错误");
        }
    }
    
    // 更新访问计数
    $stmt = $pdo->prepare("UPDATE shared_links SET access_count = access_count + 1 WHERE id = ?");
    $stmt->execute([$share['id']]);
    
    // 根据文件类型输出内容
    header('Content-Type: ' . $share['file_type']);
    readfile($share['file_path']);
    
} catch (PDOException $e) {
    die("处理共享链接时出错: " . $e->getMessage());
}
?>  