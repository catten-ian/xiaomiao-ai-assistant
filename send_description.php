<?php
/**
 * 发送描述（描述者调用）
 * 将描述保存到房间，供猜测者查看
 */
session_start();
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

include "../config.inc";
$conn = mysqli_connect("localhost", $db_user, $db_password, $db_name, $db_port);
mysqli_set_charset($conn, "utf8");

$room_id = intval($_POST['room_id'] ?? $_SESSION['room']['id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? 0;
$description = trim($_POST['description'] ?? '');

if (!$room_id || !$description) {
    echo json_encode(['ret_code' => -1, 'error' => '参数错误']);
    exit;
}

// 查询房间
$stmt = mysqli_prepare($conn, "SELECT user_id0, user_id1, current_word, round FROM tb_room WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $room_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$room = mysqli_fetch_assoc($result)) {
    echo json_encode(['ret_code' => -2, 'error' => '房间不存在']);
    exit;
}

// 根据轮数判断描述者
$is_odd_round = ($room['round'] % 2 === 1);
$describer_id = $is_odd_round ? $room['user_id0'] : $room['user_id1'];

// 验证是描述者
if ($describer_id != $user_id) {
    echo json_encode(['ret_code' => -3, 'error' => '只有描述者可以发送描述']);
    exit;
}

// 保存描述到房间
$stmt = mysqli_prepare($conn, "UPDATE tb_room SET description = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'si', $description, $room_id);
mysqli_stmt_execute($stmt);

echo json_encode([
    'ret_code' => 0,
    'message' => '描述已发送',
    'description' => $description,
    'word' => $room['current_word']
]);

mysqli_close($conn);