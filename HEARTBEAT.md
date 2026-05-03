# HEARTBEAT.md

# 心跳任务

## EMAIL_CHECK（来自MaxClaw cron，每10分钟一次）
**正确调用方式（2026-04-06 更新）：**
```python
import urllib.request, os, json
MATON_KEY = "t-t0b0DMZFccLTrFvHKar5Nk9-TqqhXukK2kSJDtJDworw00U8_d4QVd1oCqWBmkn1-sJrOh1yvNoGbh54rBcWF9OM4VmkB6G44"
account_id = "3127943000000008002"
folder_id = "1367000000000008014"  # Inbox
url = f"https://gateway.maton.ai/zoho-mail/api/accounts/{account_id}/messages/view?folderId={folder_id}&limit=5"
req = urllib.request.Request(url)
req.add_header('Authorization', f'Bearer {MATON_KEY}')
req.add_header('Content-Type', 'application/json')
resp = urllib.request.urlopen(req, timeout=10)
data = json.loads(resp.read())
# data["data"] 是邮件列表，检查是否有未读重要邮件
```
- ⚠️ 不要加 sortOrder 参数，会报 400 错误（EXTRA_PARAM_FOUND）
- 如有未读重要邮件，及时通知 catten
- 如无未完成 MJ/Suno 任务且无重要邮件，直接回复 HEARTBEAT_OK

## 其他定期检查（每30分钟心跳）
- Moltbook 重试脚本状态：检查 /var/www/charade/moltbook_run.log
- 服务器状态：检查 www.catten.cyou 是否可达

## 主动任务检查（每次心跳时执行）
检查以下待处理任务状态，如有未完成的可完成任务则立即处理并汇报：
1. MJ绘画任务：检查 /workspace 目录下是否有未完成的MJ任务日志或任务ID记录，如有则查询状态并下载
2. Suno音乐任务：检查 /workspace 目录下的 suno_task_ids.json，如有则查询状态
3. 邮件处理：按现有逻辑执行
4. **Charade 修护任务**（见下方详情，有空就推进）

---
## 🛠️ Charade 修护任务（catten.cyou/charade）

**优先级：** 中（有空就推进）
**仓库：** https://gitee.com/cattend/charade（已克隆到 /tmp/charade）

**目标：**
1. 用 Codex 修 charade 的 bug
2. 重新部署到 catten.cyou/charade
3. 浏览器测试验证

**TODO List：**
- [ ] 拉取最新 Gitee 代码确认版本
- [ ] SSH 到服务器排查报错原因（密码：6Cj.@H7QQYcgyq-E）
- [ ] 用 Codex 修 bug（调用 codex-skill）
- [ ] 重新部署到 /var/www/charade/
- [ ] 浏览器测试 catten.cyou/charade

## 上下文压缩（每次心跳时执行）
- 调用 `session_status` 检查当前上下文 token 使用量
- 如 token 使用超过 150k，执行智能压缩（不是简单裁剪）：
  1. 调用 `sessions_history` 获取最近对话内容
  2. **摘要提炼**：将旧对话内容压缩为结构化摘要，保留：
     - 未完成的任务及当前进展
     - 关键决策结论（用户偏好、配置变更等）
     - 重要上下文（研究参数、方案内容等）
  3. 将摘要追加写入当日 memory 文件（`/workspace/memory/YYYY-MM-DD.md`），格式：
     `## [时间戳] 会话摘要\n- 任务：...\n- 结论：...\n- 待跟进：...`
  4. 压缩后回复 HEARTBEAT_OK，不汇报压缩细节

## 每日自检任务（每天 23:00 UTC 执行一次）
1. **配置备份**：使用 `gateway` 工具获取配置并保存
   - 执行 `config.get` 获取原始配置
   - 将 raw 内容写入 `/workspace/backups/openclaw_YYYYMMDD.json`
   - 备份完成后，将当天记忆收获总结写入 `/workspace/memory/YYYY-MM-DD.md`
2. **当日记忆总结**（使用 self-improving-agent skill）：
   - 整理当天完成的主要工作、发现的新方法、踩的坑
   - 调用 self-improving-agent skill 记录学习内容
   - 如有新技能创建，记录到 FEATURE_REQUESTS.md
