#!/bin/bash

API="https://www.catten.cyou"

echo "=== 测试 Charade 游戏流程 ==="
echo ""

# 1. 玩家A登录
echo "1. 玩家A登录..."
RESPONSE_A=$(curl -s -c cookies_a.txt -b cookies_a.txt -X POST "$API/login_only.php" -d "username=测试玩家A")
echo "玩家A响应: $RESPONSE_A"
USER_ID_A=$(echo $RESPONSE_A | jq -r '.user_id')
echo "玩家A ID: $USER_ID_A"
echo ""

# 2. 玩家B登录
echo "2. 玩家B登录..."
RESPONSE_B=$(curl -s -c cookies_b.txt -b cookies_b.txt -X POST "$API/login_only.php" -d "username=测试玩家B")
echo "玩家B响应: $RESPONSE_B"
USER_ID_B=$(echo $RESPONSE_B | jq -r '.user_id')
echo "玩家B ID: $USER_ID_B"
echo ""

# 3. 玩家A创建房间
echo "3. 玩家A创建房间..."
RESPONSE=$(curl -s -c cookies_a.txt -b cookies_a.txt -X POST "$API/create_room.php" -d "username=测试玩家A")
echo "创建房间响应: $RESPONSE"
ROOM_ID=$(echo $RESPONSE | jq -r '.room_id')
echo "房间ID: $ROOM_ID"
echo ""

# 4. 玩家B加入房间
echo "4. 玩家B加入房间..."
RESPONSE=$(curl -s -c cookies_b.txt -b cookies_b.txt -X POST "$API/paring_v2.php" -d "username=测试玩家B")
echo "加入房间响应: $RESPONSE"
echo ""

# 5. 检查房间状态
echo "5. 检查房间状态..."
RESPONSE=$(curl -s "$API/check_room_status_v2.php?room_id=$ROOM_ID&user_id=$USER_ID_A")
echo "房间状态: $RESPONSE"
echo ""

# 6. 分配角色
echo "6. 分配角色..."
RESPONSE=$(curl -s -c cookies_a.txt -b cookies_a.txt -X POST "$API/assign_role.php" -d "user_id=$USER_ID_A&room_id=$ROOM_ID")
echo "玩家A角色: $RESPONSE"

RESPONSE=$(curl -s -c cookies_b.txt -b cookies_b.txt -X POST "$API/assign_role.php" -d "user_id=$USER_ID_B&room_id=$ROOM_ID")
echo "玩家B角色: $RESPONSE"
echo ""

# 7. 获取词语列表
echo "7. 获取词语列表..."
RESPONSE=$(curl -s "$API/get_words.php")
echo "词语列表: $RESPONSE"
WORD_ID=$(echo $RESPONSE | jq -r '.words[0].id')
WORD=$(echo $RESPONSE | jq -r '.words[0].word')
echo "选择词语: $WORD (ID: $WORD_ID)"
echo ""

# 8. 保存词语
echo "8. 保存词语..."
RESPONSE=$(curl -s -c cookies_a.txt -b cookies_a.txt -X POST "$API/save_word.php" -d "room_id=$ROOM_ID&word_id=$WORD_ID")
echo "保存词语响应: $RESPONSE"
echo ""

# 9. 检查词语是否保存
echo "9. 检查词语是否保存..."
RESPONSE=$(curl -s "$API/check_room_status_v2.php?room_id=$ROOM_ID&user_id=$USER_ID_A")
echo "房间状态: $RESPONSE"
echo ""

# 10. 提交猜测
echo "10. 玩家B提交猜测..."
RESPONSE=$(curl -s -c cookies_b.txt -b cookies_b.txt -X POST "$API/submit_guess.php" -d "room_id=$ROOM_ID&guess=$WORD")
echo "猜测结果: $RESPONSE"
echo ""

echo "=== 测试完成 ==="
