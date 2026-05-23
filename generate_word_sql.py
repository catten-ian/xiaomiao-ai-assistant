#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""生成 charade2 词库 SQL 脚本"""

# 导入词库数据
exec(open('/workspace/generate_words.py').read().split("def main")[0])

sql_lines = ["-- Charade 2 词库扩展脚本", "USE charade2;", ""]

# 先删除现有词库（可选）
sql_lines.append("-- 删除现有词库（如需要）")
sql_lines.append("-- TRUNCATE TABLE words;")
sql_lines.append("")

# 生成 INSERT 语句
i = 0
for cat, levels in sorted(WORDS.items()):
    for level, words in levels.items():
        for word in words:
            i += 1
            # 检查是否已存在
            sql_lines.append(
                f"INSERT INTO words (word, category, difficulty, used_count, created_at) "
                f"SELECT '{word}', '{cat}', '{level}', 0, NOW() "
                f"WHERE NOT EXISTS (SELECT 1 FROM words WHERE word = '{word}');"
            )

sql_lines.append("")
sql_lines.append(f"-- 总计: {i} 个词语")
sql_lines.append("-- 验证:")
sql_lines.append("SELECT category, difficulty, COUNT(*) FROM words GROUP BY category, difficulty ORDER BY category, difficulty;")
sql_lines.append("SELECT COUNT(*) AS total_words FROM words;")

sql_content = "\n".join(sql_lines)

with open('/workspace/charade2_words.sql', 'w', encoding='utf-8') as f:
    f.write(sql_content)

print(f"SQL 脚本已生成: /workspace/charade2_words.sql")
print(f"共 {i} 条 INSERT 语句")