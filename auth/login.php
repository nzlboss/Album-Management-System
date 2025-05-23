<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    try {
        // 查询用户
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            die(json_encode(['status' => 'error', 'message' => '邮箱或密码错误']));
        }
        
        // 生成会话
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        // 更新最后登录时间
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        // 如果选择"记住我"，创建持久化会话
        if ($remember) {
            $sessionId = bin2hex(random_bytes(16));
            $expiresAt = date('Y-m-d H:i:s', time() + 30 * 24 * 60 * 60); // 30天
            
            $stmt = $pdo->prepare("INSERT INTO user_sessions (id, user_id, ip_address, user_agent, expires_at)
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $sessionId,
                $user['id'],
                $_SERVER['REMOTE_ADDR'],
                substr($_SERVER['HTTP_USER_AGENT'], 0, 255),
                $expiresAt
            ]);
            
            setcookie('session_id', $sessionId, time() + 30 * 24 * 60 * 60, '/', '', true, true);
        }
        
        echo json_encode(['status' => 'success', 'message' => '登录成功', 'redirect' => '/dashboard.php']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => '数据库错误: ' . $e->getMessage()]);
    }
}
?>  