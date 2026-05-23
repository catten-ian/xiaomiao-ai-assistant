# Charade 2 项目完成报告

## 项目概述
Charade 2 是一个基于 Web 的多人在线"你说我猜"游戏，部署在服务器 217.69.4.85 上。

## 技术栈
- **前端**：HTML/CSS/JavaScript（纯原生，无框架）
- **后端**：PHP 7.4+（RESTful API）
- **数据库**：MariaDB 10.5
- **Web服务器**：Caddy 2.x（自动 HTTPS）
- **部署地址**：https://www.catten.cyou/charade2/

## 已完成功能

### 1. 前端页面

#### index.html（首页）
- 创建房间按钮
- 加入房间功能（输入6位房间码）
- 响应式设计，支持移动端
- 渐变紫色主题

#### room.html（游戏房间）
- **顶部栏**：显示房间码、状态、离开按钮
- **左侧**：玩家列表（昵称、分数、房主标识）
- **中间**：游戏区域
  - 等待中：显示房间码分享提示
  - 游戏中：描述者/猜测者两种视角
  - 游戏结束：最终排名展示
- **右侧**：实时聊天面板
- 每2秒自动轮询更新状态

### 2. 后端 API

| API | 功能 | 状态 |
|-----|------|------|
| create_room.php | 创建房间，生成唯一房间码 | ✅ |
| join_room.php | 加入房间，返回玩家信息 | ✅ |
| get_room_status.php | 轮询房间状态、玩家列表、聊天记录 | ✅ |
| start_game.php | 房主开始游戏，随机选词和描述者 | ✅ |
| send_description.php | 描述者发送描述（检测是否包含词语） | ✅ |
| submit_guess.php | 猜测者提交答案，答对加分并自动下一轮 | ✅ |
| send_chat.php | 发送聊天消息 | ✅ |

### 3. 数据库设计

#### rooms 表（房间）
```sql
id VARCHAR(32) PRIMARY KEY
room_code VARCHAR(6) UNIQUE
host_id VARCHAR(32)
status ENUM('waiting', 'playing', 'finished')
current_round INT
max_rounds INT (默认5)
current_word VARCHAR(100)
describer_id VARCHAR(32)
current_description TEXT
created_at, updated_at DATETIME
```

#### players 表（玩家）
```sql
id VARCHAR(32) PRIMARY KEY
room_id VARCHAR(32)
nickname VARCHAR(50)
is_host BOOLEAN
score INT (默认0)
status ENUM('online', 'offline', 'playing')
last_heartbeat, joined_at DATETIME
```

#### words 表（词库）
```sql
id INT AUTO_INCREMENT PRIMARY KEY
word VARCHAR(100)
category VARCHAR(50)
difficulty ENUM('easy', 'medium', 'hard')
used_count INT
created_at DATETIME
```
- 当前词库：68个词语（动物、成语、职业等）

#### chat_messages 表（聊天消息）
```sql
id INT AUTO_INCREMENT PRIMARY KEY
room_id VARCHAR(32)
player_id VARCHAR(32)
message TEXT
type ENUM('chat', 'system', 'guess')
created_at DATETIME
```

#### game_rounds 表（游戏轮次记录）
```sql
id INT AUTO_INCREMENT PRIMARY KEY
room_id VARCHAR(32)
round_number INT
word VARCHAR(100)
describer_id VARCHAR(32)
description TEXT
guessers LONGTEXT (JSON)
correct_guessers LONGTEXT (JSON)
started_at, ended_at DATETIME
```

### 4. 游戏流程

#### 完整流程
1. 玩家A创建房间 → 获得6位房间码（如：UGHCL8）
2. 玩家B输入房间码加入房间
3. 房主点击"开始游戏"（至少2人）
4. 系统随机选择：
   - 描述者（第1轮随机，之后轮流）
   - 词语（从词库随机）
5. 描述者看到词语，输入描述（不能包含词语本身）
6. 猜测者看到描述，提交答案
7. 答对：
   - 猜测者+10分
   - 系统消息通知
   - 自动进入下一轮（轮换描述者）
