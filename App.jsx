import { useState, useRef, useEffect } from 'react'
import './App.css'

const MODELS = [
  { id: 'MiniMax-M2.5', name: 'MiniMax M2.5', desc: '稳定快速', api: 'hizui' },
  { id: 'MiniMax-M2.7', name: 'MiniMax M2.7', desc: '更强推理', api: 'hizui' },
  { id: 'deepseek-ai/DeepSeek-V4-Flash', name: 'DeepSeek V4', desc: '硅基流动', api: 'siliconflow' },
  { id: 'claude-opus-4-7', name: 'Claude Opus 4.7', desc: '无审查', api: 'venice' },
  { id: 'gpt-5-5', name: 'GPT-5.5', desc: '无审查', api: 'venice' },
  { id: 'deepseek-v4-pro', name: 'DeepSeek V4 Pro', desc: '无审查', api: 'venice' },
]

// 批改系统提示词
const GRADING_PROMPT = `你是一位专业的英语作文批改老师。请按照以下标准批改学生的英语作文：

## 评分标准（总分25分）
1. **内容与思想** (5分)：观点是否明确、论据是否充分、逻辑是否连贯
2. **语言表达** (10分)：词汇丰富度、句式多样性、语法准确性
3. **篇章结构** (5分)：段落组织、过渡衔接、开头结尾
4. **格式规范** (5分)：拼写、标点、格式要求

## 批改要求
- 用中文批改，指出具体问题并给出改进建议
- 标注语法错误、拼写错误、表达不当之处
- 给出每项得分和总分
- 提供一篇修改后的范文

请按以下格式输出：

### 📊 评分详情
- 内容与思想：X/5分
- 语言表达：X/10分
- 篇章结构：X/5分
- 格式规范：X/5分
- **总分：X/25分**

### ✍️ 详细批改
[逐句批改，标注问题]

### 💡 改进建议
[总体建议]

### 📝 修改范文
[提供修改后的版本]`

