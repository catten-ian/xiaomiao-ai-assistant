#!/usr/bin/env python3
"""Midjourney 图片生成脚本（简化版）"""

import sys
import json
import urllib.request
import time

API_KEY = "sk-TMNZIXGjpnjiW9fX52Dd9e1272444eDd87105bD9B677037b"
BASE_URL = "https://api.gpt.ge"

def submit_imagine(prompt):
    """提交 Midjourney imagine 任务"""
    url = f"{BASE_URL}/mj/submit/imagine"
    data = json.dumps({
        "prompt": prompt,
        "botType": "MID_JOURNEY"
    }).encode('utf-8')
    
    req = urllib.request.Request(url, data=data)
    req.add_header('Content-Type', 'application/json')
    req.add_header('Authorization', f'Bearer {API_KEY}')
    
    try:
        resp = urllib.request.urlopen(req, timeout=30)
        result = json.loads(resp.read())
        return result.get('result')  # task_id
    except Exception as e:
        print(f"提交失败: {e}")
        return None

def fetch_task(task_id):
    """查询任务状态"""
    url = f"{BASE_URL}/mj/task/{task_id}/fetch"
    req = urllib.request.Request(url)
    req.add_header('Authorization', f'Bearer {API_KEY}')
    
    try:
        resp = urllib.request.urlopen(req, timeout=30)
        return json.loads(resp.read())
    except Exception as e:
        print(f"查询失败: {e}")
        return None

def download_image(task_id, output_path):
    """下载生成的图片"""
    url = f"{BASE_URL}/mj/image/{task_id}"
    req = urllib.request.Request(url)
    req.add_header('Authorization', f'Bearer {API_KEY}')
    
    try:
        resp = urllib.request.urlopen(req, timeout=60)
        with open(output_path, 'wb') as f:
            f.write(resp.read())
        print(f"✓ 图片已保存: {output_path}")
        return True
    except Exception as e:
        print(f"下载失败: {e}")
        return False

def main():
    if len(sys.argv) < 3:
        print("用法: python3 mj_simple.py <prompt> <output.png>")
        sys.exit(1)
    
    prompt = sys.argv[1]
    output = sys.argv[2]
    
    print(f"提交任务: {prompt}")
    task_id = submit_imagine(prompt)
    
    if not task_id:
        print("❌ 提交失败")
        sys.exit(1)
    
    print(f"任务ID: {task_id}")
    print("等待生成中...")
    
    # 轮询等待
    max_wait = 300  # 最多等待5分钟
    start = time.time()
    
    while time.time() - start < max_wait:
        status = fetch_task(task_id)
        if not status:
            time.sleep(5)
            continue
        
        state = status.get('status')
        progress = status.get('progress', '')
        
        if state == 'SUCCESS':
            print(f"✓ 生成完成!")
            download_image(task_id, output)
            break
        elif state == 'FAILED':
            print(f"❌ 生成失败: {status.get('failReason', '未知错误')}")
            sys.exit(1)
        else:
            print(f"  进度: {progress}")
            time.sleep(10)
    else:
        print("❌ 超时")
        sys.exit(1)

if __name__ == '__main__':
    main()
