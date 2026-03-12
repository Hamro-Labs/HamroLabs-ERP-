import os
import re

js_file = r'c:\Apache24\htdocs\erp\public\assets\js\institute-admin.js'
pattern = re.compile(r'window\.(render\w+)\s*=\s*(?:async\s+)?function')

with open(js_file, 'r', encoding='utf-8') as f:
    content = f.read()
    matches = pattern.findall(content)
    for func in matches:
        print(f" - {func}")
