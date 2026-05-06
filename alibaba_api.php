<?php
/**
 * 阿里巴巴后台 API
 * 数据存储在 JSON 文件中
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

define('DATA_FILE', __DIR__ . '/data.json');
define('ADMIN_USER', 'catten');
define('ADMIN_PASS', 'catwin10');

// CDN 图片上传
function uploadToCDN($file) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://img.scdn.io/api/v1.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'image' => new CURLFile($file['tmp_name'], $file['type'], $file['name']),
        'outputFormat' => 'webp'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    return $data['url'] ?? null;
}

// 初始化数据文件
function initData() {
    if (!file_exists(DATA_FILE)) {
        $defaultData = [
            'products' => [
                ['id' => 1, 'category' => 'wenchuang', 'icon' => '🎨', 'name' => '老师立牌', 'price' => '6.6', 'desc' => '迪拜老钱风造型', 'image' => 'https://img.cdn1.vip/i/69fac3788a9dc_1778041720.webp'],
                ['id' => 2, 'category' => 'wenchuang', 'icon' => '🕯️', 'name' => '香薰蜡烛', 'price' => '10', 'desc' => '丝路香氛', 'image' => 'https://img.cdn1.vip/i/69fac3692841b_1778041705.webp'],
                ['id' => 3, 'category' => 'wenchuang', 'icon' => '🖼️', 'name' => '迪拜冰箱贴', 'price' => '10', 'desc' => '哈利法塔', 'image' => 'https://img.cdn1.vip/i/69fac3762d397_1778041718.webp'],
                ['id' => 4, 'category' => 'wenchuang', 'icon' => '🏺', 'name' => '沙漠许愿瓶', 'price' => '10', 'desc' => '装满星沙', 'image' => 'https://img.cdn1.vip/i/69fac36673805_1778041702.webp'],
                ['id' => 5, 'category' => 'wenchuang', 'icon' => '💡', 'name' => '迪拜琉璃灯', 'price' => '10', 'desc' => '千夜梦境', 'image' => 'https://img.cdn1.vip/i/69fac3729a7e3_1778041714.webp'],
                ['id' => 6, 'category' => 'food', 'icon' => '🌴', 'name' => '阿拉伯椰枣', 'price' => '', 'desc' => '沙漠甜蜜', 'image' => 'https://img.cdn1.vip/i/69fac370615f0_1778041712.webp'],
                ['id' => 7, 'category' => 'food', 'icon' => '🍫', 'name' => '特产巧克力', 'price' => '', 'desc' => '金箔点缀', 'image' => 'https://img.cdn1.vip/i/69fac36d2e007_1778041709.webp'],
                ['id' => 8, 'category' => 'food', 'icon' => '🥩', 'name' => '金箔牛排', 'price' => '', 'desc' => '尊贵体验', 'image' => 'https://img.cdn1.vip/i/69fac37ad9297_1778041722.webp'],
                ['id' => 9, 'category' => 'food', 'icon' => '🥤', 'name' => '骆驼奶茶', 'price' => '', 'desc' => '丝路风味', 'image' => 'https://img.cdn1.vip/i/69fac37e2aee2_1778041726.webp'],
            ],
            'team' => [
                ['id' => 1, 'role' => '丝路使者', 'name' => '陈默雷'],
                ['id' => 2, 'role' => '丝城小厮', 'name' => '周轩逸'],
                ['id' => 3, 'role' => '丝城小厮', 'name' => '王依辰'],
                ['id' => 4, 'role' => '丝城小厮', 'name' => '吕彦锦'],
            ],
            'activity' => [
                'title' => '沙海寻珠',
                'subtitle' => '珍珠鉴真小游戏',
                'rules' => [
                    [
                        'title' => '游戏规则',
                        'items' => ['每人限玩 1 次', '展示 6 颗珍珠（2真+4仿）', '在规定时间内找出真珍珠']
                    ],
                    [
                        'title' => '奖励规则',
                        'items' => ['找出 1 颗：赠送 1 颗小珠子', '找出 2 颗：赠送 5 颗珍珠珠', '全猜错：8 折串珠优惠']
                    ],
                    [
                        'title' => 'DIY 串珠区',
                        'items' => ['天然珍珠：3 元/颗', '赠送珠/普通珠：免费', '提供弹力线、工具']
                    ]
                ]
            ]
        ];
        file_put_contents(DATA_FILE, json_encode($defaultData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}

// 读取数据
function readData() {
    initData();
    return json_decode(file_get_contents(DATA_FILE), true);
}

// 保存数据
function saveData($data) {
    file_put_contents(DATA_FILE, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

// 检查登录
function checkAuth() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input['username'] === ADMIN_USER && $input['password'] === ADMIN_PASS) {
            $_SESSION['logged_in'] = true;
            echo json_encode(['success' => true, 'message' => '登录成功']);
        } else {
            echo json_encode(['success' => false, 'error' => '用户名或密码错误']);
        }
        break;
        
    case 'logout':
        session_destroy();
        echo json_encode(['success' => true]);
        break;
        
    case 'check_auth':
        echo json_encode(['success' => true, 'logged_in' => checkAuth()]);
        break;
        
    case 'get_all':
        $data = readData();
        echo json_encode([
            'success' => true, 
            'products' => $data['products'], 
            'team' => $data['team'],
            'activity' => $data['activity'] ?? null
        ]);
        break;
        
    case 'get_products':
        $data = readData();
        echo json_encode(['success' => true, 'products' => $data['products']]);
        break;
        
    case 'get_team':
        $data = readData();
        echo json_encode(['success' => true, 'team' => $data['team']]);
        break;
        
    case 'get_activity':
        $data = readData();
        echo json_encode(['success' => true, 'activity' => $data['activity'] ?? null]);
        break;
        
    case 'upload_image':
        if (!checkAuth()) {
            echo json_encode(['success' => false, 'error' => '未登录']);
            break;
        }
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => '请选择图片']);
            break;
        }
        $url = uploadToCDN($_FILES['image']);
        if ($url) {
            echo json_encode(['success' => true, 'url' => $url]);
        } else {
            echo json_encode(['success' => false, 'error' => '上传失败']);
        }
        break;
        
    case 'add_product':
        if (!checkAuth()) {
            echo json_encode(['success' => false, 'error' => '未登录']);
            break;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $data = readData();
        $input['id'] = max(array_column($data['products'], 'id')) + 1;
        $data['products'][] = $input;
        saveData($data);
        echo json_encode(['success' => true, 'id' => $input['id']]);
        break;
        
    case 'update_product':
        if (!checkAuth()) {
            echo json_encode(['success' => false, 'error' => '未登录']);
            break;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $data = readData();
        foreach ($data['products'] as &$product) {
            if ($product['id'] == $input['id']) {
                $product = array_merge($product, $input);
                break;
            }
        }
        saveData($data);
        echo json_encode(['success' => true]);
        break;
        
    case 'delete_product':
        if (!checkAuth()) {
            echo json_encode(['success' => false, 'error' => '未登录']);
            break;
        }
        $id = $_GET['id'] ?? 0;
        $data = readData();
        $data['products'] = array_filter($data['products'], fn($p) => $p['id'] != $id);
        $data['products'] = array_values($data['products']);
        saveData($data);
        echo json_encode(['success' => true]);
        break;
        
    case 'add_team':
        if (!checkAuth()) {
            echo json_encode(['success' => false, 'error' => '未登录']);
            break;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $data = readData();
        $input['id'] = max(array_column($data['team'], 'id')) + 1;
        $data['team'][] = $input;
        saveData($data);
        echo json_encode(['success' => true, 'id' => $input['id']]);
        break;
        
    case 'update_team':
        if (!checkAuth()) {
            echo json_encode(['success' => false, 'error' => '未登录']);
            break;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $data = readData();
        foreach ($data['team'] as &$member) {
            if ($member['id'] == $input['id']) {
                $member = array_merge($member, $input);
                break;
            }
        }
        saveData($data);
        echo json_encode(['success' => true]);
        break;
        
    case 'delete_team':
        if (!checkAuth()) {
            echo json_encode(['success' => false, 'error' => '未登录']);
            break;
        }
        $id = $_GET['id'] ?? 0;
        $data = readData();
        $data['team'] = array_filter($data['team'], fn($t) => $t['id'] != $id);
        $data['team'] = array_values($data['team']);
        saveData($data);
        echo json_encode(['success' => true]);
        break;
        
    case 'update_activity':
        if (!checkAuth()) {
            echo json_encode(['success' => false, 'error' => '未登录']);
            break;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $data = readData();
        $data['activity'] = $input;
        saveData($data);
        echo json_encode(['success' => true]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => '未知操作']);
}
