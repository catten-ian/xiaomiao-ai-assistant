# LEARNINGS.md

## [LRN-20260520-001] Heartbeat Check
**Logged**: 2026-05-20T01:48:56+08:00
**Priority**: low
**Status**: pending
**Area**: heartbeat

### Summary
在本次心跳检查中，发现以下情况：
- 邮箱检查正常，没有未读的重要邮件。
- Moltbook 重试脚本日志文件不存在。
- 网站 `www.catten.cyou` 返回 404 错误。

### Details
- 需要进一步调查 Moltbook 重试脚本的状态和网站不可达的原因。

### Metadata
- Source: heartbeat
## [LRN-20260609-001] Heartbeat Status Check
**Logged**: 2026-06-09T04:25:00+08:00
**Priority**: medium
**Status**: pending
**Area**: infra

### Summary
心跳检查发现网站 www.catten.cyou 返回 404 错误，Hook daemon 脚本不存在

### Details
- 邮件检查 API 正常，收件箱为空
- Curator 服务正常运行（每30分钟触发）
- 网站 www.catten.cyou 返回 404（可能需要检查）
- Hook daemon 脚本路径 `/workspace/.scripts/hooks/daemon.sh` 不存在

### Metadata
- Source: heartbeat
- Related Files: /workspace/.learnings/LEARNINGS.md

## [LRN-20260618-001] 网站状态持续异常
**Logged**: 2026-06-18T08:06:00+08:00
**Priority**: medium
**Status**: pending
**Area**: infra

### Summary
网站 www.catten.cyou 持续返回 404 错误，已连续多日检测到此问题

### Details
- 邮件检查：正常，无重要未读邮件
- MJ/Suno 任务：无未完成任务
- 网站状态：www.catten.cyou 返回 404（响应时间约 0.5 秒）
- 此问题在 6月9日、6月10日已记录，建议用户检查网站配置

### Metadata
- Source: heartbeat
- Related Files: /workspace/.learnings/LEARNINGS.md
