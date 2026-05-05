<?php
/**
 * AI 玩家系统
 * 当没有真人玩家时，AI 自动参与游戏
 */

header('Content-Type: application/json');

// v3.cm API 配置
define('V3_API_KEY', 'sk-TMNZIXGjpnjiW9fX52Dd9e1272444eDd87105bD9B677037b');
define('V3_API_URL', 'https://api.v3.cm/v1/chat/completions');

// 数据库连接
include '../config.inc';
$conn = mysqli_connect('localhost', $db_user, $db_password, $db_name, $db_port);
mysqli_set_charset($conn, 'utf8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

/**
 * 调用 v3.cm API
 */
function callAI($messages, $temperature = 0.8) {
    $data = [
        'model' => 'claude-sonnet-4-6',
        'messages' => $messages,
        'temperature' => $temperature,
        'max_tokens' => 500
    ];
    
    $ch = curl_init(V3_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . V3_API_KEY,
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    return $result['choices'][0]['message']['content'] ?? '我猜不出来...';
}

/**
 * AI 描述词语
 */
function aiDescribe($word) {
    $prompt = "你正在玩'你画我猜'游戏，你是描述者。
你需要描述词语'{$word}'，让猜的人能猜出来。
规则：
1. 不能直接说出这个词或它的谐音
2. 用形象、生动的语言描述
3. 可以描述外观、特征、用途、习性等
4. 描述要简洁，2-3句话

请直接给出描述，不要加任何前缀：";

    return callAI([
        ['role' => 'user', 'content' => $prompt]
    ], 0.9);
}

/**
 * AI 猜测词语
 */
function aiGuess($description, $previousGuesses = []) {
    $history = '';
    if (!empty($previousGuesses)) {
        $history = "\n\n你之前猜过（都不对）：" . implode('、', $previousGuesses);
    }
    
    $prompt = "你正在玩'你画我猜'游戏，你是猜测者。
描述者说：'{$description}'
{$history}

请猜一个词。直接说出你猜的词，不要解释。只能猜一个词或短语。";

    return trim(callAI([
        ['role' => 'user', 'content' => $prompt]
    ], 0.7));
}

/**
 * 创建或获取 AI 玩家
 */
function getOrCreateAIPlayer($conn, $difficulty = 'medium') {
    $aiNames = [
        '小喵AI', '智能助手', '猜词达人', '描述大师', '游戏精灵',
        'AI小助手', '智慧玩家', '猜谜高手', '描述专家', '游戏伙伴'
    ];
    
    $name = $aiNames[array_rand($aiNames)];
    
    // 检查是否已存在
    $stmt = mysqli_prepare($conn, "SELECT id, name FROM tb_user WHERE name = ?");
    mysqli_stmt_bind_param($stmt, 's', $name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row;
    }
    
    // 创建新 AI 玩家
    $stmt = mysqli_prepare($conn, "INSERT INTO tb_user (name, score, type) VALUES (?, 0, 1)");
    mysqli_stmt_bind_param($stmt, 's', $name);
    mysqli_stmt_execute($stmt);
    
    return ['id' => mysqli_insert_id($conn), 'name' => $name];
}

// 路由处理
switch ($action) {
    case 'describe':
        $word = $_POST['word'] ?? $_GET['word'] ?? '';
        if (empty($word)) {
            echo json_encode(['error' => '缺少词语']);
            exit;
        }
        $description = aiDescribe($word);
        echo json_encode(['description' => $description]);
        break;
        
    case 'guess':
        $description = $_POST['description'] ?? $_GET['description'] ?? '';
        $previous = json_decode($_POST['previous'] ?? $_GET['previous'] ?? '[]', true);
        if (empty($description)) {
            echo json_encode(['error' => '缺少描述']);
            exit;
        }
        $guess = aiGuess($description, $previous);
        echo json_encode(['guess' => $guess]);
        break;
        
    case 'create':
        $difficulty = $_POST['difficulty'] ?? $_GET['difficulty'] ?? 'medium';
        $ai = getOrCreateAIPlayer($conn, $difficulty);
        echo json_encode(['success' => true, 'ai_player' => $ai]);
        break;
        
    default:
        echo json_encode(['error' => '未知操作', 'available_actions' => ['describe', 'guess', 'create']]);
}

mysqli_close($conn);
