<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>阿里巴巴后台管理</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --gold: #d4af37;
            --gold-light: #f4d03f;
            --bg-dark: #0a0a0a;
            --bg-card: #1a1a1a;
            --text-light: #f5f5f5;
            --text-muted: #a0a0a0;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-dark);
            color: var(--text-light);
            min-height: 100vh;
        }
        
        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        .login-box {
            background: var(--bg-card);
            border: 1px solid rgba(212, 175, 55, 0.3);
            padding: 3rem;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
        }
        
        .login-box h1 {
            color: var(--gold);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-muted);
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            background: var(--bg-dark);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 5px;
            color: var(--text-light);
            font-size: 1rem;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--gold);
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            border: none;
            border-radius: 5px;
            color: var(--bg-dark);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(212, 175, 55, 0.3);
        }
        
        .btn-secondary {
            background: transparent;
            border: 1px solid var(--gold);
            color: var(--gold);
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }
        
        .btn-full { width: 100%; }
        
        .admin-container {
            display: none;
        }
        
        .header {
            background: var(--bg-card);
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: var(--gold);
            font-size: 1.3rem;
        }
        
        .content {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .tab {
            padding: 0.8rem 1.5rem;
            background: var(--bg-card);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 5px;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .tab.active {
            border-color: var(--gold);
            color: var(--gold);
        }
        
        .panel {
            display: none;
        }
        
        .panel.active {
            display: block;
        }
        
        .card {
            background: var(--bg-card);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(212, 175, 55, 0.1);
        }
        
        .card-title {
            color: var(--gold);
            font-size: 1.1rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(212, 175, 55, 0.1);
        }
        
        th {
            color: var(--gold);
            font-weight: 600;
        }
        
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid rgba(212, 175, 55, 0.2);
        }
        
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .actions button {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        
        .modal.show {
            display: flex;
        }
        
        .modal-content {
            background: var(--bg-card);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 10px;
            padding: 2rem;
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-content.wide {
            max-width: 700px;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .modal-title {
            color: var(--gold);
            font-size: 1.2rem;
        }
        
        .close-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        .image-preview {
            width: 100%;
            height: 150px;
            border: 2px dashed rgba(212, 175, 55, 0.3);
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            overflow: hidden;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        
        .image-preview:hover {
            border-color: var(--gold);
        }
        
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .image-preview-placeholder {
            color: var(--text-muted);
            text-align: center;
        }
        
        .upload-progress {
            display: none;
            color: var(--gold);
            text-align: center;
            margin-top: 0.5rem;
        }
        
        .error-msg {
            color: #dc3545;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        /* 活动编辑样式 */
        .activity-form {
            display: grid;
            gap: 1.5rem;
        }
        
        .rule-box {
            background: var(--bg-dark);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 5px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .rule-box h4 {
            color: var(--gold);
            margin-bottom: 0.8rem;
            font-size: 1rem;
        }
        
        .rule-items {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .rule-item {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .rule-item input {
            flex: 1;
            padding: 0.5rem;
            background: var(--bg-card);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 3px;
            color: var(--text-light);
        }
        
        .rule-item button {
            padding: 0.3rem 0.6rem;
            background: #dc3545;
            border: none;
            border-radius: 3px;
            color: white;
            cursor: pointer;
            font-size: 0.8rem;
        }
        
        .add-rule-btn {
            padding: 0.5rem 1rem;
            background: transparent;
            border: 1px dashed var(--gold);
            color: var(--gold);
            border-radius: 5px;
            cursor: pointer;
            margin-top: 0.5rem;
        }
        
        .add-rule-btn:hover {
            background: rgba(212, 175, 55, 0.1);
        }
    </style>
</head>
<body>
    <div class="login-container" id="loginPage">
        <div class="login-box">
            <h1>🔐 后台管理登录</h1>
            <div id="loginError" class="error-msg" style="display: none;"></div>
            <div class="form-group">
                <label>用户名</label>
                <input type="text" id="username" placeholder="请输入用户名">
            </div>
            <div class="form-group">
                <label>密码</label>
                <input type="password" id="password" placeholder="请输入密码">
            </div>
            <button class="btn btn-full" onclick="login()">登录</button>
        </div>
    </div>
    
    <div class="admin-container" id="adminPage">
        <div class="header">
            <h1>🏛️ 阿里巴巴后台管理</h1>
            <button class="btn btn-secondary" onclick="logout()">退出登录</button>
        </div>
        
        <div class="content">
            <div class="tabs">
                <div class="tab active" onclick="switchTab('products')">📦 商品管理</div>
                <div class="tab" onclick="switchTab('team')">👥 团队管理</div>
                <div class="tab" onclick="switchTab('activity')">🎮 活动管理</div>
            </div>
            
            <div class="panel active" id="productsPanel">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">文创产品</span>
                        <button class="btn" onclick="openProductModal('wenchuang')">+ 添加商品</button>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>图片</th>
                                <th>图标</th>
                                <th>名称</th>
                                <th>价格</th>
                                <th>描述</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody id="wenchuangList"></tbody>
                    </table>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">丝路美食</span>
                        <button class="btn" onclick="openProductModal('food')">+ 添加商品</button>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>图片</th>
                                <th>图标</th>
                                <th>名称</th>
                                <th>价格</th>
                                <th>描述</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody id="foodList"></tbody>
                    </table>
                </div>
            </div>
            
            <div class="panel" id="teamPanel">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">团队成员</span>
                        <button class="btn" onclick="openTeamModal()">+ 添加成员</button>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>角色</th>
                                <th>姓名</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody id="teamList"></tbody>
                    </table>
                </div>
            </div>
            
            <div class="panel" id="activityPanel">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">活动内容编辑</span>
                    </div>
                    <div class="activity-form">
                        <div class="form-group">
                            <label>活动标题</label>
                            <input type="text" id="activityTitle" placeholder="如：沙海寻珠">
                        </div>
                        <div class="form-group">
                            <label>活动副标题</label>
                            <input type="text" id="activitySubtitle" placeholder="如：珍珠鉴真小游戏">
                        </div>
                        <div class="form-group">
                            <label>规则模块</label>
                            <div id="rulesContainer"></div>
                            <button class="add-rule-btn" onclick="addRuleBox()">+ 添加规则模块</button>
                        </div>
                        <button class="btn" onclick="saveActivity()">保存活动内容</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal" id="productModal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title" id="productModalTitle">添加商品</span>
                <button class="close-btn" onclick="closeProductModal()">&times;</button>
            </div>
            <input type="hidden" id="productId">
            <input type="hidden" id="productCategory">
            <input type="hidden" id="productImage">
            
            <div class="form-group">
                <label>产品图片</label>
                <div class="image-preview" id="imagePreview" onclick="document.getElementById('imageFile').click()">
                    <div class="image-preview-placeholder" id="imagePlaceholder">
                        📷 点击上传图片<br><small>支持 JPG/PNG/WebP</small>
                    </div>
                    <img id="previewImg" style="display: none;">
                </div>
                <input type="file" id="imageFile" accept="image/*" style="display: none;" onchange="uploadImage(this)">
                <div class="upload-progress" id="uploadProgress">上传中...</div>
            </div>
            
            <div class="form-group">
                <label>图标（Emoji）</label>
                <input type="text" id="productIcon" placeholder="如：🎨">
            </div>
            <div class="form-group">
                <label>名称</label>
                <input type="text" id="productName" placeholder="商品名称">
            </div>
            <div class="form-group">
                <label>价格（可选）</label>
                <input type="text" id="productPrice" placeholder="如：10">
            </div>
            <div class="form-group">
                <label>描述</label>
                <input type="text" id="productDesc" placeholder="简短描述">
            </div>
            <button class="btn btn-full" onclick="saveProduct()">保存</button>
        </div>
    </div>
    
    <div class="modal" id="teamModal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title" id="teamModalTitle">添加成员</span>
                <button class="close-btn" onclick="closeTeamModal()">&times;</button>
            </div>
            <input type="hidden" id="teamId">
            <div class="form-group">
                <label>角色</label>
                <input type="text" id="teamRole" placeholder="如：丝路使者">
            </div>
            <div class="form-group">
                <label>姓名</label>
                <input type="text" id="teamName" placeholder="成员姓名">
            </div>
            <button class="btn btn-full" onclick="saveTeam()">保存</button>
        </div>
    </div>
    
    <script>
        let activityData = null;
        
        async function checkAuth() {
            try {
                const res = await fetch('api.php?action=check_auth');
                const data = await res.json();
                if (data.logged_in) showAdmin();
            } catch (e) {}
        }
        
        async function login() {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                document.getElementById('loginError').textContent = '请输入用户名和密码';
                document.getElementById('loginError').style.display = 'block';
                return;
            }
            
            try {
                const res = await fetch('api.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password })
                });
                const data = await res.json();
                
                if (data.success) {
                    showAdmin();
                } else {
                    document.getElementById('loginError').textContent = data.error;
                    document.getElementById('loginError').style.display = 'block';
                }
            } catch (e) {
                document.getElementById('loginError').textContent = '网络错误';
                document.getElementById('loginError').style.display = 'block';
            }
        }
        
        async function logout() {
            await fetch('api.php?action=logout');
            document.getElementById('loginPage').style.display = 'flex';
            document.getElementById('adminPage').style.display = 'none';
        }
        
        function showAdmin() {
            document.getElementById('loginPage').style.display = 'none';
            document.getElementById('adminPage').style.display = 'block';
            loadData();
        }
        
        function switchTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
            
            const tabIndex = { products: 1, team: 2, activity: 3 };
            document.querySelector(`.tab:nth-child(${tabIndex[tab]})`).classList.add('active');
            document.getElementById(`${tab}Panel`).classList.add('active');
        }
        
        async function loadData() {
            try {
                const res = await fetch('api.php?action=get_all');
                const data = await res.json();
                
                if (data.success) {
                    renderProducts(data.products);
                    renderTeam(data.team);
                    if (data.activity) {
                        activityData = data.activity;
                        renderActivity();
                    }
                }
            } catch (e) {}
        }
        
        function renderProducts(products) {
            const wenchuang = products.filter(p => p.category === 'wenchuang');
            const food = products.filter(p => p.category === 'food');
            
            document.getElementById('wenchuangList').innerHTML = wenchuang.map(p => `
                <tr>
                    <td>${p.image ? `<img src="${p.image}" class="product-img" onerror="this.style.display='none'">` : '-'}</td>
                    <td style="font-size: 1.5rem;">${p.icon}</td>
                    <td>${p.name}</td>
                    <td>${p.price ? '¥' + p.price : '-'}</td>
                    <td>${p.desc}</td>
                    <td class="actions">
                        <button class="btn btn-secondary" onclick='editProduct(${JSON.stringify(p).replace(/'/g, "\\'")})'>编辑</button>
                        <button class="btn btn-danger" onclick="deleteProduct(${p.id})">删除</button>
                    </td>
                </tr>
            `).join('');
            
            document.getElementById('foodList').innerHTML = food.map(p => `
                <tr>
                    <td>${p.image ? `<img src="${p.image}" class="product-img" onerror="this.style.display='none'">` : '-'}</td>
                    <td style="font-size: 1.5rem;">${p.icon}</td>
                    <td>${p.name}</td>
                    <td>${p.price ? '¥' + p.price : '-'}</td>
                    <td>${p.desc}</td>
                    <td class="actions">
                        <button class="btn btn-secondary" onclick='editProduct(${JSON.stringify(p).replace(/'/g, "\\'")})'>编辑</button>
                        <button class="btn btn-danger" onclick="deleteProduct(${p.id})">删除</button>
                    </td>
                </tr>
            `).join('');
        }
        
        function renderTeam(team) {
            document.getElementById('teamList').innerHTML = team.map(t => `
                <tr>
                    <td>${t.role}</td>
                    <td>${t.name}</td>
                    <td class="actions">
                        <button class="btn btn-secondary" onclick='editTeam(${JSON.stringify(t).replace(/'/g, "\\'")})'>编辑</button>
                        <button class="btn btn-danger" onclick="deleteTeam(${t.id})">删除</button>
                    </td>
                </tr>
            `).join('');
        }
        
        function renderActivity() {
            if (!activityData) return;
            
            document.getElementById('activityTitle').value = activityData.title || '';
            document.getElementById('activitySubtitle').value = activityData.subtitle || '';
            
            const container = document.getElementById('rulesContainer');
            container.innerHTML = '';
            
            if (activityData.rules && activityData.rules.length > 0) {
                activityData.rules.forEach((rule, index) => {
                    createRuleBox(rule.title, rule.items, index);
                });
            }
        }
        
        function createRuleBox(title = '', items = [], index = null) {
            const container = document.getElementById('rulesContainer');
            const boxIndex = index !== null ? index : container.children.length;
            
            const box = document.createElement('div');
            box.className = 'rule-box';
            box.dataset.index = boxIndex;
            
            box.innerHTML = `
                <h4>
                    <input type="text" value="${title}" placeholder="规则标题" style="background: transparent; border: none; color: var(--gold); font-size: 1rem; width: 70%;">
                    <button class="btn btn-danger btn-small" onclick="removeRuleBox(this)" style="float: right;">删除模块</button>
                </h4>
                <div class="rule-items">
                    ${items.map(item => `
                        <div class="rule-item">
                            <input type="text" value="${item}">
                            <button onclick="removeRuleItem(this)">×</button>
                        </div>
                    `).join('')}
                </div>
                <button class="add-rule-btn" onclick="addRuleItem(this)">+ 添加条目</button>
            `;
            
            container.appendChild(box);
        }
        
        function addRuleBox() {
            createRuleBox('', []);
        }
        
        function removeRuleBox(btn) {
            btn.closest('.rule-box').remove();
        }
        
        function addRuleItem(btn) {
            const itemsContainer = btn.previousElementSibling;
            const item = document.createElement('div');
            item.className = 'rule-item';
            item.innerHTML = `
                <input type="text" placeholder="输入规则内容">
                <button onclick="removeRuleItem(this)">×</button>
            `;
            itemsContainer.appendChild(item);
        }
        
        function removeRuleItem(btn) {
            btn.closest('.rule-item').remove();
        }
        
        async function saveActivity() {
            const rules = [];
            document.querySelectorAll('.rule-box').forEach(box => {
                const title = box.querySelector('h4 input').value;
                const items = Array.from(box.querySelectorAll('.rule-item input')).map(input => input.value).filter(v => v.trim());
                if (title || items.length > 0) {
                    rules.push({ title, items });
                }
            });
            
            const data = {
                title: document.getElementById('activityTitle').value,
                subtitle: document.getElementById('activitySubtitle').value,
                rules
            };
            
            try {
                const res = await fetch('api.php?action=update_activity', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await res.json();
                
                if (result.success) {
                    alert('保存成功！');
                } else {
                    alert(result.error || '保存失败');
                }
            } catch (e) {
                alert('保存失败');
            }
        }
        
        async function uploadImage(input) {
            if (!input.files || !input.files[0]) return;
            
            const file = input.files[0];
            const preview = document.getElementById('previewImg');
            const placeholder = document.getElementById('imagePlaceholder');
            const progress = document.getElementById('uploadProgress');
            
            // 本地预览
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            };
            reader.readAsDataURL(file);
            
            // 上传到 CDN
            progress.style.display = 'block';
            
            const formData = new FormData();
            formData.append('image', file);
            
            try {
                const res = await fetch('api.php?action=upload_image', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                
                progress.style.display = 'none';
                
                if (data.success) {
                    document.getElementById('productImage').value = data.url;
                    preview.src = data.url;
                } else {
                    alert('上传失败: ' + data.error);
                }
            } catch (e) {
                progress.style.display = 'none';
                alert('上传失败');
            }
        }
        
        function openProductModal(category) {
            document.getElementById('productModalTitle').textContent = '添加商品';
            document.getElementById('productId').value = '';
            document.getElementById('productCategory').value = category;
            document.getElementById('productImage').value = '';
            document.getElementById('productIcon').value = '';
            document.getElementById('productName').value = '';
            document.getElementById('productPrice').value = '';
            document.getElementById('productDesc').value = '';
            document.getElementById('previewImg').style.display = 'none';
            document.getElementById('imagePlaceholder').style.display = 'block';
            document.getElementById('productModal').classList.add('show');
        }
        
        function editProduct(product) {
            document.getElementById('productModalTitle').textContent = '编辑商品';
            document.getElementById('productId').value = product.id;
            document.getElementById('productCategory').value = product.category;
            document.getElementById('productImage').value = product.image || '';
            document.getElementById('productIcon').value = product.icon;
            document.getElementById('productName').value = product.name;
            document.getElementById('productPrice').value = product.price || '';
            document.getElementById('productDesc').value = product.desc;
            
            const preview = document.getElementById('previewImg');
            const placeholder = document.getElementById('imagePlaceholder');
            
            if (product.image) {
                preview.src = product.image;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            } else {
                preview.style.display = 'none';
                placeholder.style.display = 'block';
            }
            
            document.getElementById('productModal').classList.add('show');
        }
        
        function closeProductModal() {
            document.getElementById('productModal').classList.remove('show');
        }
        
        async function saveProduct() {
            const id = document.getElementById('productId').value;
            const data = {
                category: document.getElementById('productCategory').value,
                image: document.getElementById('productImage').value,
                icon: document.getElementById('productIcon').value,
                name: document.getElementById('productName').value,
                price: document.getElementById('productPrice').value,
                desc: document.getElementById('productDesc').value
            };
            
            if (id) data.id = parseInt(id);
            
            const action = id ? 'update_product' : 'add_product';
            
            try {
                const res = await fetch(`api.php?action=${action}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await res.json();
                
                if (result.success) {
                    closeProductModal();
                    loadData();
                } else {
                    alert(result.error);
                }
            } catch (e) {
                alert('保存失败');
            }
        }
        
        async function deleteProduct(id) {
            if (!confirm('确定删除此商品？')) return;
            
            try {
                await fetch(`api.php?action=delete_product&id=${id}`);
                loadData();
            } catch (e) {}
        }
        
        function openTeamModal() {
            document.getElementById('teamModalTitle').textContent = '添加成员';
            document.getElementById('teamId').value = '';
            document.getElementById('teamRole').value = '';
            document.getElementById('teamName').value = '';
            document.getElementById('teamModal').classList.add('show');
        }
        
        function editTeam(member) {
            document.getElementById('teamModalTitle').textContent = '编辑成员';
            document.getElementById('teamId').value = member.id;
            document.getElementById('teamRole').value = member.role;
            document.getElementById('teamName').value = member.name;
            document.getElementById('teamModal').classList.add('show');
        }
        
        function closeTeamModal() {
            document.getElementById('teamModal').classList.remove('show');
        }
        
        async function saveTeam() {
            const id = document.getElementById('teamId').value;
            const data = {
                role: document.getElementById('teamRole').value,
                name: document.getElementById('teamName').value
            };
            
            if (id) data.id = parseInt(id);
            
            const action = id ? 'update_team' : 'add_team';
            
            try {
                const res = await fetch(`api.php?action=${action}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await res.json();
                
                if (result.success) {
                    closeTeamModal();
                    loadData();
                } else {
                    alert(result.error);
                }
            } catch (e) {
                alert('保存失败');
            }
        }
        
        async function deleteTeam(id) {
            if (!confirm('确定删除此成员？')) return;
            
            try {
                await fetch(`api.php?action=delete_team&id=${id}`);
                loadData();
            } catch (e) {}
        }
        
        checkAuth();
    </script>
</body>
</html>
