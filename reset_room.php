<?php
/**
 * 重置房间状态 - 开始新一轮游戏
 * 清空词语和描述，交换角色（通过 round 字段判断）
 */
session_start();
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

include "../config.inc";
$conn = mysqli_connect("localhost", $db_user, $db_password, $db_name, $db_port);
mysqli_set_charset($conn, "utf8");

$room_id = intval($_POST['room_id'] ?? $_GET['room_id'] ?? $_SESSION['room']['id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? 0;

if (!$room_id) {
    echo json_encode(['ret_code' => -1, 'error' => '参数错误']);
    exit;
}

// 查询房间
$stmt = mysqli_prepare($conn, "SELECT user_id0, user_id1, status, round FROM tb_room WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $room_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$room = mysqli_fetch_assoc($result)) {
    echo json_encode(['ret_code' => -2, 'error' => '房间不存在']);
    exit;
}

// 验证用户是房间成员
if ($room['user_id0'] != $user_id && $room['user_id1'] != $user_id) {
    echo json_encode(['ret_code' => -3, 'error' => '你不是房间成员']);
    exit;
}

// 增加轮数
$new_round = $room['round'] + 1;

// 判断新角色：奇数轮 user_id0 是描述者，偶数轮 user_id1 是描述者
// round 1: user_id0 描述, user_id1 猜测
// round 2: user_id1 描述, user_id0 猜测
// round 3: user_id0 描述, user_id1 猜测
$is_odd_round = ($new_round % 2 === 1);
$is_user_id0 = ($room['user_id0'] == $user_id);

// 判断当前用户的新角色
if ($is_odd_round) {
    // 奇数轮：user_id0 是描述者
    $new_role = $is_user_id0 ? 'describer' : 'guesser';
} else {
    // 偶数轮：user_id1 是描述者
    $new_role = $is_user_id0 ? 'guesser' : 'describer';
}

// 更新房间：清空词语和描述，增加轮数，状态设为游戏中
$stmt = mysqli_prepare($conn, "UPDATE tb_room SET 
    current_word = NULL, 
    word_id = NULL, 
    description = NULL,
    round = ?, 
    status = 2 
    WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $new_round, $room_id);
mysqli_stmt_execute($stmt);

// 更新 SESSION
$_SESSION['role'] = $new_role;
$_SESSION['selected_word'] = null;
$_SESSION['word_id'] = null;

echo json_encode([
    'ret_code' => 0,
    'message' => '房间已重置，开始新一轮',
    'round' => $new_round,
    'new_role' => $new_role,
    'room_status' => 2
]);

mysqli_close($conn);