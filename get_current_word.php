<?php
/**
 * 获取房间当前词语（给描述者看）
 */
session_start();
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

include "../config.inc";
$conn = mysqli_connect("localhost", $db_user, $db_password, $db_name, $db_port);
mysqli_set_charset($conn, "utf8");

$room_id = intval($_GET['room_id'] ?? $_SESSION['room']['id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? 0;

if (!$room_id) {
    echo json_encode(['ret_code' => -1, 'error' => '参数错误']);
    exit;
}

// 查询房间
$stmt = mysqli_prepare($conn, "SELECT current_word, word_id, user_id0, user_id1, status FROM tb_room WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $room_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$room = mysqli_fetch_assoc($result)) {
    echo json_encode(['ret_code' => -2, 'error' => '房间不存在']);
    exit;
}

// 判断角色
$is_describer = ($room['user_id0'] == $user_id);

$response = [
    'ret_code' => 0,
    'room_status' => (int)$room['status'],
    'role' => $is_describer ? 'describer' : 'guesser'
];

// 只有描述者能看到词语
if ($is_describer && !empty($room['current_word'])) {
    $response['word'] = $room['current_word'];
    $response['word_id'] = $room['word_id'];
    $_SESSION['selected_word'] = $room['current_word'];
} else if (!$is_describer) {
    // 猜测者看不到词语
    $response['word'] = null;
    $response['hint'] = '等待描述者选择词语...';
}

echo json_encode($response);
mysqli_close($conn);
