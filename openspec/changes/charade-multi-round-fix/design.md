# Charade 多轮游戏修复 - 技术设计

## 架构概览

```
┌─────────────────────────────────────────────────────────────┐
│                        游戏流程                              │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  登录 → 等待房间 → 角色分配 → 选词/等待 → 描述/猜测 → 结束  │
│                                                             │
│                          ↓ "再来一轮"                       │
│                                                             │
│  角色交换 → 选词/等待 → 描述/猜测 → 结束                    │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

## 核心组件

### 1. 角色分配系统 (assign_role.php)

**职责**：根据轮数动态分配角色

**算法**：
```php
// 获取当前轮数
$current_round = $room['round'];
$is_odd_round = ($current_round % 2 === 1);

// 根据轮数和用户位置分配角色
if ($is_odd_round) {
    // 奇数轮：user_id0 = 描述者
    $assigned_role = ($room['user_id0'] == $user_id) ? 1 : 2;
} else {
    // 偶数轮：user_id1 = 描述者
    $assigned_role = ($room['user_id1'] == $user_id) ? 1 : 2;
}
```

**数据流**：
1. exampleroom2.php 通过 AJAX 调用
2. 查询 tb_room 获取 round, user_id0, user_id1
3. 计算角色并更新 tb_user.role
4. 返回角色信息给前端

### 2. 选词保存系统 (save_word_to_session.php)

**职责**：保存描述者选择的词语

**数据流**：
1. choose.php 通过 AJAX 调用
2. 接收 selected_word 参数
3. 查询 tb_words 获取 word_id
4. 更新 tb_room.current_word 和 word_id
5. 保存到 $_SESSION['selected_word']

**关键修复**：
```php
// 之前：只保存 word_id
// 现在：同时保存 current_word 和 word_id
$stmt = mysqli_prepare($conn, "UPDATE tb_room SET current_word = ?, word_id = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'sii', $selected_word, $word_id, $room_id);
```

### 3. 多轮游戏系统 (start_new_round.php)

**职责**：重置房间状态开始新一轮

**操作**：
```php
// 增加轮数
$new_round = $room['round'] + 1;

// 重置房间状态
UPDATE tb_room SET 
    current_word = NULL,
    word_id = NULL,
    description = NULL,
    round = ?,
    status = 2
WHERE id = ?
```

### 4. 描述系统 (send_description.php)

**职责**：描述者发送描述给猜测者

**数据流**：
1. 描述者在 describe.php 输入描述
2. 通过 AJAX 发送到 send_description.php
3. 保存到 tb_room.description
4. 猜测者通过轮询获取描述

### 5. 猜测验证系统 (submit_guess.php)

**职责**：验证猜测者提交的答案

**算法**：
```php
// 标准化比较（忽略大小写和空格）
$guess_normalized = mb_strtolower(preg_replace('/\s+/', '', $guess), 'UTF-8');
$word_normalized = mb_strtolower(preg_replace('/\s+/', '', $correct_word), 'UTF-8');

$is_correct = ($guess_normalized === $word_normalized);
```

## 数据库设计

### tb_room 表结构

```sql
CREATE TABLE tb_room (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    user_id0 INT,              -- 房主/第一个玩家
    user_id1 INT,              -- 第二个玩家
    current_word VARCHAR(100), -- 当前词语
    word_id INT,               -- 词语ID
    description TEXT,          -- 描述内容（新增）
    status TINYINT,            -- 0=等待, 1=满员, 2=游戏中
    round INT DEFAULT 1,       -- 当前轮数
    created_at DATETIME
);
```

### tb_user 表结构

```sql
CREATE TABLE tb_user (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    role TINYINT,  -- 0=未分配, 1=描述者, 2=猜测者
    score INT DEFAULT 0
);
```

## 页面流程

### 第一轮游戏

```
玩家A                          玩家B
  │                             │
  ├─ login.html                 ├─ login.html
  │  输入用户名                  │  输入用户名
  │                             │
  ├─ exampleroom.php            ├─ exampleroom.php
  │  创建房间                    │  加入房间
  │                             │
  └─ exampleroom2.php ←─────────┘
     等待匹配
     │
     ├─ assign_role.php (AJAX)
     │  玩家A: role=1 (描述者)
     │  玩家B: role=2 (猜测者)
     │
     ├─ start.php (5秒倒计时)
     │
     ├─ choose.php              ├─ waiting.php
     │  选择词语                 │  等待描述者选词
     │                          │
     ├─ describe.php            ├─ guess.php
     │  看到词语                 │  等待描述
     │  输入描述                 │  输入猜测
     │                          │
     └─ 等待猜测结果 ←───────────┘
        │
        ├─ right.php (猜对)
        │  或
        ├─ wrong.php (猜错)
        │
        └─ end.php
           显示分数
           "再来一轮"按钮
```

### 第二轮游戏（角色交换）

```
玩家A                          玩家B
  │                             │
  └─ end.php                    └─ end.php
     点击"再来一轮"               点击"再来一轮"
     │                             │
     └─ exampleroom2.php ←─────────┘
        │
        ├─ start_new_round.php
        │  round: 1 → 2
        │  清空词语、描述
        │
        ├─ assign_role.php
        │  玩家A: role=2 (猜测者) ← 角色交换
        │  玩家B: role=1 (描述者) ← 角色交换
        │
        ├─ waiting.php           ├─ choose.php
        │  等待选词               │  选择词语
        │                        │
        ├─ guess.php             ├─ describe.php
        │  输入猜测               │  输入描述
        │                        │
        └─ right.php / wrong.php ┘
           │
           └─ end.php
              显示累计分数
```

## 关键技术决策

### 1. 角色分配策略

**决策**：使用 round 字段动态计算角色

**原因**：
- 避免交换 user_id0 和 user_id1（可能导致外键问题）
- 简单明了的数学逻辑（奇偶轮）
- 易于扩展到更多轮数

### 2. 描述传递方式

**决策**：使用数据库轮询

**原因**：
- 简单可靠
- 不需要 WebSocket
- 兼容现有架构

**备选方案**：
- WebSocket 实时推送（更复杂）
- Server-Sent Events（需要额外配置）

### 3. 猜测验证方式

**决策**：字符串标准化比较

**原因**：
- 简单有效
- 容错性好（忽略空格、大小写）

**改进空间**：
- 添加同义词支持
- 添加模糊匹配（Levenshtein 距离）

## 测试策略

### 单元测试

1. **角色分配测试**
   - 测试奇数轮角色分配
   - 测试偶数轮角色分配
   - 测试边界情况（round=0, round=MAX）

2. **选词保存测试**
   - 测试词语保存到数据库
   - 测试词语保存到会话
   - 测试词语查询

3. **猜测验证测试**
   - 测试完全匹配
   - 测试忽略空格
   - 测试忽略大小写

### 集成测试

1. **完整游戏流程测试**
   - 第一轮游戏流程
   - 第二轮游戏流程（角色交换）
   - 多轮游戏流程

2. **边界情况测试**
   - 单方点击"再来一轮"
   - 网络中断恢复
   - 并发访问

### 端到端测试

使用真实浏览器测试完整流程：
1. 两个玩家登录
2. 匹配并开始游戏
3. 完成一轮游戏
4. 点击"再来一轮"
5. 验证角色交换
6. 完成第二轮游戏

## 风险与缓解

### 风险1：角色分配失败

**影响**：游戏无法正常进行

**缓解措施**：
- 添加详细日志记录
- 添加角色验证检查
- 提供手动重置功能

### 风险2：数据库并发问题

**影响**：多个玩家同时操作可能导致数据不一致

**缓解措施**：
- 使用事务处理
- 添加乐观锁
- 添加操作时间戳

### 风险3：浏览器兼容性

**影响**：某些浏览器可能不支持特定功能

**缓解措施**：
- 使用标准 HTML5 API
- 添加 polyfill
- 测试主流浏览器

## 部署计划

### 阶段1：数据库变更
```sql
ALTER TABLE tb_room ADD COLUMN description TEXT NULL AFTER current_word;
```

### 阶段2：代码部署
1. 备份现有文件
2. 上传新文件
3. 替换修改的文件

### 阶段3：验证测试
1. 测试登录流程
2. 测试游戏流程
3. 测试多轮游戏

### 回滚计划
1. 恢复备份文件
2. 删除新增字段（如需要）
```sql
ALTER TABLE tb_room DROP COLUMN description;
```
