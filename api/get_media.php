<?php
require_once '../config.php';

try {
    $stmt = $pdo->query("SELECT * FROM media ORDER BY created_at DESC");
    $mediaList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 格式化数据
    foreach ($mediaList as &$item) {
        $item['is_image'] = str_starts_with($item['file_type'], 'image/');
        $item['created_at'] = date('Y-m-d H:i:s', strtotime($item['created_at']));
    }
    
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'data' => $mediaList]);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['status' => 'error', 'message' => '数据库错误: ' . $e->getMessage()]);
}
?>  