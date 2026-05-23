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
- 提供一篇修改后的范文`

const generateId = () => 'conv_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9)

const formatTime = (timestamp) => {
  const date = new Date(timestamp)
  const now = new Date()
  const diff = now - date
  
  if (diff < 60000) return '刚刚'
  if (diff < 3600000) return Math.floor(diff / 60000) + '分钟前'
  if (diff < 86400000) return Math.floor(diff / 3600000) + '小时前'
  if (diff < 604800000) return Math.floor(diff / 86400000) + '天前'
  
  return date.toLocaleDateString('zh-CN', { month: 'short', day: 'numeric' })
}

function App() {
  const [conversations, setConversations] = useState([])
  const [currentConvId, setCurrentConvId] = useState(null)
  const [sidebarOpen, setSidebarOpen] = useState(true)
  const [editingTitle, setEditingTitle] = useState(null)
  const [editTitleValue, setEditTitleValue] = useState('')
  const [messages, setMessages] = useState([])
  const [input, setInput] = useState('')
  const [loading, setLoading] = useState(false)
  const [model, setModel] = useState('MiniMax-M2.5')
  const [showModels, setShowModels] = useState(false)
  const [thinking, setThinking] = useState(false)
  const [showGrading, setShowGrading] = useState(false)
  const [showImageGen, setShowImageGen] = useState(false)
  const [essay, setEssay] = useState('')
  const [gradingResult, setGradingResult] = useState(null)
  const [imagePrompt, setImagePrompt] = useState('')
  const [generatedImage, setGeneratedImage] = useState(null)
  const messagesEndRef = useRef(null)

  useEffect(() => { loadConversations() }, [])
  useEffect(() => { messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' }) }, [messages])
  useEffect(() => { if (currentConvId) loadConversation(currentConvId) }, [currentConvId])
  useEffect(() => { if (currentConvId && messages.length > 0) saveCurrentConversation() }, [messages, currentConvId])

  const loadConversations = async () => {
    try {
      const res = await fetch('/api/conversations')
      const data = await res.json()
      setConversations(data.conversations || [])
      if (data.conversations?.length > 0 && !currentConvId) {
        setCurrentConvId(data.conversations[0].id)
      }
    } catch (e) {
      console.error('加载对话列表失败:', e)
      const local = localStorage.getItem('conversations')
      if (local) {
        const parsed = JSON.parse(local)
        setConversations(parsed)
        if (parsed.length > 0) setCurrentConvId(parsed[0].id)
      }
    }
  }

  const loadConversation = async (id) => {
    try {
      const res = await fetch(`/api/conversations/${id}`)
      const data = await res.json()
      setMessages(data.messages || [])
    } catch (e) {
      console.error('加载对话失败:', e)
      const local = localStorage.getItem(`conv_${id}`)
      if (local) setMessages(JSON.parse(local))
      else setMessages([])
    }
  }

  const saveCurrentConversation = async () => {
    if (!currentConvId) return
    const conv = conversations.find(c => c.id === currentConvId)
    const title = conv?.title || generateTitle(messages)
    
    try {
      await fetch(`/api/conversations/${currentConvId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title, messages })
      })
    } catch (e) {
      console.error('保存失败:', e)
    }
    localStorage.setItem(`conv_${currentConvId}`, JSON.stringify(messages))
  }

  const generateTitle = (msgs) => {
    if (msgs.length === 0) return '新对话'
    const firstUserMsg = msgs.find(m => m.role === 'user')
    if (!firstUserMsg) return '新对话'
    const content = typeof firstUserMsg.content === 'string' ? firstUserMsg.content : ''
    return content.slice(0, 20) + (content.length > 20 ? '...' : '')
  }

  const createNewConversation = async () => {
    const id = generateId()
    const now = Date.now()
    const newConv = { id, title: '新对话', created_at: now, updated_at: now }
    
    try {
      await fetch('/api/conversations', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, title: '新对话' })
      })
    } catch (e) {
      console.error('创建对话失败:', e)
    }
    
    setConversations(prev => [newConv, ...prev])
    setCurrentConvId(id)
    setMessages([])
    localStorage.setItem(`conv_${id}`, JSON.stringify([]))
  }

  const deleteConversation = async (id, e) => {
    e?.stopPropagation()
    if (!confirm('确定要删除这个对话吗？')) return
    
    try {
      await fetch(`/api/conversations/${id}`, { method: 'DELETE' })
    } catch (e) {
      console.error('删除失败:', e)
    }
    
    setConversations(prev => prev.filter(c => c.id !== id))
    localStorage.removeItem(`conv_${id}`)
    
    if (currentConvId === id) {
      const remaining = conversations.filter(c => c.id !== id)
      if (remaining.length > 0) setCurrentConvId(remaining[0].id)
      else {
        setCurrentConvId(null)
        setMessages([])
      }
    }
  }

  const startEditTitle = (conv, e) => {
    e?.stopPropagation()
    setEditingTitle(conv.id)
    setEditTitleValue(conv.title)
  }

  const saveEditTitle = async (id) => {
    if (!editTitleValue.trim()) {
      setEditingTitle(null)
      return
    }
    
    try {
      await fetch(`/api/conversations/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title: editTitleValue.trim() })
      })
    } catch (e) {
      console.error('更新标题失败:', e)
    }
    
    setConversations(prev => prev.map(c => 
      c.id === id ? { ...c, title: editTitleValue.trim() } : c
    ))
    setEditingTitle(null)
  }

  const send = async () => {
    if (!input.trim() || loading) return
    
    let convId = currentConvId
    if (!convId) {
      convId = generateId()
      const now = Date.now()
      const newConv = { id: convId, title: '新对话', created_at: now, updated_at: now }
      
      try {
        await fetch('/api/conversations', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: convId, title: '新对话' })
        })
      } catch (e) {}
      
      setConversations(prev => [newConv, ...prev])
      setCurrentConvId(convId)
    }
    
    const userMsg = { role: 'user', content: input }
    setMessages(prev => [...prev, userMsg])
    setInput('')
    setLoading(true)

    try {
      const res = await fetch('/ai/api/v1/chat/completions', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ model, messages: [...messages, userMsg], stream: false, thinking }),
      })
      const data = await res.json()
      
      let content = data.choices?.[0]?.message?.content || '（无回复）'
      
      if (thinking) {
        const thinkMatch = content.match(/<think>([\s\S]*?)<\/think>/)
        if (thinkMatch) {
          const reasoning = thinkMatch[1].trim()
          content = content.replace(/<think>[\s\S]*?<\/think>/, '').trim()
          content = `<details><summary>💭 思考过程</summary><pre>${reasoning}</pre></details>\n\n${content}`
        } else if (data.choices?.[0]?.message?.reasoning_content) {
          const reasoning = data.choices[0].message.reasoning_content
          content = `<details><summary>💭 思考过程</summary><pre>${reasoning}</pre></details>\n\n${content}`
        }
      }
      
      setMessages(prev => [...prev, { role: 'assistant', content }])
      
      // 更新标题
      if (messages.length === 0) {
        const title = generateTitle([...messages, userMsg, { role: 'assistant', content }])
        setConversations(prev => prev.map(c => c.id === convId ? { ...c, title } : c))
      }
    } catch (e) {
      setMessages(prev => [...prev, { role: 'assistant', content: '错误: ' + e.message }])
    }
    setLoading(false)
  }

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
          thinking: true,
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

  const generateImage = async () => {
    if (!imagePrompt.trim() || loading) return
    setLoading(true)
    setGeneratedImage(null)

    try {
      const res = await fetch('/ai/api/v1/images/generations', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ prompt: imagePrompt, model: 'flux', size: '1024x1024' }),
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
      {/* 侧边栏 */}
      <aside className={`sidebar ${sidebarOpen ? 'open' : 'closed'}`}>
        <div className="sidebar-header">
          <h2>💬 对话</h2>
          <button className="new-chat-btn" onClick={createNewConversation} title="新建对话">
            ➕
          </button>
        </div>
        
        <div className="conversations-list">
          {conversations.length === 0 ? (
            <div className="no-conversations">暂无对话</div>
          ) : (
            conversations.map(conv => (
              <div
                key={conv.id}
                className={`conversation-item ${currentConvId === conv.id ? 'active' : ''}`}
                onClick={() => selectConversation(conv.id)}
              >
                {editingTitle === conv.id ? (
                  <input
                    type="text"
                    className="edit-title-input"
                    value={editTitleValue}
                    onChange={e => setEditTitleValue(e.target.value)}
                    onBlur={() => saveEditTitle(conv.id)}
                    onKeyDown={e => e.key === 'Enter' && saveEditTitle(conv.id)}
                    onClick={e => e.stopPropagation()}
                    autoFocus
                  />
                ) : (
                  <>
                    <div className="conv-title">{conv.title}</div>
                    <div className="conv-time">{formatTime(conv.updated_at)}</div>
                  </>
                )}
                <div className="conv-actions">
                  <button
                    className="edit-btn"
                    onClick={(e) => startEditTitle(conv, e)}
                    title="编辑标题"
                  >
                    ✏️
                  </button>
                  <button
                    className="delete-btn"
                    onClick={(e) => deleteConversation(conv.id, e)}
                    title="删除对话"
                  >
                    🗑️
                  </button>
                </div>
              </div>
            ))
          )}
        </div>
      </aside>

      {/* 主内容区 */}
      <main className="main-content">
        <header>
          <button
            className="hamburger"
            onClick={() => setSidebarOpen(!sidebarOpen)}
            title="切换侧边栏"
          >
            ☰
          </button>
          <h1>小喵AI助手</h1>
          <div className="controls">
            <label className="thinking-toggle">
              <input type="checkbox" checked={showImageGen} onChange={e => setShowImageGen(e.target.checked)} />
              <span>🎨 画图</span>
            </label>
            <label className="thinking-toggle">
              <input type="checkbox" checked={showGrading} onChange={e => setShowGrading(e.target.checked)} />
              <span>📝 作文批改</span>
            </label>
            <label className="thinking-toggle">
              <input type="checkbox" checked={thinking} onChange={e => setThinking(e.target.checked)} />
              <span>💭 思考模式</span>
            </label>
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
          <>
            <div className="messages">
              {messages.length === 0 && (
                <div className="empty-state">
                  <p>👋 开始新对话吧！</p>
                  <p className="hint">输入消息或使用上方功能</p>
                </div>
              )}
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
      </main>
    </div>
  )
}

export default App