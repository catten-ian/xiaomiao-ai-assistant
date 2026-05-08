#!/bin/bash

API="https://www.catten.cyou"
ROOM_ID=14

echo "=== 测试第二轮游戏（角色交换） ==="
echo ""

# 1. 开始新一轮
echo "1. 开始新一轮..."
RESPONSE=$(curl -s -X POST "$API/start_new_round.php" -d "room_id=$ROOM_ID")
echo "开始新一轮响应: $RESPONSE"
NEW_ROUND=$(echo $RESPONSE | jq -r '.round')
echo "新轮数: $NEW_ROUND"
echo ""

# 2. 检查房间状态
echo "2. 检查房间状态..."
RESPONSE=$(curl -s "$API/check_room_status_v2.php?room_id=$ROOM_ID&user_id=21")
echo "房间状态: $RESPONSE"
echo ""

# 3. 分配角色（第二轮）
echo "3. 分配角色（第二轮）..."
RESPONSE=$(curl -s -X POST "$API/assign_role.php" -d "user_id=21&room_id=$ROOM_ID")
echo "玩家A角色: $RESPONSE"

RESPONSE=$(curl -s -X POST "$API/assign_role.php" -d "user_id=22&room_id=$ROOM_ID")
echo "玩家B角色: $RESPONSE"
echo ""

# 4. 获取新词语
echo "4. 获取新词语..."
RESPONSE=$(curl -s "$API/get_words.php")
WORD_ID=$(echo $RESPONSE | jq -r '.words[1].id')
WORD=$(echo $RESPONSE | jq -r '.words[1].word')
echo "选择词语: $WORD (ID: $WORD_ID)"
echo ""

# 5. 保存词语（玩家B现在是描述者）
echo "5. 保存词语..."
RESPONSE=$(curl -s -X POST "$API/save_word.php" -d "room_id=$ROOM_ID&word_id=$WORD_ID")
echo "保存词语响应: $RESPONSE"
echo ""

# 6. 检查词语是否保存
echo "6. 检查词语是否保存..."
RESPONSE=$(curl -s "$API/check_room_status_v2.php?room_id=$ROOM_ID&user_id=22")
echo "房间状态: $RESPONSE"
echo ""

# 7. 玩家A提交猜测
echo "7. 玩家A提交猜测..."
RESPONSE=$(curl -s -X POST "$API/submit_guess.php" -d "room_id=$ROOM_ID&guess=$WORD")
echo "猜测结果: $RESPONSE"
echo ""

echo "=== 第二轮测试完成 ==="
