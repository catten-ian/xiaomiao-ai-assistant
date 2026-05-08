# Multi-Round Game Specification

## Overview

多轮游戏流程规范，定义角色交换、轮数管理和房间重置的行为。

## Requirements

### REQ-001: 轮数管理
系统 SHALL 维护一个 `round` 字段来跟踪当前游戏轮数。

- 初始值：1
- 每次点击"再来一轮"按钮后递增
- 存储在 `tb_room.round` 字段

### REQ-002: 角色动态分配
系统 SHALL 根据当前轮数动态分配角色。

- **奇数轮（1, 3, 5...）**：user_id0 是描述者，user_id1 是猜测者
- **偶数轮（2, 4, 6...）**：user_id1 是描述者，user_id0 是猜测者

### REQ-003: 房间重置
系统 SHALL 在开始新一轮时重置房间状态。

重置操作包括：
- 清空 `current_word`
- 清空 `word_id`
- 清空 `description`
- 递增 `round`
- 保持 `status = 2`（游戏中）
- 保持 `user_id0` 和 `user_id1` 不变

### REQ-004: 多轮游戏流程
系统 SHALL 支持至少2轮完整游戏流程。

#### Scenario: 第一轮游戏
- GIVEN 两个玩家已匹配成功
- WHEN 游戏开始
- THEN user_id0 是描述者，user_id1 是猜测者
- AND 描述者选词并描述
- AND 猜测者猜测
- AND 游戏结束后显示"再来一轮"按钮

#### Scenario: 第二轮游戏（角色交换）
- GIVEN 第一轮游戏结束
- WHEN 两个玩家都点击"再来一轮"按钮
- THEN 角色交换（user_id1 变成描述者，user_id0 变成猜测者）
- AND round 递增为 2
- AND 开始新一轮游戏

#### Scenario: 单方点击"再来一轮"
- GIVEN 第一轮游戏结束
- WHEN 只有一个玩家点击"再来一轮"按钮
- THEN 等待另一个玩家点击
- AND 如果 60 秒内另一个玩家未点击，自动开始新游戏

### REQ-005: 分数累计
系统 SHALL 在多轮游戏中累计玩家分数。

- 每轮猜对加1分
- 分数存储在 `tb_user.score`
- 游戏结束时显示累计分数

## Data Model

### tb_room 表变更

```sql
ALTER TABLE tb_room ADD COLUMN description TEXT NULL AFTER current_word;
ALTER TABLE tb_room ADD COLUMN round INT DEFAULT 1;
```

## API Specification

### POST /start_new_round.php

开始新一轮游戏。

**Request:**
```
room_id: <int>
```

**Response:**
```json
{
  "status": "success",
  "message": "新一轮开始",
  "round": 2
}
```

**Behavior:**
1. 验证用户是房间成员
2. 递增 round
3. 清空 current_word, word_id, description
4. 返回新轮数