function App() {
  const [messages, setMessages] = useState([])
  const [input, setInput] = useState('')
  const [loading, setLoading] = useState(false)
  const [model, setModel] = useState('MiniMax-M2.5')
  const [showModels, setShowModels] = useState(false)
  const [thinking, setThinking] = useState(false) // 思考模式开关
  const [showGrading, setShowGrading] = useState(false) // 批改模式开关
  const [showImageGen, setShowImageGen] = useState(false) // 画图模式开关
  const [essay, setEssay] = useState('') // 待批改的作文
  const [gradingResult, setGradingResult] = useState(null) // 批改结果
  const [imagePrompt, setImagePrompt] = useState('') // 画图提示词
  const [generatedImage, setGeneratedImage] = useState(null) // 生成的图片
  const messagesEndRef = useRef(null)

  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' })
  }, [messages])

  const send = async () => {
    if (!input.trim() || loading) return
    const userMsg = { role: 'user', content: input }
    setMessages(prev => [...prev, userMsg])
    setInput('')
    setLoading(true)

    try {
      const res = await fetch('/ai/api/v1/chat/completions', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          model,
          messages: [...messages, userMsg],
          stream: false,
          thinking, // 传递思考模式参数
        }),
      })
      const data = await res.json()
      
      // 处理思考模式的响应
      let content = data.choices?.[0]?.message?.content || '（无回复）'
      
      // 如果有思考内容，显示出来
      if (thinking) {
        // 处理 <think> 标签
        const thinkMatch = content.match(/<think>([\s\S]*?)<\/think>/);
        if (thinkMatch) {
          const reasoning = thinkMatch[1].trim();
          content = content.replace(/<think>[\s\S]*?<\/think>/, '').trim();
          content = `<details><summary>💭 思考过程</summary><pre>${reasoning}</pre></details>\n\n${content}`;
        }
        // 也支持 reasoning_content 字段
        else if (data.choices?.[0]?.message?.reasoning_content) {
          const reasoning = data.choices[0].message.reasoning_content;
          content = `<details><summary>💭 思考过程</summary><pre>${reasoning}</pre></details>\n\n${content}`;
        }
      }
      
      setMessages(prev => [...prev, { role: 'assistant', content }])
    } catch (e) {
      setMessages(prev => [...prev, { role: 'assistant', content: '错误: ' + e.message }])
    }
    setLoading(false)
  }

  // 批改作文函数
  const gradeEssay = async () => {
    if (!essay.trim() || loading) return
    setLoading(true)
    setGradingResult(null)

    try {
      const res = await fetch('/ai/api/v1/chat/completions', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          model,
          messages: [
            { role: 'system', content: GRADING_PROMPT },
            { role: 'user', content: `请批改以下英语作文：\n\n${essay}` }
          ],
          stream: false,
          thinking: true, // 批改时开启思考模式
        }),
      })
      const data = await res.json()
      const content = data.choices?.[0]?.message?.content || '（无结果）'
      setGradingResult(content)
    } catch (e) {
      setGradingResult('批改失败: ' + e.message)
    }
    setLoading(false)
  }

  // 画图函数
  const generateImage = async () => {
    if (!imagePrompt.trim() || loading) return
    setLoading(true)
    setGeneratedImage(null)

    try {
      const res = await fetch('/ai/api/v1/images/generations', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          prompt: imagePrompt,
          model: 'flux', // Venice 的 Flux 模型
          size: '1024x1024',
        }),
      })
      const data = await res.json()
      
      if (data.data && data.data[0]) {
        setGeneratedImage(data.data[0].url || `data:image/png;base64,${data.data[0].b64_json}`)
      } else {
        setGeneratedImage('error')
        alert('生成失败：' + JSON.stringify(data))
      }
    } catch (e) {
      setGeneratedImage('error')
      alert('生成失败: ' + e.message)
    }
    setLoading(false)
  }

  return (
    <div className="app">
      <header>
        <h1>小喵AI助手</h1>
        <div className="controls">
          {/* 画图模式开关 */}
          <label className="thinking-toggle">
            <input 
              type="checkbox" 
              checked={showImageGen} 
              onChange={e => setShowImageGen(e.target.checked)}
            />
            <span>🎨 画图</span>
          </label>
          
          {/* 批改模式开关 */}
          <label className="thinking-toggle">
            <input 
              type="checkbox" 
              checked={showGrading} 
              onChange={e => setShowGrading(e.target.checked)}
            />
            <span>📝 作文批改</span>
          </label>
          
          {/* 思考模式开关 */}
          <label className="thinking-toggle">
            <input 
              type="checkbox" 
              checked={thinking} 
              onChange={e => setThinking(e.target.checked)}
            />
            <span>💭 思考模式</span>
          </label>
          
          {/* 模型选择器 */}
          <div className="model-selector">
            <button onClick={() => setShowModels(!showModels)}>
              {MODELS.find(m => m.id === model)?.name} ▼
            </button>
            {showModels && (
              <div className="model-dropdown">
                {MODELS.map(m => (
                  <div
                    key={m.id}
                    className={model === m.id ? 'selected' : ''}
                    onClick={() => { setModel(m.id); setShowModels(false) }}
                  >
                    <b>{m.name}</b>
                    <span>{m.desc}</span>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      </header>
      
      {/* 画图模式界面 */}
      {showImageGen ? (
        <div className="image-gen-panel">
          <h2>🎨 AI 画图（无审查）</h2>
          <textarea
            value={imagePrompt}
            onChange={e => setImagePrompt(e.target.value)}
            placeholder="描述你想画的图片...&#10;&#10;例如：一只可爱的猫咪在花园里玩耍"
            rows={5}
          />
          <button onClick={generateImage} disabled={loading || !imagePrompt.trim()}>
            {loading ? '生成中...' : '生成图片'}
          </button>
          
          {generatedImage && generatedImage !== 'error' && (
            <div className="generated-image">
              <h3>生成结果</h3>
              <img src={generatedImage} alt="AI Generated" />
            </div>
          )}
        </div>
      ) : showGrading ? (
        /* 批改模式界面 */
        <div className="grading-panel">
          <h2>📝 英语作文批改</h2>
          <textarea
            value={essay}
            onChange={e => setEssay(e.target.value)}
            placeholder="请输入你的英语作文..."
            rows={10}
          />
          <button onClick={gradeEssay} disabled={loading || !essay.trim()}>
            {loading ? '批改中...' : '开始批改'}
          </button>
          
          {gradingResult && (
            <div className="grading-result">
              <h3>批改结果</h3>
              <div className="result-content">
                {gradingResult.split('\n').map((line, i) => (
                  <p key={i}>{line}</p>
                ))}
              </div>
            </div>
          )}
        </div>
      ) : (
        /* 正常聊天界面 */
        <>
          <div className="messages">
            {messages.map((msg, i) => (
              <div key={i} className={msg.role}>
                {msg.content.startsWith('<details') 
                  ? <div dangerouslySetInnerHTML={{ __html: msg.content }} />
                  : msg.content
                }
              </div>
            ))}
            {loading && <div className="assistant loading">{thinking ? '深度思考中...' : '思考中...'}</div>}
            <div ref={messagesEndRef} />
          </div>
          <div className="input-area">
            <input
              value={input}
              onChange={e => setInput(e.target.value)}
              onKeyDown={e => e.key === 'Enter' && send()}
              placeholder="输入消息..."
            />
            <button onClick={send} disabled={loading}>发送</button>
          </div>
        </>
      )}
    </div>
  )
}

export default App
