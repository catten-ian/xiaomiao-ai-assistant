#!/bin/bash

API="https://www.catten.cyou"
COOKIES_B="/tmp/cookies_player_b.txt"

echo "=== 玩家B测试流程 ==="
echo ""

# 1. 玩家B登录
echo "1. 玩家B登录..."
curl -s -c "$COOKIES_B" -X POST "$API/exampleroom.php" \
  -d "username=测试玩家B&type=1&from_login=1" > /dev/null

# 获取玩家B的user_id
USER_ID_B=$(curl -s -b "$COOKIES_B" "$API/paring.php" | jq -r '.user_id')
echo "玩家B ID: $USER_ID_B"
echo ""

# 2. 玩家B加入房间
echo "2. 玩家B加入房间..."
RESPONSE=$(curl -s -b "$COOKIES_B" "$API/paring.php")
ROOM_ID=$(echo $RESPONSE | jq -r '.room_id')
ROOM_STATUS=$(echo $RESPONSE | jq -r '.room_status')
echo "房间ID: $ROOM_ID, 状态: $ROOM_STATUS"
echo ""

# 3. 玩家B分配角色
echo "3. 玩家B分配角色..."
RESPONSE=$(curl -s -b "$COOKIES_B" -X POST "$API/assign_role.php" \
  -d "user_id=$USER_ID_B&room_id=$ROOM_ID")
echo "角色分配响应: $RESPONSE"
ROLE_B=$(echo $RESPONSE | jq -r '.role')
echo "玩家B角色: $ROLE_B"
echo ""

# 4. 检查数据库状态
echo "4. 检查数据库状态..."
echo ""

echo "=== 测试完成 ==="
