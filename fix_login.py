import re

# 1. Fix js/supabase_auth.js
auth_js_path = r"c:\Users\MSI\Music\IAI_MENTORIA5 - Copie\iai-resources\js\supabase_auth.js"
with open(auth_js_path, 'r', encoding='utf-8') as f:
    js_content = f.read()

# Replace shadowed variable
js_content = js_content.replace('const supabase = supabase.createClient', 'const supabaseClient = window.supabase.createClient')
js_content = js_content.replace('supabase.auth.', 'supabaseClient.auth.')

with open(auth_js_path, 'w', encoding='utf-8') as f:
    f.write(js_content)
print("supabase_auth.js fixed!")

# 2. Fix login.html UI size
html_path = r"c:\Users\MSI\Music\IAI_MENTORIA5 - Copie\iai-resources\login.html"
with open(html_path, 'r', encoding='utf-8') as f:
    html_content = f.read()

# Increase height of auth-container
html_content = html_content.replace('height: 600px;', 'min-height: 750px;\n            height: auto;\n            padding-bottom: 20px;')

# We also need to fix .overlay-container to match the new height
html_content = html_content.replace('height: 100%;', 'height: 100%;\n            min-height: 750px;')
# Wait, overlay-container uses position: absolute and height: 100%. That's fine if the parent .auth-container is min-height: 750px and height: 100%. But height: auto might break absolute children relying on 100% height. Let's just use `height: 750px;` for both .auth-container instead of auto.

# Let's revert and do a simpler replace
html_path = r"c:\Users\MSI\Music\IAI_MENTORIA5 - Copie\iai-resources\login.html"
with open(html_path, 'r', encoding='utf-8') as f:
    html_content = f.read()
    
html_content = html_content.replace('height: 600px;', 'height: 780px;')

with open(html_path, 'w', encoding='utf-8') as f:
    f.write(html_content)
print("login.html fixed!")
