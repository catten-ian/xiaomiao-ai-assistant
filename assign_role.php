<?php
include "log.php";
session_start();
$log_user_ids = [8, 14];

function shouldLog($user_id) {
    global $log_user_ids;
    return in_array($user_id, $log_user_ids);
}

include '../config.inc';
header('Content-Type: application/json');

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;

$response = array(
    'status' => 'error',
    'message' => '参数无效',
    'role' => null
);

if ($user_id <= 0 || $room_id <= 0) {
    echo json_encode($response);
    exit;
}

try {
    $conn = new mysqli('localhost', $db_user, $db_password, $db_name, $db_port);
    
    if ($conn->connect_error) {
        throw new Exception('数据库连接失败');
    }
    
    // 检查用户是否在房间内，并获取当前轮数
    $check_sql = "SELECT user_id0, user_id1, round FROM tb_room WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('i', $room_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $room = $check_result->fetch_assoc();
    
    if (!$room || ($room['user_id0'] != $user_id && $room['user_id1'] != $user_id)) {
        throw new Exception('用户不在指定房间内');
    }
    
    // 根据轮数决定角色：奇数轮 user_id0 是描述者，偶数轮 user_id1 是描述者
    $current_round = $room['round'];
    $is_odd_round = ($current_round % 2 === 1);
    
    // 判断当前用户的角色
    if ($is_odd_round) {
        // 奇数轮：user_id0 = describer, user_id1 = guesser
        $assigned_role = ($room['user_id0'] == $user_id) ? 1 : 2;
    } else {
        // 偶数轮：user_id1 = describer, user_id0 = guesser
        $assigned_role = ($room['user_id1'] == $user_id) ? 1 : 2;
    }
    
    // 更新用户角色
    $update_sql = "UPDATE tb_user SET role = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('ii', $assigned_role, $user_id);
    $update_stmt->execute();
    
    // 保存到 SESSION
    $_SESSION['role'] = ($assigned_role == 1) ? 'describer' : 'guesser';
    $_SESSION['room']['id'] = $room_id;
    
    $response['status'] = 'success';
    $response['message'] = '角色分配成功';
    $response['role'] = ($assigned_role == 1) ? 'describer' : 'guesser';
    $response['round'] = $current_round;
    
    $check_stmt->close();
    $update_stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>