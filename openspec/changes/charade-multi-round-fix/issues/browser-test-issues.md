# 浏览器测试发现的问题

## 测试时间
2026-05-08 08:18-08:32 (UTC+8)

## 测试环境
- 浏览器：真实浏览器测试
- 服务器：217.69.4.85
- 数据库：charade (MariaDB)

## 发现的问题

### 问题1：玩家A没有调用assign_role.php
**现象：**
- 玩家A在exampleroom.php等待，room_status一直是1
- 玩家B加入后，room_status变成2
- 玩家A跳转到exampleroom2.php
- **但是玩家A没有调用assign_role.php**，直接跳转到guess.php

**控制台日志证据：**
```
玩家A的日志：
- 一直在exampleroom.php，返回room_status=1
- 玩家B加入后，返回room_status=2
- 跳转到exampleroom2.php
- 没有看到"[LOG] 请求服务器分配角色"的日志
- 直接跳转到guess.php

玩家B的日志：
- 加入房间后，返回room_status=2
- 跳转到exampleroom2.php
- 调用assign_role.php，返回role=guesser
- 跳转到start.php，然后到waiting.php，最后到guess.php
```

**数据库状态：**
```sql
SELECT id, name, user_id0, user_id1, status, round, current_word, word_id 
FROM tb_room WHERE id = 16;

结果：
id: 16
name: room_16
user_id0: 25 (玩家A)
user_id1: 26 (玩家B)
status: 2
round: 1
current_word: NULL
word_id: NULL

SELECT id, name, role FROM tb_user WHERE name LIKE "测试玩家%";
结果：
id: 25, name: 测试玩家A, role: 0  ← 错误！应该是1
id: 26, name: 测试玩家B, role: 2  ← 正确
```

### 问题2：两个玩家都在guess.php页面
**现象：**
- 玩家A和玩家B都在guess.php页面
- 但是词语还没选（current_word=NULL）
- 玩家A的角色是0（应该是1）

**影响：**
- 游戏无法正常进行
- 猜测者无法猜测（没有词语）
- 描述者无法描述（没有词语）

### 问题3：玩家A的标签页可能被关闭
**现象：**
- 玩家A在exampleroom.php等待时，标签页可能被关闭或跳转
- 导致exampleroom2.php的checkUserCount函数没有执行
- 玩家A没有调用assign_role.php

## 根本原因分析

### 原因1：exampleroom.php的跳转逻辑问题
- exampleroom.php调用paring.php检查房间状态
- 当room_status=2时，跳转到exampleroom2.php
- 但是exampleroom2.php的checkUserCount函数需要5秒后才执行
- 如果玩家A的标签页在这5秒内被关闭或跳转，就不会调用assign_role.php

### 原因2：没有强制角色分配
- assign_role.php只在exampleroom2.php的checkUserCount函数中调用
- 如果玩家A没有执行这个函数，就不会分配角色
- 导致玩家A的角色一直是0

### 原因3：guess.php没有角色验证
- guess.php没有检查用户是否有正确的角色
- 导致角色为0的用户也能进入guess.php

## 建议的解决方案

### 方案1：在exampleroom.php跳转前强制分配角色
- 在exampleroom.php跳转到exampleroom2.php之前，先调用assign_role.php
- 确保每个玩家都有正确的角色

### 方案2：在guess.php和choose.php中添加角色验证
- 检查用户的角色是否正确
- 如果角色不正确，重定向到exampleroom2.php重新分配角色

### 方案3：在start.php中强制分配角色
- 在start.php中检查用户是否有正确的角色
- 如果没有，调用assign_role.php分配角色

## 下一步测试计划

1. 修复上述问题后，重新测试第一轮游戏
2. 完成第一轮游戏后，测试"再来一轮"功能
3. 测试第二轮游戏（角色交换）
4. 测试第三轮游戏（再次角色交换）
5. 验证分数累积是否正确
