import { useState, useRef, useEffect } from "react"
import ReactMarkdown from 'react-markdown'
import remarkGfm from 'remark-gfm'
import rehypeHighlight from 'rehype-highlight'
import 'highlight.js/styles/github-dark.css'
import "./App.css"

// === Models and ImageModels (keep existing) ===
const models = [
  { id: "google/gemma-4-26b-a4b-it:free", name: "Gemma 4 26B", desc: "Google·多模态", api: "openrouter", vision: true, thinking: false },
  { id: "google/gemma-4-31b-it:free", name: "Gemma 4 31B", desc: "Google旗舰", api: "openrouter", vision: true, thinking: false },
  { id: "meta-llama/llama-3.3-70b-instruct:free", name: "Llama 3.3 70B", desc: "Meta旗舰", api: "openrouter", vision: false, thinking: false },
  { id: "qwen/qwen3-next-80b-a3b-instruct:free", name: "Qwen3 80B", desc: "阿里旗舰", api: "openrouter", vision: false, thinking: false },
  { id: "deepseek/deepseek-v4-flash:free", name: "DeepSeek V4", desc: "国产之光", api: "openrouter", vision: false, thinking: false },
  { id: "gpt-4o", name: "GPT-4o", desc: "最强多模态", api: "v3cm", vision: true, thinking: false },
  { id: "claude-sonnet-4-6", name: "Claude Sonnet 4.6", desc: "编程最强", api: "v3cm", vision: false, thinking: false },
  { id: "gemma-4-uncensored", name: "Gemma 4 非审查版", desc: "Venice·无限制", api: "venice", vision: false, thinking: false },
]

const imageModels = [
  { id: "nano-banana-2-2k", name: "Nano Banana 2K", desc: "生物结构图", type: "nano" },
  { id: "flux-1-schnell", name: "Flux 1 Schnell", desc: "快速生成", type: "flux" },
]

const imageSizes = [
  { id: "auto", name: "自动（推荐）" },
  { id: "1024x1024", name: "1024×1024 方形" },
  { id: "1024x1792", name: "1024×1792 竖版 9:16" },
]

// === NEW: Helper Functions for Conversation Management ===
const generateId = () => Date.now().toString()

const formatTime = (timestamp) => {
  const diff = Date.now() - timestamp
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)
  
  if (minutes < 1) return '刚刚'
  if (minutes < 60) return `${minutes}分钟前`
  if (hours < 24) return `${hours}小时前`
  if (days < 7) return `${days}天前`
  return new Date(timestamp).toLocaleDateString('zh-CN')
}

// === NEW: localStorage Functions ===
const saveConversations = (convs) => {
  try {
    localStorage.setItem('xiaomiao_conversations', JSON.stringify(convs))
  } catch (e) {
    if (e.name === 'QuotaExceededError') {
      alert('存储空间不足，请删除一些旧对话')
    }
  }
}

const loadConversations = () => {
  try {
    const saved = localStorage.getItem('xiaomiao_conversations')
    return saved ? JSON.parse(saved) : []
  } catch {
    return []
  }
}

