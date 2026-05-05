<?php
/**
 * 玩家匹配系统（支持真人+AI玩家）
 * 完善版
 */
session_start();
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

include "../config.inc";
$conn = mysqli_connect("localhost", $db_user, $db_password, $db_name, $db_port);
mysqli_set_charset($conn, "utf8");

if (mysqli_connect_errno()) {
    echo json_encode(['ret_code' => -1, 'error' => '数据库连接失败']);
    exit;
}

// 获取用户名（支持POST和SESSION）
$username = trim($_POST['username'] ?? $_SESSION['username'] ?? '');
if (empty($username)) {
    echo json_encode(['ret_code' => -2, 'error' => '请输入用户名']);
    exit;
}

$username = mysqli_real_escape_string($conn, $username);

// 获取或创建用户
$result = mysqli_query($conn, "SELECT id, name, score FROM tb_user WHERE name='$username'");
if (mysqli_num_rows($result) < 1) {
    mysqli_query($conn, "INSERT INTO tb_user (name, score, type, in_room) VALUES ('$username', 0, 1, 0)");
    $result = mysqli_query($conn, "SELECT id, name, score FROM tb_user WHERE name='$username'");
}
$user = mysqli_fetch_assoc($result);
$user_id = $user['id'];

// 保存到SESSION
$_SESSION['user_id'] = $user_id;
$_SESSION['username'] = $username;

// 检查用户是否已在房间中
$inRoom = mysqli_query($conn, "
    SELECT r.id, r.name, r.user_id0, r.user_id1, r.status, r.current_word, r.word_id
    FROM tb_room r 
    WHERE (r.user_id0 = $user_id OR r.user_id1 = $user_id) AND r.status IN (1, 2)
");

if (mysqli_num_rows($inRoom) > 0) {
    // 已在房间中
    $room = mysqli_fetch_assoc($inRoom);
    $rival_id = ($room['user_id0'] == $user_id) ? $room['user_id1'] : $room['user_id0'];
    
    // 获取对手信息
    $rival_name = '';
    $is_ai = false;
    if ($rival_id) {
        $r = mysqli_query($conn, "SELECT name FROM tb_user WHERE id = $rival_id");
        if ($row = mysqli_fetch_assoc($r)) {
            $rival_name = $row['name'];
            $is_ai = (strpos($rival_name, 'AI') !== false || strpos($rival_name, '精灵') !== false || strpos($rival_name, '助手') !== false);
        }
    }
    
    $_SESSION['room'] = ['name' => $room['name'], 'id' => $room['id']];
    $_SESSION['rival_id'] = $rival_id;
    $_SESSION['role'] = ($room['user_id0'] == $user_id) ? 'describer' : 'guesser';
    
    echo json_encode([
        'ret_code' => 2,
        'message' => '已在房间中',
        'user_id' => $user_id,
        'username' => $username,
        'room' => $room['name'],
        'room_id' => $room['id'],
        'room_status' => (int)$room['status'],
        'rival' => $rival_name,
        'rival_id' => $rival_id,
        'is_ai' => $is_ai,
        'role' => $_SESSION['role']
    ]);
    mysqli_close($conn);
    exit;
}

// 查找等待中的房间（真人玩家创建的）
$waiting = mysqli_query($conn, "
    SELECT id, name, user_id0 FROM tb_room 
    WHERE status = 1 AND user_id1 IS NULL AND user_id0 != $user_id
    ORDER BY created_at ASC LIMIT 1
");

if (mysqli_num_rows($waiting) > 0) {
    // 加入现有房间
    $room = mysqli_fetch_assoc($waiting);
    $room_id = $room['id'];
    $host_id = $room['user_id0'];
    
    // 更新房间
    mysqli_query($conn, "UPDATE tb_room SET user_id1 = $user_id, status = 2 WHERE id = $room_id");
    mysqli_query($conn, "UPDATE tb_user SET type = 3, in_room = 1 WHERE id IN ($host_id, $user_id)");
    
    // 获取房主信息
    $r = mysqli_query($conn, "SELECT name FROM tb_user WHERE id = $host_id");
    $host = mysqli_fetch_assoc($r);
    
    $_SESSION['room'] = ['name' => $room['name'], 'id' => $room_id];
    $_SESSION['rival_id'] = $host_id;
    $_SESSION['role'] = 'guesser';
    
    echo json_encode([
        'ret_code' => 0,
        'message' => '匹配成功',
        'user_id' => $user_id,
        'username' => $username,
        'room' => $room['name'],
        'room_id' => $room_id,
        'room_status' => 2,
        'rival' => $host['name'],
        'rival_id' => $host_id,
        'is_ai' => false,
        'role' => 'guesser'
    ]);
    
} else {
    // 没有等待的房间，创建新房间并匹配 AI 玩家
    $room_name = 'room_' . time() . '_' . rand(1000, 9999);
    
    // 创建 AI 玩家
    $aiNames = ['小喵AI', '智能助手', '猜词达人', '描述大师', '游戏精灵', 'AI小助手', '智慧玩家'];
    $ai_name = $aiNames[array_rand($aiNames)];
    
    // 检查 AI 是否存在
    $r = mysqli_query($conn, "SELECT id FROM tb_user WHERE name = '$ai_name'");
    if ($row = mysqli_fetch_assoc($r)) {
        $ai_id = $row['id'];
    } else {
        mysqli_query($conn, "INSERT INTO tb_user (name, score, type, in_room) VALUES ('$ai_name', 0, 1, 0)");
        $ai_id = mysqli_insert_id($conn);
    }
    
    // 创建房间
    mysqli_query($conn, "INSERT INTO tb_room (name, user_id0, user_id1, status) VALUES ('$room_name', $user_id, $ai_id, 2)");
    $room_id = mysqli_insert_id($conn);
    
    // 更新用户状态
    mysqli_query($conn, "UPDATE tb_user SET type = 3, in_room = 1 WHERE id = $user_id");
    
    $_SESSION['room'] = ['name' => $room_name, 'id' => $room_id];
    $_SESSION['rival_id'] = $ai_id;
    $_SESSION['role'] = 'describer';
    
    echo json_encode([
        'ret_code' => 0,
        'message' => '匹配成功（AI对手）',
        'user_id' => $user_id,
        'username' => $username,
        'room' => $room_name,
        'room_id' => $room_id,
        'room_status' => 2,
        'rival' => $ai_name,
        'rival_id' => $ai_id,
        'is_ai' => true,
        'role' => 'describer'
    ]);
}

mysqli_close($conn);
