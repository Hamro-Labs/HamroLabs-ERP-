import os
import re
import json

base_dir = r'c:\Apache24\htdocs\erp'
js_dir = os.path.join(base_dir, r'public\assets\js')
sidebar_file = os.path.join(base_dir, r'app\Helpers\ia-sidebar-config.php')
core_js = os.path.join(js_dir, 'ia-core.js')

# 1. FIND ALL RENDER FUNCTIONS
render_funcs = {}
render_pattern = re.compile(r'window\.(render\w+)\s*=\s*(?:async\s+)?function')
keyword_pattern = re.compile(r'function\s+(\w*(?:Report|Analytics|Chart)\w*)\s*\(')

for filename in os.listdir(js_dir):
    if filename.startswith('ia-') and filename.endswith('.js'):
        path = os.path.join(js_dir, filename)
        with open(path, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
            renders = render_pattern.findall(content)
            keywords = keyword_pattern.findall(content)
            render_funcs[filename] = {
                'renders': sorted(list(set(renders))),
                'keyword_matches': sorted(list(set(keywords)))
            }

# 2. PARSE SIDEBAR CONFIG (Focus on REPORTS section)
sidebar_data = []
with open(sidebar_file, 'r', encoding='utf-8', errors='ignore') as f:
    sb_content = f.read()
    # Find the REPORTS section
    report_section = re.search(r"'id'\s*=>\s*'reports'.*?'sub'\s*=>\s*\[(.*?)\]", sb_content, re.DOTALL)
    if report_section:
        items = re.findall(r"id'\s*=>\s*'([^']+)'.*?'l'\s*=>\s*'([^']+)'", report_section.group(1))
        for item_id, label in items:
            sidebar_data.append({'id': item_id, 'label': label})

# 3. ANALYZE ROUTING IN ia-core.js
routing_logic = []
with open(core_js, 'r', encoding='utf-8', errors='ignore') as f:
    core_content = f.read()
    # Look for the reports handling block
    report_block = re.search(r"if\s*\(nav\s*===\s*'reports'\s*\)\s*\{(.*?)\}", core_content, re.DOTALL)
    if report_block:
        lines = report_block.group(1).split('\n')
        for line in lines:
            if 'sub' in line and 'render' in line:
                routing_logic.append(line.strip())

# OUTPUT REPORT
print("-" * 50)
print("ERP REPORT MODULE AUDIT")
print("-" * 50)

print("\n[1] EXISTING JS RENDER FUNCTIONS:")
for file, data in render_funcs.items():
    if data['renders'] or data['keyword_matches']:
        print(f"\nFile: {file}")
        if data['renders']:
            print(f"  Standard Renders: {', '.join(data['renders'])}")
        if data['keyword_matches']:
            print(f"  Implicit Reports: {', '.join(data['keyword_matches'])}")

print("\n" + "-" * 50)
print("[2] CURRENT SIDEBAR REPORT IDs:")
for item in sidebar_data:
    print(f"  ID: {item['id']} | Label: {item['label']}")

print("\n" + "-" * 50)
print("[3] CURRENT ia-core.js ROUTING (REPORTS BLOCK):")
if routing_logic:
    for logic in routing_logic:
        print(f"  {logic}")
else:
    print("  No specific mapping found for 'reports' sub-items.")

print("-" * 50)
