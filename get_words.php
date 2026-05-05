<?php
/**
 * 获取随机词语（4个选项）
 */
session_start();
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

include "../config.inc";
$conn = mysqli_connect("localhost", $db_user, $db_password, $db_name, $db_port);
mysqli_set_charset($conn, "utf8");

// 获取词库总数
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM tb_words");
$row = mysqli_fetch_assoc($result);
$count = $row['count'];

// 生成4个不同的随机索引
$indexes = [];
while (count($indexes) < 4) {
    $idx = rand(0, $count - 1);
    if (!in_array($idx, $indexes)) {
        $indexes[] = $idx;
    }
}

// 获取4个随机词
$words = [];
foreach ($indexes as $idx) {
    $result = mysqli_query($conn, "SELECT id, word, type, difficulty FROM tb_words LIMIT $idx, 1");
    if ($row = mysqli_fetch_assoc($result)) {
        $words[] = [
            'id' => $row['id'],
            'word' => $row['word'],
            'type' => $row['type'],
            'difficulty' => $row['difficulty']
        ];
    }
}

echo json_encode([
    'ret_code' => 0,
    'words' => $words
]);

mysqli_close($conn);