export default function App() {
  // === EXISTING State Variables ===
  const [messages, setMessages] = useState([])
  const [input, setInput] = useState("")
  const [loading, setLoading] = useState(false)
  const [model, setModel] = useState("google/gemma-4-26b-a4b-it:free")
  const [showModels, setShowModels] = useState(false)
  const [imagePreview, setImagePreview] = useState(null)
  const [imageBase64, setImageBase64] = useState(null)
  const [showThinking, setShowThinking] = useState(false)
  const [showImageGen, setShowImageGen] = useState(false)
  const [imagePrompt, setImagePrompt] = useState("")
  const [imageModel, setImageModel] = useState("nano-banana-2-2k")
  const [imageSize, setImageSize] = useState("auto")
  const [generatingImage, setGeneratingImage] = useState(false)
  const [showMenu, setShowMenu] = useState(false)
  const messagesEndRef = useRef(null)
  const fileInputRef = useRef(null)
  const thinkingStateRef = useRef({ inThinking: false, buffer: "" })
  
  // === NEW: Conversation State Variables ===
  const [conversations, setConversations] = useState([])
  const [currentConversationId, setCurrentConversationId] = useState(null)
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [editingTitle, setEditingTitle] = useState(null)
  const [editTitleValue, setEditTitleValue] = useState('')
  
  // === NEW: Load conversations on mount ===
  useEffect(() => {
    const savedConvs = loadConversations()
    if (savedConvs.length > 0) {
      setConversations(savedConvs)
      setCurrentConversationId(savedConvs[0].id)
      setMessages(savedConvs[0].messages || [])
    }
    setSidebarOpen(window.innerWidth >= 768)
  }, [])
  
  // === EXISTING: Scroll to bottom ===
  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" })
  }, [messages])
  
  // === NEW: Auto-save conversations when messages change ===
  useEffect(() => {
    if (currentConversationId && messages.length > 0) {
      const title = messages[0]?.content?.slice(0, 30) || '新对话'
      const updated = conversations.map(c =>
        c.id === currentConversationId
          ? { ...c, messages, title, updatedAt: Date.now() }
          : c
      )
      setConversations(updated)
      saveConversations(updated)
    }
  }, [messages])
  
  // === NEW: Conversation Management Functions ===
  const createConversation = () => {
    const newConv = {
      id: generateId(),
      title: '新对话',
      messages: [],
      createdAt: Date.now(),
      updatedAt: Date.now()
    }
    const newConvs = [newConv, ...conversations]
    setConversations(newConvs)
    setCurrentConversationId(newConv.id)
    setMessages([])
    saveConversations(newConvs)
  }
  
  const switchConversation = (id) => {
    const conv = conversations.find(c => c.id === id)
    if (conv) {
      setCurrentConversationId(id)
      setMessages(conv.messages || [])
    }
    if (window.innerWidth < 768) setSidebarOpen(false)
  }
  
  const deleteConversation = (id, e) => {
    e?.stopPropagation()
    if (!confirm('确定删除这个对话吗？')) return
    
    const remaining = conversations.filter(c => c.id !== id)
    if (id === currentConversationId) {
      if (remaining.length > 0) {
        setCurrentConversationId(remaining[0].id)
        setMessages(remaining[0].messages || [])
      } else {
        setCurrentConversationId(null)
        setMessages([])
      }
    }
    setConversations(remaining)
    saveConversations(remaining)
  }
  
  // === EXISTING: Image upload and other functions (keep as-is) ===
  const handleImageUpload = (e) => {
    const file = e.target.files[0]
    if (!file) return
    if (file.size > 5 * 1024 * 1024) {
      alert("图片大小不能超过 5MB")
      return
    }
    const reader = new FileReader()
    reader.onload = (e) => {
      setImageBase64(e.target.result)
      setImagePreview(e.target.result)
    }
    reader.readAsDataURL(file)
  }
  
  const parseThinkingTags = (text, state) => {
    let thinking = ""
    let content = ""
    let buffer = state.buffer + text
    let i = 0
    
    while (i < buffer.length) {
      if (state.inThinking) {
        const endIdx = buffer.indexOf("</thinking>", i)
        if (endIdx === -1) {
          thinking += buffer.slice(i)
          state.buffer = ""
          break
        } else {
          thinking += buffer.slice(i, endIdx)
          state.inThinking = false
          i = endIdx + "</thinking>".length
        }
      } else {
        const startIdx = buffer.indexOf("<thinking>", i)
        if (startIdx === -1) {
          content += buffer.slice(i)
          state.buffer = ""
          break
        } else {
          content += buffer.slice(i, startIdx)
          state.inThinking = true
          i = startIdx + "<thinking>".length
        }
      }
    }
    return { thinking, content }
  }


  // === EXISTING: send function ===
  const send = () => {
    if ((!input.trim() && !imageBase64) || loading) return
    
    const userMsg = input.trim()
    const currentImage = imageBase64
    
    let messageContent
    if (currentImage) {
      messageContent = [
        { type: "text", text: userMsg || "请描述这张图片" },
        { type: "image_url", image_url: { url: currentImage } }
      ]
    } else {
      messageContent = userMsg
    }
    
    setInput("")
    setImagePreview(null)
    setImageBase64(null)
    
    setMessages(prev => [...prev, { 
      role: "user", 
      content: userMsg || "请描述这张图片",
      image: currentImage
    }])
    setLoading(true)
    
    thinkingStateRef.current = { inThinking: false, buffer: "" }
    
    setMessages(prev => [...prev, { role: "assistant", content: "", thinking: "", streaming: true }])
    const assistantIndex = messages.length + 1

    const wsProtocol = window.location.protocol === "https:" ? "wss:" : "ws:"
    const wsUrl = wsProtocol + "//" + window.location.host + "/ai/api/ws"
    
    const ws = new WebSocket(wsUrl)
    
    ws.onopen = () => {
      ws.send(JSON.stringify({ 
        model, 
        messages: [...messages, { role: "user", content: messageContent }],
        showThinking 
      }))
    }
    
    ws.onmessage = (event) => {
      const data = JSON.parse(event.data)
      
      if (data.type === "content") {
        const parsed = parseThinkingTags(data.content, thinkingStateRef.current)
        
        setMessages(prev => {
          const newMsgs = [...prev]
          if (newMsgs[assistantIndex]) {
            newMsgs[assistantIndex] = {
              ...newMsgs[assistantIndex],
              content: newMsgs[assistantIndex].content + parsed.content,
              thinking: (newMsgs[assistantIndex].thinking || "") + parsed.thinking
            }
          }
          return newMsgs
        })
      } else if (data.type === "done") {
        setMessages(prev => {
          const newMsgs = [...prev]
          if (newMsgs[assistantIndex]) {
            newMsgs[assistantIndex] = { ...newMsgs[assistantIndex], streaming: false }
          }
          return newMsgs
        })
        setLoading(false)
        ws.close()
      } else if (data.type === "error") {
        setMessages(prev => {
          const newMsgs = [...prev]
          if (newMsgs[assistantIndex]) {
            newMsgs[assistantIndex] = { role: "assistant", content: "错误: " + data.message, streaming: false }
          }
          return newMsgs
        })
        setLoading(false)
        ws.close()
      }
    }
    
    ws.onerror = () => {
      setMessages(prev => {
        const newMsgs = [...prev]
        if (newMsgs[assistantIndex]) {
          newMsgs[assistantIndex] = { role: "assistant", content: "WebSocket 连接失败", streaming: false }
        }
        return newMsgs
      })
      setLoading(false)
    }
  }

  const downloadImage = async (imageData, filename) => {
    const link = document.createElement('a')
    link.href = imageData
    link.download = filename || 'generated-image.png'
    link.click()
  }

  // === EXISTING: Keyboard handler ===
  const handleKeyDown = (e) => {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault()
      send()
    }
  }

  const currentModel = models.find(m => m.id === model)

  // === RENDER ===
  return (
    <div className="app">
      {/* === NEW: Sidebar === */}
      <aside className={`sidebar ${sidebarOpen ? 'open' : ''}`}>
        <div className="sidebar-header">
          <div className="sidebar-title">对话历史</div>
          <button onClick={createConversation} className="new-conv-btn">
            ➕ 新建对话
          </button>
        </div>
        
        <div className="conv-list">
          {conversations.length === 0 ? (
            <div className="conv-empty">暂无对话，点击上方按钮新建</div>
          ) : (
            conversations.map(conv => (
              <div
                key={conv.id}
                className={`conv-item ${conv.id === currentConversationId ? 'active' : ''}`}
                onClick={() => switchConversation(conv.id)}
              >
                <div className="conv-title">{conv.title}</div>
                <div className="conv-time">{formatTime(conv.updatedAt)}</div>
                <button
                  className="conv-delete-btn"
                  onClick={(e) => deleteConversation(conv.id, e)}
                  title="删除对话"
                >
                  ×
                </button>
              </div>
            ))
          )}
        </div>
      </aside>

      {/* === NEW: Overlay for mobile === */}
      {sidebarOpen && window.innerWidth < 768 && (
        <div className="sidebar-overlay" onClick={() => setSidebarOpen(false)} />
      )}

      {/* === MAIN CONTENT === */}
      <main className="main-content">
        {/* === HEADER === */}
        <header className="header">
          <div className="header-left">
            {/* === NEW: Hamburger button === */}
            <button
              className="hamburger-btn"
              onClick={() => setSidebarOpen(!sidebarOpen)}
              title="对话列表"
            >
              ☰
            </button>
            <h1 className="title">🐱 小喵AI</h1>
          </div>
          
          <div className="header-center">
            <div className="model-selector">
              <button
                onClick={() => setShowModels(!showModels)}
                className="model-button"
              >
                {currentModel?.name || model} ▼
              </button>
              {showModels && (
                <div className="model-list">
                  {models.map(m => (
                    <div
                      key={m.id}
                      className="model-item"
                      onClick={() => {
                        setModel(m.id)
                        setShowModels(false)
                      }}
                    >
                      <div className="model-name">{m.name}</div>
                      <div className="model-desc">{m.desc}</div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
          
          <div className="header-right">
            <button
              className="menu-btn"
              onClick={() => setShowMenu(!showMenu)}
            >
              ⋮
            </button>
            {showMenu && (
              <div className="menu-dropdown">
                <label className="menu-item">
                  <input
                    type="checkbox"
                    checked={showThinking}
                    onChange={() => setShowThinking(!showThinking)}
                  />
                  🧠 思考模式
                </label>
                <button onClick={() => { setShowImageGen(true); setShowMenu(false) }} className="menu-item">
                  🎨 图片生成
                </button>
                <button onClick={() => { setMessages([]); setShowMenu(false) }} className="menu-item">
                  🗑 清空记录
                </button>
              </div>
            )}
          </div>
        </header>

        {/* === CHAT MESSAGES === */}
        <div className="messages">
          {messages.length === 0 ? (
            <div className="empty-chat">
              <div className="empty-icon">🐱</div>
              <div className="empty-text">有什么可以帮你的吗？</div>
              <div className="quick-buttons">
                <button onClick={() => setInput("介绍一下你自己")} className="quick-btn">介绍</button>
                <button onClick={() => setInput("帮我写一首诗")} className="quick-btn">写诗</button>
              </div>
            </div>
          ) : (
            messages.map((msg, i) => (
              <div key={i} className={`message ${msg.role}`}>
                <div className="message-avatar">
                  {msg.role === "user" ? "👤" : "🐱"}
                </div>
                <div className="message-content">
                  {msg.image && <img src={msg.image} alt="用户上传" className="message-image" />}
                  {msg.generatedImages?.map((img, idx) => (
                    <div key={idx} className="generated-image-wrapper">
                      <img src={img} alt="AI生成" className="generated-image" />
                      <button onClick={() => downloadImage(img, 'image-'+Date.now()+'.png')} className="download-image-btn">💾</button>
                    </div>
                  ))}
                  {msg.thinking?.length > 0 && (
                    <details className="thinking-block">
                      <summary>🧠 思考过程</summary>
                      <div className="thinking-content">{msg.thinking}</div>
                    </details>
                  )}
                  {msg.content ? (
                    msg.streaming ? (
                      <pre className="streaming-text">{msg.content}</pre>
                    ) : (
                      <ReactMarkdown remarkPlugins={[remarkGfm]} rehypePlugins={[rehypeHighlight]}>
                        {msg.content}
                      </ReactMarkdown>
                    )
                  ) : (loading && i === messages.length - 1 && msg.role === "assistant" ? (
                    <div className="typing-indicator"><span></span><span></span><span></span></div>
                  ) : null)}
                </div>
              </div>
            ))
          )}
          <div ref={messagesEndRef} />
        </div>

        {/* === FOOTER / INPUT === */}
        <footer className="footer">
          {imagePreview && (
            <div className="image-preview-bar">
              <img src={imagePreview} alt="预览" className="preview-thumb" />
              <button onClick={() => { setImagePreview(null); setImageBase64(null) }} className="remove-preview">×</button>
            </div>
          )}
          
          <div className="input-container">
            <input type="file" accept="image/*" ref={fileInputRef} onChange={handleImageUpload} style={{ display: "none" }} />
            {currentModel?.vision && (
              <button onClick={() => fileInputRef.current?.click()} className="upload-button" title="上传图片">🖼</button>
            )}
            <textarea
              value={input}
              onChange={(e) => setInput(e.target.value)}
              onKeyDown={handleKeyDown}
              placeholder="输入消息..."
              rows="1"
              className="input"
            />
            <button onClick={send} disabled={loading || (!input.trim() && !imageBase64)} className="send-button">
              <span className="send-icon">➤</span>
            </button>
          </div>
        </footer>
      </main>

      {/* === IMAGE GENERATION MODAL === */}
      {showImageGen && (
        <div className="image-gen-modal" onClick={() => setShowImageGen(false)}>
          <div className="image-gen-panel" onClick={e => e.stopPropagation()}>
            <h3>🎨 图片生成</h3>
            <textarea
              value={imagePrompt}
              onChange={(e) => setImagePrompt(e.target.value)}
              placeholder="描述你想要生成的图片..."
              rows="3"
            />
            <button onClick={() => {
              if (!imagePrompt.trim()) return
              setMessages(prev => [...prev, { role: "user", content: "🎨 生成图片: " + imagePrompt }])
              setMessages(prev => [...prev, { role: "assistant", content: "🖼️ 图片生成功能已初始化，请稍候..." }])
              setImagePrompt("")
              setShowImageGen(false)
            }} disabled={!imagePrompt.trim()} className="generate-btn">
              生成
            </button>
          </div>
        </div>
      )}
    </div>
  )
}
