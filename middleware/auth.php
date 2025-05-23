<?php
// 验证用户是否已登录
function authenticate() {
    session_start();
    
    // 检查会话
    if (!isset($_SESSION['user_id'])) {
        // 检查持久化会话
        if (isset($_COOKIE['session_id'])) {
            require_once '../config.php';
            
            $sessionId = $_COOKIE['session_id'];
            
            try {
                $stmt = $pdo->prepare("SELECT user_id FROM user_sessions WHERE id = ? AND expires_at > NOW()");
                $stmt->execute([$sessionId]);
                $session = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($session) {
                    // 恢复会话
                    $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE id = ?");
                    $stmt->execute([$session['user_id']]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($user) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role'];
                        return true;
                    }
                }
            } catch (PDOException $e) {
                // 忽略数据库错误，继续执行未授权流程
            }
        }
        
        // 未登录，返回错误
        header('HTTP/1.1 401 Unauthorized');
        die(json_encode(['status' => 'error', 'message' => '请先登录']));
    }
    
    return true;
}

// 验证用户是否具有管理员权限
function authorizeAdmin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('HTTP/1.1 403 Forbidden');
        die(json_encode(['status' => 'error', 'message' => '权限不足']));
    }
    
    return true;
}

// 验证CSRF令牌
function validateCsrfToken() {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (empty($csrfToken) || $csrfToken !== $_SESSION['csrf_token']) {
            header('HTTP/1.1 403 Forbidden');
            die(json_encode(['status' => 'error', 'message' => 'CSRF验证失败']));
        }
    }
    
    return true;
}
?>  