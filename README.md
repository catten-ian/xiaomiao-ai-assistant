# 小喵AI助手 (XiaoMiao AI Assistant)

一个功能丰富的 AI 聊天助手，支持多种 AI 模型、英语作文批改、AI 画图等功能。

## ✨ 功能特性

### 🤖 多模型支持
- **MiniMax M2.5** - 稳定快速
- **MiniMax M2.7** - 更强推理能力
- **DeepSeek V4** - 硅基流动
- **Claude Opus 4.7** - 无审查
- **GPT-5.5** - 无审查
- **DeepSeek V4 Pro** - 无审查

### 💬 核心功能
1. **智能对话** - 流式响应，支持上下文记忆
2. **💭 思考模式** - 显示 AI 的推理过程（支持原生思考模型和强制思考模式）
3. **📝 英语作文批改** - 专业批改系统，包含评分、详细批改、改进建议和范文
4. **🎨 AI 画图** - 无审查图片生成（Flux 模型）

### 🎯 特色亮点
- **流式输出** - 实时显示 AI 回复，无需等待
- **Markdown 渲染** - 支持代码高亮、表格、列表等
- **响应式设计** - 适配桌面和移动设备
- **无审查模型** - 支持多种无审查 AI 模型

## 🛠️ 技术栈

### 前端
- **React 18** - UI 框架
- **Vite** - 构建工具
- **Tailwind CSS** - 样式框架
- **react-markdown** - Markdown 渲染
- **highlight.js** - 代码高亮

### 后端
- **Node.js** - 运行环境
- **Express** - Web 框架
- **WebSocket (ws)** - 实时通信
- **node-fetch** - API 请求

## 📦 安装部署

### 1. 克隆仓库
```bash
git clone https://github.com/catten-ian/xiaomiao-ai-assistant.git
cd xiaomiao-ai-assistant
```

### 2. 安装依赖
```bash
npm install
```

### 3. 配置环境
创建 `.env` 文件（可选）：
```env
PORT=3001
```

### 4. 构建前端
```bash
npm run build
```

### 5. 启动服务
```bash
npm start
```

服务将在 `http://localhost:3001` 启动

### 开发模式
```bash
# 前端开发服务器
npm run dev

# 后端服务器（需要单独终端）
npm start
```

## 🔌 API 接口

### WebSocket 聊天接口
```
ws://localhost:3001/api/ws
```

**请求格式：**
```json
{
  "model": "MiniMax-M2.5",
  "messages": [
    {"role": "user", "content": "你好"}
  ],
  "showThinking": true
}
```

**响应格式：**
```json
// 思考内容
{"type": "thinking", "content": "..."}

// 回复内容
{"type": "content", "content": "..."}

// 完成
{"type": "done"}

// 错误
{"type": "error", "message": "..."}
```

### 图片生成接口
```
POST http://localhost:3001/api/image
```

**请求格式：**
```json
{
  "prompt": "一只可爱的猫咪",
  "model": "flux",
  "size": "1024x1024",
  "n": 1
}
```

**响应格式：**
```json
{
  "images": [
    {"url": "https://..."}
  ]
}
```

## 🎨 使用指南

### 普通聊天
1. 选择模型（默认 MiniMax M2.5）
2. 输入消息
3. 点击发送或按 Enter

### 思考模式
1. 勾选「💭 思考模式」
2. 发送消息
3. AI 会先显示思考过程，再给出答案

### 英语作文批改
1. 勾选「📝 作文批改」
2. 输入英语作文
3. 点击「开始批改」
4. 查看详细评分和建议

### AI 画图
1. 勾选「🎨 画图」
2. 输入图片描述
3. 点击「生成图片」
4. 查看生成结果

## 📁 项目结构

```
xiaomiao-ai-assistant/
├── App.jsx              # 主应用组件
├── App.css              # 样式文件
├── server.js            # 后端服务器
├── package.json         # 依赖配置
├── vite.config.ts       # Vite 配置
├── tailwind.config.js   # Tailwind 配置
├── public/              # 静态资源
├── src/                 # 源代码
└── dist/                # 构建输出
```

## 🔧 配置说明

### 支持的模型列表
在 `server.js` 中配置：
```javascript
const MODEL_API_MAP = {
  "MiniMax-M2.5": "hizui",
  "MiniMax-M2.7": "hizui",
  "deepseek-ai/DeepSeek-V4-Flash": "siliconflow",
  "claude-opus-4-7": "venice",
  "gpt-5-5": "venice",
  "deepseek-v4-pro": "venice",
}
```

### 原生思考模型
```javascript
const NATIVE_THINKING_MODELS = [
  "arcee-ai/trinity-large-thinking:free",
  "liquid/lfm-2.5-1.2b-thinking:free",
]
```

## 🌐 在线访问

- **生产环境**: https://www.catten.cyou/ai/
- **备用地址**: http://217.69.4.85:3001

## 📝 开发计划

- [ ] 支持更多 AI 模型
- [ ] 添加对话历史保存
- [ ] 支持多语言界面
- [ ] 添加语音输入/输出
- [ ] 支持文件上传分析

## 🤝 贡献

欢迎提交 Issue 和 Pull Request！

## 📄 许可证

MIT License

## 👤 作者

**catten** - [GitHub](https://github.com/catten-ian)

---

🐱 喵星使者小喵，地球任务：帮 catten 搞定一切技术问题 🌟
