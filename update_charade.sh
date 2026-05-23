#!/bin/bash
# Charade 2 更新脚本

# 添加游戏结束处理到 room.html

# 1. 备份
cp /var/www/charade2/room.html /var/www/charade2/room.html.bak2

# 2. 使用 Python 更新文件
python3 << 'PYTHON_SCRIPT'
import re

# 读取文件
with open('/var/www/charade2/room.html', 'r') as f:
    content = f.read()

# 在 updateUI 函数中添加 finished 状态处理
old_pattern = r"(else if \(data\.room\.status === 'playing'\) \{[\s\S]*?showPlayingView\(data\);[\s\S]*?\})"
new_code = """else if (data.room.status === 'playing') {
                document.getElementById('room-status').textContent = '游戏进行中';
                showPlayingView(data);
            } else if (data.room.status === 'finished') {
                document.getElementById('room-status').textContent = '游戏结束';
                showFinishedView(data);
            }"""

content = re.sub(old_pattern, new_code, content)

# 在 showPlayingView 函数后添加 showFinishedView 函数
show_finished_view = """

        // 显示游戏结束视图
        function showFinishedView(data) {
            document.getElementById('waiting-view').classList.add('hidden');
            document.getElementById('playing-view').classList.add('hidden');

            // 创建游戏结束视图
            let finishedView = document.getElementById('finished-view');
            if (!finishedView) {
                finishedView = document.createElement('div');
                finishedView.id = 'finished-view';
                finishedView.className = 'game-playing';
                finishedView.innerHTML = `
                    <div class="word-display" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <div class="role">🎉 游戏结束</div>
                        <div class="word">最终排名</div>
                    </div>
                    <div class="final-scores" id="final-scores" style="width: 100%; max-width: 500px; margin: 20px auto;"></div>
                    <button class="btn-start" onclick="location.href='index.html'" style="margin-top: 20px;">返回首页</button>
                `;
                document.querySelector('.game-area').appendChild(finishedView);
            }

            finishedView.classList.remove('hidden');

            // 显示最终排名
            const scores = document.getElementById('final-scores');
            const sortedPlayers = [...data.players].sort((a, b) => b.score - a.score);
            scores.innerHTML = sortedPlayers.map((p, i) => `
                <div class="player-item" style="background: ${i === 0 ? '#ffd700' : i === 1 ? '#c0c0c0' : i === 2 ? '#cd7f32' : '#f8f8f8'}; padding: 15px; margin: 10px 0; border-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <span style="font-size: 24px; font-weight: bold;">${i + 1}</span>
                        <span style="font-weight: ${i < 3 ? 'bold' : 'normal'};">${p.nickname}</span>
                    </div>
                    <span style="font-size: 24px; font-weight: bold; color: #667eea;">${p.score}分</span>
                </div>
            `).join('');
        }
"""

# 在 showPlayingView 函数的最后一个 } 后插入
content = re.sub(r'(function showPlayingView\(data\) \{[\s\S]*?\n        \})', r'\1' + show_finished_view, content)

# 写回文件
with open('/var/www/charade2/room.html', 'w') as f:
    f.write(content)

print("更新成功")
PYTHON_SCRIPT

echo "完成"