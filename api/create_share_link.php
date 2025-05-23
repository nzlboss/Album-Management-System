<?php
require_once '../config.php';
require_once '../middleware/auth.php';

authenticate();
validateCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mediaId = $_POST['media_id'] ?? 0;
    $expiresIn = $_POST['expires_in'] ?? 86400; // 默认24小时
    $password = $_POST['password'] ?? '';
    
    try {
        // 查询媒体信息
        $stmt = $pdo->prepare("SELECT id, file_path, user_id FROM media WHERE id = ?");
        $stmt->execute([$mediaId]);
        $media = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$media) {
            die(json_encode(['status' => 'error', 'message' => '文件不存在']));
        }
        
        // 验证用户权限
        if ($media['user_id'] !== $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
            die(json_encode(['status' => 'error', 'message' => '没有权限']));
        }
        
        // 生成令牌
        $token = bin2hex(random_bytes(32));
        $passwordHash = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;
        $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);
        
        // 保存共享链接
        $stmt = $pdo->prepare(
            "INSERT INTO shared_links (media_id, token, password_hash, expires_at)
            VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$mediaId, $token, $passwordHash, $expiresAt]);
        
        // 返回共享链接
        $shareUrl = $_SERVER['HTTP_HOST'] . '/share.php?t=' . $token;
        
        echo json_encode([
            'status' => 'success',
            'message' => '共享链接已生成',
            'url' => $shareUrl,
            'token' => $token,
            'expires_at' => $expiresAt
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => '生成链接失败: ' . $e->getMessage()]);
    }
}
?>  