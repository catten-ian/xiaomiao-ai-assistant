# Charade 多轮游戏修复方案

## Why

Charade 游戏目前存在以下核心问题：

1. **角色分配不一致**：玩家A（房主）的角色在数据库中显示为0，而不是预期的1（描述者），导致两个玩家都跳转到猜测页面
2. **选词保存不完整**：`save_word_to_session.php` 只保存了 `word_id`，没有保存 `current_word`，导致描述者看不到词语
3. **多轮游戏流程不清晰**：现有的"再来一轮"功能存在，但角色交换逻辑不明确，需要验证是否正确工作

这些问题导致游戏无法正常进行完整的多轮流程测试。

## What Changes

### 修复项
- **修复角色分配逻辑**：`assign_role.php` 需要根据 `round` 字段正确分配角色（奇数轮：user_id0=描述者，偶数轮：user_id1=描述者）
- **修复选词保存**：`save_word_to_session.php` 需要同时保存 `current_word` 和 `word_id` 到数据库
- **添加数据库字段**：`tb_room` 表需要添加 `description` 字段存储描述内容
- **验证多轮流程**：确保"再来一轮"按钮能正确触发角色交换并开始新一轮

### 新增功能
- **新增 API**：`start_new_round.php` 用于增加轮数并重置房间状态
- **新增 API**：`send_description.php` 用于描述者发送描述
- **新增 API**：`submit_guess.php` 用于猜测者提交猜测并判断正确与否

## Capabilities

### New Capabilities
- `multi-round-game`: 多轮游戏流程，包括角色交换、轮数管理、房间重置
- `description-system`: 描述系统，描述者发送描述、猜测者查看描述
- `guess-validation`: 猜测验证系统，判断猜测是否正确并返回结果

### Modified Capabilities
- `role-assignment`: 角色分配逻辑修改，从固定分配改为根据轮数动态分配
- `word-selection`: 选词保存逻辑修改，同时保存词语文本和ID

## Impact

### 受影响的文件
- `/var/www/charade/assign_role.php` - 角色分配逻辑
- `/var/www/charade/save_word_to_session.php` - 选词保存逻辑
- `/var/www/charade/check_room_status_v2.php` - 房间状态查询（需要返回 description 字段）
- `/var/www/charade/exampleroom2.php` - 等待房间页面（需要正确传递 room_id）

### 新增的文件
- `/var/www/charade/start_new_round.php` - 开始新一轮
- `/var/www/charade/send_description.php` - 发送描述
- `/var/www/charade/submit_guess.php` - 提交猜测

### 数据库变更
- `tb_room` 表添加 `description` TEXT 字段

### API 变更
- `assign_role.php` 返回值新增 `round` 字段
- `check_room_status_v2.php` 返回值新增 `description` 和 `round` 字段

## 风险评估

### 低风险
- 选词保存修复：影响范围小，逻辑简单
- 数据库字段添加：不影响现有数据

### 中风险
- 角色分配逻辑修改：可能影响现有游戏流程，需要充分测试
- 多轮游戏流程：涉及多个页面跳转，需要端到端测试

### 回滚计划
- 保留原始文件备份
- 数据库字段添加可逆（可删除字段）
- 新增 API 文件可直接删除
