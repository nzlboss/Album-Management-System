<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // 验证输入
    if (empty($username) || empty($email) || empty($password)) {
        die(json_encode(['status' => 'error', 'message' => '所有字段均为必填项']));
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die(json_encode(['status' => 'error', 'message' => '邮箱格式不正确']));
    }
    
    if ($password !== $confirmPassword) {
        die(json_encode(['status' => 'error', 'message' => '两次输入的密码不匹配']));
    }
    
    if (strlen($password) < 8) {
        die(json_encode(['status' => 'error', 'message' => '密码长度至少为8个字符']));
    }
    
    try {
        // 检查邮箱是否已存在
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            die(json_encode(['status' => 'error', 'message' => '该邮箱已被注册']));
        }
        
        // 检查用户名是否已存在
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            die(json_encode(['status' => 'error', 'message' => '该用户名已被使用']));
        }
        
        // 哈希密码
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // 创建用户
        $stmt = $pdo->prepare(
            "INSERT INTO users (username, email, password_hash, created_at)
            VALUES (:username, :email, :password_hash, NOW())"
        );
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => $passwordHash
        ]);
        
        // 自动登录用户
        $userId = $pdo->lastInsertId();
        
        session_start();
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'user';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        echo json_encode(['status' => 'success', 'message' => '注册成功', 'redirect' => '/dashboard.php']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => '数据库错误: ' . $e->getMessage()]);
    }
}
?>  