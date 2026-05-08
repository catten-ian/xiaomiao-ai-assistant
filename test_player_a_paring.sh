#!/bin/bash

API="https://www.catten.cyou"
COOKIES_A="/tmp/cookies_player_a.txt"

echo "=== 玩家A paring.php测试 ==="

# 模拟玩家A的paring.php请求（使用玩家A的user_id）
curl -s -c "$COOKIES_A" -X POST "$API/exampleroom.php" \
  -d "username=测试玩家A&type=1&from_login=1" > /dev/null

# 获取玩家A的user_id
RESPONSE=$(curl -s -b "$COOKIES_A" "$API/paring.php")
echo "玩家A paring.php响应:"
echo "$RESPONSE" | jq .

USER_ID_A=$(echo "$RESPONSE" | jq -r '.user_id')
ROOM_STATUS=$(echo "$RESPONSE" | jq -r '.room_status')

echo ""
echo "玩家A ID: $USER_ID_A"
echo "房间状态: $ROOM_STATUS"
