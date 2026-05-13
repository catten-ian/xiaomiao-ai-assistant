#!/usr/bin/env python3
"""Nano Banana 2 图片生成脚本"""

import sys
import json
import urllib.request
import base64

API_KEY = "sk-TMNZIXGjpnjiW9fX52Dd9e1272444eDd87105bD9B677037b"
BASE_URL = "https://api.v3.cm/v1beta/models"

def generate_image(prompt, output_path, model="nano-banana-2-2k"):
    """使用 Nano Banana 生成图片"""
    url = f"{BASE_URL}/{model}:predict"
    
    data = json.dumps({
        "contents": [
            {
                "parts": [
                    {"text": prompt}
                ]
            }
        ]
    }).encode('utf-8')
    
    req = urllib.request.Request(url, data=data)
    req.add_header('Content-Type', 'application/json')
    req.add_header('Authorization', f'Bearer {API_KEY}')
    
    try:
        print(f"生成中: {prompt}")
        resp = urllib.request.urlopen(req, timeout=120)
        result = json.loads(resp.read())
        
        # 提取 base64 图片数据
        # Gemini API 返回格式: candidates[0].content.parts[0].inlineData.data
        if 'candidates' in result and len(result['candidates']) > 0:
            candidate = result['candidates'][0]
            if 'content' in candidate and 'parts' in candidate['content']:
                parts = candidate['content']['parts']
                if len(parts) > 0 and 'inlineData' in parts[0]:
                    img_data = parts[0]['inlineData']['data']
                    
                    # 保存图片
                    with open(output_path, 'wb') as f:
                        f.write(base64.b64decode(img_data))
                    
                    print(f"✓ 已保存: {output_path}")
                    return True
                else:
                    print(f"❌ 响应中没有图片数据")
                    print(f"Parts: {parts}")
                    return False
            else:
                print(f"❌ 响应格式错误: {candidate}")
                return False
        else:
            print(f"❌ 响应格式错误: {result}")
            return False
            
    except Exception as e:
        print(f"❌ 生成失败: {e}")
        return False

def main():
    if len(sys.argv) < 3:
        print("用法: python3 nano_banana.py <prompt> <output.png>")
        print("示例: python3 nano_banana.py 'camel skin texture' output.png")
        sys.exit(1)
    
    prompt = sys.argv[1]
    output = sys.argv[2]
    
    generate_image(prompt, output)

if __name__ == '__main__':
    main()