8. 5轮后游戏结束
9. 显示最终排名（金/银/铜牌）

#### 轮换机制
- 描述者每轮轮换（排除当前描述者）
- 新词语每轮随机选择
- 所有玩家都能参与描述和猜测

## 测试结果

### 功能测试（2026-05-23）

#### 测试用例1：完整游戏流程
- **房间码**：UGHCL8
- **玩家**：房主（创建）、玩家2（加入）
- **轮次**：5轮
- **结果**：✅ 全部通过

| 轮次 | 词语 | 描述者 | 结果 |
|------|------|--------|------|
| 1 | 画家 | 房主 | 玩家2猜对 +10分 |
| 2 | 西瓜 | 玩家2 | 房主猜对 +10分 |
| 3 | 掩耳盗铃 | 房主 | 玩家2猜对 +10分 |
| 4 | 刻舟求剑 | 玩家2 | 房主猜对 +10分 |
| 5 | 程序员 | 房主 | 玩家2猜对 +10分 |

**最终排名**：
1. 玩家2（30分）🥇
2. 房主（20分）🥈

#### API 响应时间
- create_room：~200ms
- join_room：~150ms
- get_room_status：~100ms（轮询）
- send_description：~120ms
- submit_guess：~180ms

## 已知限制

### 当前版本限制
1. **词库规模**：68个词（可扩展）
2. **房间容量**：上限8人（可调整）
3. **无房间密码**：任何人知道房间码即可加入
4. **无断线重连**：玩家离开后需重新加入
5. **无AI参与**：只能多人游戏
6. **无语音/视频**：纯文字交流

### 技术限制
1. **轮询机制**：每2秒轮询一次，可能产生延迟
2. **无WebSocket**：实时性受限
3. **无持久化登录**：sessionStorage存储用户信息

## 扩展建议

### 短期优化（1-2天）
1. **扩展词库**：添加更多类别（电影、书籍、地点等）
2. **难度分级**：使用现有的 difficulty 字段
3. **房间密码**：增加房间安全性
4. **超时机制**：描述/猜测超时自动跳过

### 中期优化（1周）
1. **WebSocket实时通信**：替代轮询，提升实时性
2. **AI玩家**：单人模式，AI参与猜测
3. **语音描述**：支持语音输入
4. **游戏统计**：记录历史战绩

### 长期优化（1月+）
1. **用户系统**：注册登录、好友系统
2. **排行榜**：全球/好友排行
3. **自定义词库**：玩家创建词库
4. **多语言支持**：英文、日文等

## 部署信息

### 服务器配置
- **IP**：217.69.4.85
- **系统**：Debian 10
- **Web服务器**：Caddy 2.x
- **PHP版本**：7.4
- **数据库**：MariaDB 10.5

### Caddy 配置
```
redir /charade2 /charade2/ 308
handle_path /charade2/* {
    root * /var/www/charade2
    try_files {path} /index.html
    @is_php path *.php
    php_fastcgi @is_php 127.0.0.1:9074
    file_server
    encode gzip zstd
}
```

### 数据库账号
- **数据库名**：charade2
- **用户名**：charade
- **密码**：Charade@2026!

## 项目文件结构
```
/var/www/charade2/
├── index.html          # 首页
├── room.html           # 游戏房间页面
├── api/
│   ├── config.php      # 数据库配置
│   ├── create_room.php
│   ├── join_room.php
│   ├── get_room_status.php
│   ├── start_game.php
│   ├── send_description.php
│   ├── submit_guess.php
│   └── send_chat.php
├── database.sql        # 数据库初始化脚本
└── README.md           # 项目说明
```

## 总结

Charade 2 项目已完成核心功能开发，包括：
- ✅ 创建/加入房间
- ✅ 实时轮询更新
- ✅ 游戏流程（描述/猜测/得分）
- ✅ 自动轮换和下一轮
- ✅ 游戏结束排名
- ✅ 实时聊天

项目已通过完整游戏流程测试，所有功能正常运行。

---

**开发时间**：2026-05-17 ~ 2026-05-23
**当前版本**：v1.0
**状态**：生产环境运行中