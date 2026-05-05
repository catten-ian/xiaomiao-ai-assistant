<?php
/**
 * 玩家登录（不自动匹配）
 */
session_start();
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

include "../config.inc";
$conn = mysqli_connect("localhost", $db_user, $db_password, $db_name, $db_port);
mysqli_set_charset($conn, "utf8");

$username = trim($_POST['username'] ?? '');
if (empty($username)) {
    echo json_encode(['ret_code' => -2, 'error' => '请输入用户名']);
    exit;
}

$username = mysqli_real_escape_string($conn, $username);

// 获取或创建用户
$result = mysqli_query($conn, "SELECT id, name, score, type FROM tb_user WHERE name='$username'");
if (mysqli_num_rows($result) < 1) {
    mysqli_query($conn, "INSERT INTO tb_user (name, score, type, in_room) VALUES ('$username', 0, 1, 0)");
    $result = mysqli_query($conn, "SELECT id, name, score, type FROM tb_user WHERE name='$username'");
}
$user = mysqli_fetch_assoc($result);
$user_id = $user['id'];

// 保存到SESSION
$_SESSION['user_id'] = $user_id;
$_SESSION['username'] = $username;

// 检查用户是否已在房间中
$inRoom = mysqli_query($conn, "
    SELECT r.id, r.name, r.user_id0, r.user_id1, r.status
    FROM tb_room r 
    WHERE (r.user_id0 = $user_id OR r.user_id1 = $user_id) AND r.status IN (1, 2)
");

if (mysqli_num_rows($inRoom) > 0) {
    $room = mysqli_fetch_assoc($inRoom);
    $rival_id = ($room['user_id0'] == $user_id) ? $room['user_id1'] : $room['user_id0'];
    
    $rival_name = '';
    if ($rival_id) {
        $r = mysqli_query($conn, "SELECT name FROM tb_user WHERE id = $rival_id");
        if ($row = mysqli_fetch_assoc($r)) {
            $rival_name = $row['name'];
        }
    }
    
    $_SESSION['room'] = ['name' => $room['name'], 'id' => $room['id']];
    $_SESSION['rival_id'] = $rival_id;
    
    echo json_encode([
        'ret_code' => 0,
        'message' => '登录成功（已在房间）',
        'user_id' => $user_id,
        'username' => $username,
        'room' => $room['name'],
        'room_id' => $room['id'],
        'room_status' => (int)$room['status'],
        'rival' => $rival_name,
        'rival_id' => $rival_id,
        'in_room' => true
    ]);
} else {
    echo json_encode([
        'ret_code' => 0,
        'message' => '登录成功',
        'user_id' => $user_id,
        'username' => $username,
        'in_room' => false
    ]);
}

mysqli_close($conn);