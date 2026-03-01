import re
import json

md_file = r'c:\Users\Lenovo\Downloads\HamroLabs_Email_Templates_Complete.md'
out_file = r'c:\Apache24\htdocs\erp\templates_array.php'

with open(md_file, 'r', encoding='utf-8') as f:
    content = f.read()

# Regex to find all blocks
# **Template Key:** `some_key`
# ...
# **Subject Line:**
# ```
# Some Subject
# ```
# ...
# **Email Body:**
# ```
# Some Body
# ```

pattern = re.compile(
    r'\*\*Template Key:\*\* `([^`]+)`.*?'
    r'\*\*Subject Line:\*\*\s*```\s*(.*?)\s*```.*?'
    r'\*\*Email Body:\*\*\s*```\s*(.*?)\s*```',
    re.DOTALL
)

matches = pattern.findall(content)

php_array = "        $templates = [\n"

for match in matches:
    key, subject, body = match
    key = key.strip()
    subject = subject.strip().replace("'", "\\'")
    
    # Format body into basic HTML
    # Replace plain text newlines with HTML tags
    body = body.strip()
    
    # basic formatting
    paragraphs = body.split('\n\n')
    html_paragraphs = []
    for p in paragraphs:
        p = p.replace('\n', '<br>')
        html_paragraphs.append(f'<p>{p}</p>')
    
    html_body = '<div style="font-family:sans-serif;color:#333;">' + ''.join(html_paragraphs) + '</div>'
    html_body = html_body.replace("'", "\\'")

    php_array += f"            '{key}' => [\n"
    php_array += f"                'subject' => '{subject}',\n"
    php_array += f"                'body' => '{html_body}'\n"
    php_array += f"            ],\n"

php_array += "        ];\n"

with open(out_file, 'w', encoding='utf-8') as f:
    f.write(php_array)

print(f"Extracted {len(matches)} templates.")
