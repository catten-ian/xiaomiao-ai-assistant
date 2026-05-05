<?php
/**
 * 检查房间状态（用于轮询等待对手）
 */
session_start();
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

include "../config.inc";
$conn = mysqli_connect("localhost", $db_user, $db_password, $db_name, $db_port);
mysqli_set_charset($conn, "utf8");

$user_id = intval($_SESSION['user_id'] ?? $_GET['user_id'] ?? $_POST['user_id'] ?? 0);
$room_id = intval($_GET['room_id'] ?? $_POST['room_id'] ?? $_SESSION['room']['id'] ?? 0);

if (!$user_id || !$room_id) {
    echo json_encode(['ret_code' => -1, 'error' => '参数错误']);
    exit;
}

// 查询房间状态
$stmt = mysqli_prepare($conn, "SELECT name, user_id0, user_id1, status, current_word, word_id FROM tb_room WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $room_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$room = mysqli_fetch_assoc($result)) {
    echo json_encode(['ret_code' => -2, 'error' => '房间不存在']);
    exit;
}

// 判断当前用户是房主还是加入者
$is_host = ($room['user_id0'] == $user_id);

// 获取对手信息（对手是另一个人）
$rival_id = $is_host ? $room['user_id1'] : $room['user_id0'];
$rival_name = '';

if ($rival_id) {
    $stmt = mysqli_prepare($conn, "SELECT name FROM tb_user WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $rival_id);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($r)) {
        $rival_name = $row['name'];
    }
}

// 更新SESSION
$_SESSION['room'] = ['name' => $room['name'], 'id' => $room_id];
$_SESSION['rival_id'] = $rival_id;

$response = [
    'ret_code' => 0,
    'room_id' => $room_id,
    'room' => $room['name'],
    'room_status' => (int)$room['status'],
    'rival' => $rival_name,
    'rival_id' => $rival_id,
    'has_opponent' => !empty($rival_id),
    'current_word' => $room['current_word'],
    'word_id' => $room['word_id'],
    'is_host' => $is_host
];

// 如果游戏开始，确定角色
if ($room['status'] == 2 && $rival_id) {
    // 房主是描述者，加入者是猜测者
    $response['role'] = $is_host ? 'describer' : 'guesser';
    $_SESSION['role'] = $response['role'];
    
    // 更新用户状态为游戏中
    mysqli_query($conn, "UPDATE tb_user SET type = 3 WHERE id = $user_id");
}

echo json_encode($response);
mysqli_close($conn);
