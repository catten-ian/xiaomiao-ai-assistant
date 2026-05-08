<?php
/**
 * 开始新一轮游戏
 * 增加轮数，清空词语和描述
 */
session_start();
include '../config.inc';

header('Content-Type: application/json');

$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

if ($room_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => '参数无效']);
    exit;
}

$conn = mysqli_connect('localhost', $db_user, $db_password, $db_name, $db_port);
if (mysqli_connect_errno()) {
    echo json_encode(['status' => 'error', 'message' => '数据库连接失败']);
    exit;
}

mysqli_set_charset($conn, 'utf8');

// 获取房间信息
$stmt = mysqli_prepare($conn, "SELECT user_id0, user_id1, round FROM tb_room WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $room_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$room = mysqli_fetch_assoc($result);

if (!$room) {
    echo json_encode(['status' => 'error', 'message' => '房间不存在']);
    exit;
}

// 验证用户是房间成员
if ($room['user_id0'] != $user_id && $room['user_id1'] != $user_id) {
    echo json_encode(['status' => 'error', 'message' => '你不是房间成员']);
    exit;
}

// 增加轮数
$new_round = $room['round'] + 1;

// 重置房间状态：清空词语和描述，增加轮数
$stmt = mysqli_prepare($conn, "UPDATE tb_room SET 
    current_word = NULL, 
    word_id = NULL, 
    description = NULL,
    round = ?,
    status = 2
    WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $new_round, $room_id);
mysqli_stmt_execute($stmt);

echo json_encode([
    'status' => 'success',
    'message' => '新一轮开始',
    'round' => $new_round
]);

mysqli_close($conn);
?>