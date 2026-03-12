import os
import re

js_dir = r'c:\Apache24\htdocs\erp\public\assets\js'
pattern = re.compile(r'window\.(render\w+)\s*=\s*(?:async\s+)?function')

results = {}

for filename in os.listdir(js_dir):
    if filename.startswith('ia-') and filename.endswith('.js'):
        filepath = os.path.join(js_dir, filename)
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
            matches = pattern.findall(content)
            if matches:
                results[filename] = matches

for file, funcs in results.items():
    print(f"{file}:")
    for func in funcs:
        print(f"  - {func}")
