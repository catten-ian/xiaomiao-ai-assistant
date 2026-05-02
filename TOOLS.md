# TOOLS.md - Local Notes

Skills define _how_ tools work. This file is for _your_ specifics — the stuff that's unique to your setup.

## What Goes Here

Things like:

- Camera names and locations
- SSH hosts and aliases
- Preferred voices for TTS
- Speaker/room names
- Device nicknames
- Anything environment-specific

## Examples

```markdown
### Cameras

- living-room → Main area, 180° wide angle
- front-door → Entrance, motion-triggered

### SSH

- home-server → 192.168.1.100, user: admin

### TTS

- Preferred voice: "Nova" (warm, slightly British)
- Default speaker: Kitchen HomePod
```

## Why Separate?

Skills are shared. Your setup is yours. Keeping them apart means you can update skills without losing your notes, and share skills without leaking your infrastructure.

---

## Nano Banana（图片生成，v3 API）
- **脚本**：`/workspace/nano_banana.py`（已配置）
- **API**：`https://api.v3.cm/v1beta/models/{model}:predict`
- **模型选择**：
  - `nano-banana-2-2k`：生物结构图（**默认首选**）
  - `nano-banana-2-4k`：需要高清晰度时
  - `nano-banana-2`：标准版
- **调用**：`python3 /workspace/nano_banana.py "<prompt>" <output.png> [resolution] [aspect_ratio]`
- **注意**：不要用 `nano-banana-pro`，用 `nano-banana-2`**

## Midjourney API
- **API**: `https://api.gpt.ge`
- **Key**: `sk-TMNZIXGjpnjiW9fX52Dd9e1272444eDd87105bD9B677037b`
- **提交**: `POST /mj/submit/imagine` body `{prompt, botType: 'MID_JOURNEY'}`
- **查询**: `GET /mj/task/{task_id}/fetch`
- **下载**: `GET /mj/image/{task_id}`
- **Upscale/Variation按钮**: `POST /mj/submit/action` body `{"customId": "MJ::JOB::upsample::1::...", "taskId": "父任务ID"}`
- **⚠️ Variation限制**: api.gpt.ge 的 `/mj/submit/action` 对 variation 返回500上游错误，旧任务（>几分钟）的按钮交互已失效；可用 `--cref` 参数替代 variation 功能
- **脚本**: `/workspace/skills/midjourney-api/mj_full.py` (提交→等待→下载)

## Tavily 搜索
- **API Key**: `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`
- **端点**: `POST https://api.tavily.com/search`
- **调用**: `{"query": "...", "api_key": "<KEY>", "max_results": 8, "days": 7}`
- **用途**: 实时搜索新闻（优于curl爬虫）

## v3.cm API（通用文字/图片/多模态）
- **Chat端点**: `https://api.v3.cm/v1/chat/completions`（注意是 `/v1/` 前缀）
- **Models端点**: `https://api.v3.cm/v1/models`（获取可用模型列表）
- **Key**: `sk-TMNZIXGjpnjiW9fX52Dd9e1272444eDd87105bD9B677037b`
- **多模态模型**: `qwen-vl-ocr`（文字识别）、`qwen-vl-max`（图文理解）、`qwen-vl-plus`
- **qwen-vl-ocr 用法**: base64 图片 + `model: "qwen-vl-ocr"`，图片需先缩小至 700px 宽避免超时
- **qwen3-vl 系列**: `qwen3-vl-8b-instruct` 等（更强大但可能更慢）
- **Embedding**: `bge-large-zh-v1.5`（中文向量）

## 代码仓库
- **Gitee**: https://gitee.com/cattend/chinese-character-processor/（用户名 catten，论文相关程序）

## 工具策略（Tool Strategy）

根据任务类型选择最优工具集：

| 任务类型 | 默认工具 | 复杂场景备选 |
|---------|---------|------------|
| 图片生成 | MiniMax 内置 image_synthesize | Midjourney API（via /workspace/skills/midjourney-api/mj_full.py） |
| OCR 文字识别 | images_understand(qwen-vl-ocr) | gpt-4o（复杂版面/多语言） |
| TTS 语音合成 | MiniMax 内置 synthesize_speech | tts-1（v3cm，情感更丰富） |
| STT 语音转写 | MiniMax 内置 audios_understand | whisper-1（v3cm，高精度） |

### 工具选择原则
- 简单任务用内置工具（更快、更便宜）
- 复杂任务（多语言、高精度要求）切换到对应备选方案
- Midjourney 用于高质量艺术图片生成（需要先查任务状态）

Add whatever helps you do your job. This is your cheat sheet.
