<?php
session_start();
require_once '../config.php';
require_once '../middleware/auth.php';

// 验证管理员身份
authorizeAdmin();

// 处理用户操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = $_POST['user_id'] ?? 0;
    
    try {
        if ($action === 'delete' && $userId > 0 && $userId !== $_SESSION['user_id']) {
            // 删除用户及其相关数据
            $pdo->beginTransaction();
            
            // 删除用户会话
            $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // 删除用户设置
            $stmt = $pdo->prepare("DELETE FROM user_settings WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // 删除用户媒体
            $stmt = $pdo->prepare("SELECT file_path FROM media WHERE user_id = ?");
            $stmt->execute([$userId]);
            $mediaFiles = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // 删除物理文件
            foreach ($mediaFiles as $filePath) {
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // 删除媒体记录
            $stmt = $pdo->prepare("DELETE FROM media WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // 删除用户
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            
            $pdo->commit();
            $message = "用户已删除";
        } elseif ($action === 'update_role' && $userId > 0 && $userId !== $_SESSION['user_id']) {
            $role = $_POST['role'] ?? 'user';
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$role, $userId]);
            $message = "用户角色已更新";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "操作失败: " . $e->getMessage();
    }
}

// 获取用户列表
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>用户管理 - 管理后台</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .card { background: #f9f9f9; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f5f5f5; }
        .actions { display: flex; gap: 10px; }
        .btn { padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #007BFF; color: white; }
        .btn-danger { background: #DC3545; color: white; }
        .btn-warning { background: #FFC107; color: black; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-success { background-color: #D4EDDA; color: #155724; }
        .alert-error { background-color: #F8D7DA; color: #721C24; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>用户管理</h1>
            <a href="dashboard.php" class="btn btn-primary">返回仪表盘</a>
        </div>
        
        <?php if (isset($message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h3>用户列表</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>用户名</th>
                    <th>邮箱</th>
                    <th>角色</th>
                    <th>创建时间</th>
                    <th>最后登录</th>
                    <th>操作</th>
                </tr>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="action" value="update_role">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <select name="role" onchange="this.form.submit()">
                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>用户</option>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>管理员</option>
                                <option value="guest" <?= $user['role'] === 'guest' ? 'selected' : '' ?>>访客</option>
                            </select>
                        </form>
                    </td>
                    <td><?= $user['created_at'] ?></td>
                    <td><?= $user['last_login'] ?? '从未登录' ?></td>
                    <td class="actions">
                        <button class="btn btn-danger" onclick="if(!confirm('确定要删除此用户吗？')) return false; document.getElementById('delete_form_<?= $user['id'] ?>').submit()">删除</button>
                        <form id="delete_form_<?= $user['id'] ?>" method="post" style="display: none;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>  