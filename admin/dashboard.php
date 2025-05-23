<?php
session_start();
require_once '../config.php';

// 验证管理员登录
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit;
}

// 获取统计数据
$stmt = $pdo->query("SELECT COUNT(*) as total_media FROM media");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
$stats += $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT file_type, COUNT(*) as count FROM media GROUP BY file_type");
$mediaTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>媒体管理后台</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { background: #f9f9f9; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .stats { display: flex; gap: 20px; }
        .stat-card { flex: 1; background: #fff; padding: 15px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f5f5f5; }
        .actions { display: flex; gap: 10px; }
        .btn { padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #007BFF; color: white; }
        .btn-danger { background: #DC3545; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>媒体管理后台</h1>
        
        <div class="stats">
            <div class="stat-card">
                <h3>总媒体数</h3>
                <p><?= $stats['total_media'] ?></p>
            </div>
            <div class="stat-card">
                <h3>用户数</h3>
                <p><?= $stats['total_users'] ?></p>
            </div>
        </div>
        
        <div class="card">
            <h3>媒体类型分布</h3>
            <table>
                <tr>
                    <th>类型</th>
                    <th>数量</th>
                </tr>
                <?php foreach ($mediaTypes as $type): ?>
                <tr>
                    <td><?= $type['file_type'] ?></td>
                    <td><?= $type['count'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        <div class="card">
            <h3>最近上传的媒体</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>标题</th>
                    <th>类型</th>
                    <th>上传时间</th>
                    <th>操作</th>
                </tr>
                <?php
                $stmt = $pdo->query("SELECT * FROM media ORDER BY created_at DESC LIMIT 10");
                $recentMedia = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($recentMedia as $media):
                ?>
                <tr>
                    <td><?= $media['id'] ?></td>
                    <td><?= htmlspecialchars($media['title']) ?></td>
                    <td><?= $media['file_type'] ?></td>
                    <td><?= date('Y-m-d H:i:s', strtotime($media['created_at'])) ?></td>
                    <td class="actions">
                        <button class="btn btn-primary">编辑</button>
                        <button class="btn btn-danger">删除</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        <a href="logout.php">退出登录</a>
    </div>
</body>
</html>  