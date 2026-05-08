# Role Assignment Specification

## Overview

角色分配规范，定义如何根据轮数动态分配描述者和猜测者角色。

## Requirements

### REQ-001: 角色定义
系统 SHALL 使用以下角色定义：

- **描述者（describer）**：role = 1
- **猜测者（guesser）**：role = 2

### REQ-002: 角色分配规则
系统 SHALL 根据轮数和用户在房间中的位置分配角色。

#### Scenario: 奇数轮角色分配
- GIVEN round 为奇数（1, 3, 5...）
- WHEN 请求角色分配
- THEN user_id0 获得 role = 1（描述者）
- AND user_id1 获得 role = 2（猜测者）

#### Scenario: 偶数轮角色分配
- GIVEN round 为偶数（2, 4, 6...）
- WHEN 请求角色分配
- THEN user_id1 获得 role = 1（描述者）
- AND user_id0 获得 role = 2（猜测者）

### REQ-003: 角色持久化
系统 SHALL 将分配的角色持久化到数据库。

- 更新 `tb_user.role` 字段
- 保存到 `$_SESSION['role']`

### REQ-004: 角色验证
系统 SHALL 在游戏页面加载时验证用户角色。

#### Scenario: 描述者访问选词页面
- GIVEN 用户角色为描述者（role = 1）
- WHEN 用户访问 choose.php
- THEN 显示选词界面

#### Scenario: 猜测者访问选词页面
- GIVEN 用户角色为猜测者（role = 2）
- WHEN 用户访问 choose.php
- THEN 重定向到 waiting.php 或 guess.php

### REQ-005: 角色分配时机
系统 SHALL 在以下时机分配角色：

1. 玩家进入 exampleroom2.php 时（通过 AJAX 调用 assign_role.php）
2. 点击"再来一轮"按钮后重新进入游戏时

## API Specification

### POST /assign_role.php

分配用户角色。

**Request:**
```
user_id: <int>
room_id: <int>
```

**Response:**
```json
{
  "status": "success",
  "message": "角色分配成功",
  "role": "describer" | "guesser",
  "round": 1
}
```

**Behavior:**
1. 查询房间的 user_id0, user_id1, round
2. 验证用户是房间成员
3. 根据 round 和用户位置计算角色
4. 更新 tb_user.role
5. 返回角色信息

## Implementation Notes

### 关键代码位置
- `/var/www/charade/assign_role.php` - 角色分配逻辑
- `/var/www/charade/exampleroom2.php` - 调用角色分配
- `/var/www/charade/start.php` - 根据角色跳转

### 已修复的问题
1. ✅ 角色分配现在根据 round 字段动态计算
2. ✅ 返回值包含 round 信息
3. ✅ 角色正确保存到数据库和 SESSION
