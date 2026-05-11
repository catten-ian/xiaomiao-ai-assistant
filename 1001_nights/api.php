<?php
/**
 * 一千零一夜故事生成 API
 * 使用 v3.cm API 生成《一千零一夜》风格的故事
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// API 配置
define('V3_API_URL', 'https://api.v3.cm/v1/chat/completions');
define('V3_API_KEY', 'sk-TMNZIXGjpnjiW9fX52Dd9e1272444eDd87105bD9B677037b');

// 接收 POST 数据
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => '无效的请求']);
    exit;
}

$night = intval($input['night'] ?? 1);
$keyword = trim($input['keyword'] ?? '');

if (empty($keyword)) {
    echo json_encode(['success' => false, 'error' => '请提供关键词']);
    exit;
}

// 生成故事提示词
function buildPrompt($night, $keyword) {
    if ($night === 1) {
        return "你是《一千零一夜》中的山鲁佐德，正在给国王讲故事以延续生命。
请用第一人称讲述一个引人入胜的东方故事开头。

要求：
1. 故事要有悬念和神秘感
2. 语言要优美，有古典韵味
3. 在关键时刻停下，制造悬念
4. 故事要包含关键词：{$keyword}
5. 故事长度约150-200字
6. 结尾要有\"欲知后事如何，请听下回分解\"的感觉

请直接输出故事内容，不要有任何解释或说明。";
    } else {
        return "你是《一千零一夜》中的山鲁佐德，正在继续第{$night}夜的故事。
上一夜的故事在关键时刻停下了，现在需要继续。

关键词：{$keyword}

要求：
1. 继续故事，但要有新的转折
2. 语言优美，有古典韵味
3. 再次在关键时刻停下，制造新的悬念
4. 故事长度约150-200字
5. 结尾要有\"欲知后事如何，请听下回分解\"的感觉

请直接输出故事内容，不要有任何解释或说明。";
    }
}

// 调用 v3.cm API
function generateStory($prompt) {
    $data = [
        'model' => 'gpt-4o-mini', // 使用快速模型
        'messages' => [
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.8, // 较高温度，更有创意
        'max_tokens' => 500
    ];
    
    $ch = curl_init(V3_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . V3_API_KEY
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => 'API调用失败: ' . $error];
    }
    
    if ($httpCode !== 200) {
        return ['success' => false, 'error' => 'API返回错误: HTTP ' . $httpCode];
    }
    
    $result = json_decode($response, true);
    
    if (!$result || !isset($result['choices'][0]['message']['content'])) {
        return ['success' => false, 'error' => 'API返回格式错误'];
    }
    
    $story = $result['choices'][0]['message']['content'];
    
    // 格式化故事（添加段落）
    $story = formatStory($story);
    
    return ['success' => true, 'story' => $story];
}

// 格式化故事
function formatStory($story) {
    // 移除可能的前缀说明
    $story = preg_replace('/^(故事：|山鲁佐德说：|我开始讲述：)/', '', $story);
    
    // 添加段落标签
    $paragraphs = preg_split('/\n+/', trim($story));
    $formatted = '';
    
    foreach ($paragraphs as $p) {
        if (trim($p)) {
            $formatted .= '<p>' . trim($p) . '</p>';
        }
    }
    
    return $formatted;
}

// 主逻辑
$prompt = buildPrompt($night, $keyword);
$result = generateStory($prompt);

echo json_encode($result);
