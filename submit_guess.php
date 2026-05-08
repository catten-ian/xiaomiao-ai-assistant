<?php
/**
 * 提交猜测（猜测者调用）
 * 判断猜测是否正确，返回结果
 */
session_start();
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

include "../config.inc";
$conn = mysqli_connect("localhost", $db_user, $db_password, $db_name, $db_port);
mysqli_set_charset($conn, "utf8");

$room_id = intval($_POST['room_id'] ?? $_SESSION['room']['id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? 0;
$guess = trim($_POST['guess'] ?? '');

if (!$room_id || !$guess) {
    echo json_encode(['ret_code' => -1, 'error' => '参数错误']);
    exit;
}

// 查询房间
$stmt = mysqli_prepare($conn, "SELECT user_id0, user_id1, current_word, description, round FROM tb_room WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $room_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$room = mysqli_fetch_assoc($result)) {
    echo json_encode(['ret_code' => -2, 'error' => '房间不存在']);
    exit;
}

// 根据轮数判断猜测者
$is_odd_round = ($room['round'] % 2 === 1);
$guesser_id = $is_odd_round ? $room['user_id1'] : $room['user_id0'];

// 验证是猜测者
if ($guesser_id != $user_id) {
    echo json_encode(['ret_code' => -3, 'error' => '只有猜测者可以提交猜测']);
    exit;
}

// 判断猜测是否正确（简单字符串匹配，忽略大小写和空格）
$correct_word = trim($room['current_word']);
$guess_normalized = mb_strtolower(preg_replace('/\s+/', '', $guess), 'UTF-8');
$word_normalized = mb_strtolower(preg_replace('/\s+/', '', $correct_word), 'UTF-8');

$is_correct = ($guess_normalized === $word_normalized);

echo json_encode([
    'ret_code' => 0,
    'is_correct' => $is_correct,
    'guess' => $guess,
    'correct_word' => $correct_word,
    'description' => $room['description'],
    'message' => $is_correct ? '恭喜猜对了！' : '猜错了，正确答案是：' . $correct_word
]);

mysqli_close($conn);