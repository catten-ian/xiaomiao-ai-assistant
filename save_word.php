<?php
/**
 * 保存选中的词到房间
 */
session_start();
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

include "../config.inc";
$conn = mysqli_connect("localhost", $db_user, $db_password, $db_name, $db_port);
mysqli_set_charset($conn, "utf8");

$room_id = intval($_POST['room_id'] ?? $_SESSION['room']['id'] ?? 0);
$word_id = intval($_POST['word_id'] ?? 0);

if (!$room_id || !$word_id) {
    echo json_encode(['ret_code' => -1, 'error' => '参数错误']);
    exit;
}

// 获取词语
$stmt = mysqli_prepare($conn, "SELECT word FROM tb_words WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $word_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$row = mysqli_fetch_assoc($result)) {
    echo json_encode(['ret_code' => -2, 'error' => '词语不存在']);
    exit;
}

$word = $row['word'];

// 更新房间
$stmt = mysqli_prepare($conn, "UPDATE tb_room SET current_word = ?, word_id = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'sii', $word, $word_id, $room_id);
mysqli_stmt_execute($stmt);

// 保存到SESSION
$_SESSION['selected_word'] = $word;
$_SESSION['word_id'] = $word_id;

echo json_encode([
    'ret_code' => 0,
    'message' => '词语已保存',
    'word' => $word,
    'word_id' => $word_id
]);

mysqli_close($conn);
