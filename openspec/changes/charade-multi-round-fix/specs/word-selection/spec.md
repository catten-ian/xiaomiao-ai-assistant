# Word Selection Specification

## Overview

选词保存规范，定义描述者选择词语后如何保存到数据库和会话。

## Requirements

### REQ-001: 词语保存完整性
系统 SHALL 同时保存词语文本和词语ID。

- `current_word`: 词语文本（如"医院"）
- `word_id`: 词语在 tb_words 表中的ID

### REQ-002: 词语保存时机
系统 SHALL 在描述者点击词语卡片时立即保存。

#### Scenario: 描述者选择词语
- GIVEN 描述者在 choose.php 页面
- WHEN 点击一个词语卡片
- THEN 发送 POST 请求到 save_word_to_session.php
- AND 词语保存到 tb_room.current_word 和 tb_room.word_id
- AND 词语保存到 $_SESSION['selected_word']

### REQ-003: 词语查询
系统 SHALL 支持从数据库或会话中获取当前词语。

#### Scenario: 从会话获取词语
- GIVEN $_SESSION['selected_word'] 存在
- WHEN describe.php 加载
- THEN 直接使用会话中的词语

#### Scenario: 从数据库获取词语
- GIVEN $_SESSION['selected_word'] 不存在
- AND tb_room.current_word 存在
- WHEN describe.php 加载
- THEN 从数据库查询词语
- AND 保存到会话

### REQ-004: 词语可见性
系统 SHALL 根据角色限制词语可见性。

- **描述者**：可以看到词语文本
- **猜测者**：不能看到词语文本

## API Specification

### POST /save_word_to_session.php

保存选中的词语。

**Request:**
```
selected_word: <string>
```

**Response:**
```json
{
  "status": "success"
}
```

**Behavior:**
1. 保存词语到 $_SESSION['selected_word']
2. 查询词语在 tb_words 表中的ID
3. 更新 tb_room.current_word 和 tb_room.word_id
4. 返回成功响应

## Implementation Notes

### 已修复的问题
1. ✅ 现在同时保存 current_word 和 word_id
2. ✅ 优先使用 room_id 进行数据库更新

### 数据库变更
```sql
-- 已执行
-- ALTER TABLE tb_room ADD COLUMN description TEXT NULL AFTER current_word;
```

### 测试验证
```sql
-- 验证词语已保存
SELECT id, current_word, word_id FROM tb_room WHERE id = 13;
-- 结果: current_word = '医院', word_id = 32
```
