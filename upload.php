<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media'])) {
    $uploadDir = 'uploads/';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm'];
    
    $file = $_FILES['media'];
    $fileName = uniqid() . '_' . basename($file['name']);
    $filePath = $uploadDir . $fileName;
    $fileType = mime_content_type($file['tmp_name']);
    
    // 验证文件类型
    if (!in_array($fileType, $allowedTypes)) {
        die(json_encode(['status' => 'error', 'message' => '不支持的文件类型']));
    }
    
    // 移动文件到上传目录
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // 保存元数据到数据库
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $isPublic = isset($_POST['public']) ? 1 : 0;
        $userId = $_SESSION['user_id'] ?? 0; // 假设用户已登录
        
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO media (title, description, file_path, file_type, user_id, is_public, created_at)
                VALUES (:title, :description, :file_path, :file_type, :user_id, :is_public, NOW())"
            );
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':file_path' => $filePath,
                ':file_type' => $fileType,
                ':user_id' => $userId,
                ':is_public' => $isPublic
            ]);
            
            die(json_encode(['status' => 'success', 'message' => '上传成功', 'id' => $pdo->lastInsertId()]));
        } catch (PDOException $e) {
            unlink($filePath); // 删除已上传的文件
            die(json_encode(['status' => 'error', 'message' => '数据库错误: ' . $e->getMessage()]));
        }
    } else {
        die(json_encode(['status' => 'error', 'message' => '文件上传失败']));
    }
}
?>  