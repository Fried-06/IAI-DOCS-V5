import os
import glob
import re

base_dir = r"C:\Users\MSI\Music\IAI_MENTORIA\iai-resources"
html_files = glob.glob(os.path.join(base_dir, "**", "*.html"), recursive=True)

# 1. Update logo and height everywhere
for file in html_files:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Old logo pattern might vary, so let's be broad but safe
    # Replace logo_iai_clean.png with IAI_DOCS2.png
    content = content.replace('assets/logo_iai_clean.png', 'assets/IAI_DOCS2.png')
    
    # Update height to 200px (or ensure it matches the user's manual change)
    content = re.sub(r'style="height: (\d+)px; width: auto; object-fit: contain;"', 
                     r'style="height: 200px; width: auto; object-fit: contain;"', content)
    
    with open(file, 'w', encoding='utf-8') as f:
        f.write(content)

# 2. Specific redesign for login.html
login_path = os.path.join(base_dir, "login.html")
with open(login_path, 'r', encoding='utf-8') as f:
    login_content = f.read()

# Replace the internal <style> block with tech-doc themed CSS
new_login_style = """
    <style>
        :root {
            --bg:      #040c18;
            --bg2:     #07111f;
            --bg3:     #0b1930;
            --border:  #152540;
            --border2: #1e3558;
            --cyan:    #00e5c4;
            --amber:   #ffb703;
            --text:    #c8ddf2;
            --muted:   #4a6a8a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'DM Sans', sans-serif;
        }

        body {
            background: var(--bg);
            color: var(--text);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        .auth-container {
            position: relative;
            width: 900px;
            max-width: 100%;
            height: 600px;
            background: var(--bg2);
            border-radius: 12px;
            border: 1px solid var(--border2);
            box-shadow: 0 20px 40px rgba(0,0,0,0.5), 0 0 20px rgba(0, 229, 196, 0.05);
            overflow: hidden;
        }

        .form-panel {
            position: absolute;
            top: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 0 4rem;
            transition: all 0.6s ease-in-out;
            background: var(--bg2);
        }

        .form-panel h2 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 2.8rem;
            color: #fff;
            margin-bottom: 0.5rem;
            letter-spacing: 0.05em;
        }

        .form-panel p {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            color: var(--muted);
            margin-bottom: 2rem;
            text-align: center;
            letter-spacing: 0.05em;
        }

        .sign-in-panel {
            left: 0;
            width: 50%;
            z-index: 2;
        }

        .sign-up-panel {
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
            transform: translateX(100%);
        }

        form {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .input-field {
            width: 100%;
            background-color: var(--bg3);
            color: var(--text);
            margin: 0.6rem 0;
            padding: 1rem 1.4rem;
            border-radius: 4px;
            border: 1px solid var(--border2);
            outline: none;
            transition: 0.3s;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
        }

        .input-field:focus {
            border-color: var(--cyan);
            box-shadow: 0 0 10px rgba(0, 229, 196, 0.2);
            background-color: #0d1e3d;
        }

        .btn {
            width: 100%;
            background-color: var(--cyan);
            color: #000;
            border: none;
            padding: 1rem;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-top: 1.5rem;
            transition: 0.3s;
        }

        .btn:hover {
            background-color: #00fbd7;
            box-shadow: 0 0 20px rgba(0, 229, 196, 0.4);
            transform: translateY(-2px);
        }

        .overlay-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: transform 0.6s ease-in-out;
            z-index: 100;
            border-left: 1px solid var(--border2);
        }

        .overlay {
            position: absolute;
            top: 0;
            left: -100%;
            width: 200%;
            height: 100%;
            background: linear-gradient(135deg, var(--bg3), var(--bg));
            color: #fff;
            transition: transform 0.6s ease-in-out;
        }

        /* Blueprint grid for overlay */
        .overlay::before {
            content:'';position:absolute;inset:0;
            background-image:
            linear-gradient(rgba(0,229,196,.03) 1px,transparent 1px),
            linear-gradient(90deg,rgba(0,229,196,.03) 1px,transparent 1px);
            background-size:50px 50px;
            pointer-events:none;
        }

        .overlay-panel {
            position: absolute;
            top: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            width: 50%;
            text-align: center;
            padding: 0 3rem;
            transition: transform 0.6s ease-in-out;
        }

        .overlay-left {
            transform: translateX(-20%);
        }

        .overlay-right {
            right: 0;
            transform: translateX(0);
        }

        .overlay h2 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 3rem;
            margin-bottom: 1rem;
            letter-spacing: 0.05em;
        }

        .overlay p {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            color: var(--muted);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .btn-overlay {
            background: transparent;
            border: 2px solid var(--cyan);
            border-radius: 4px;
            padding: 0.8rem 2.5rem;
            color: var(--cyan);
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-overlay:hover {
            background: var(--cyan);
            color: #000;
            box-shadow: 0 0 15px rgba(0, 229, 196, 0.4);
        }

        .home-link {
            position: absolute;
            top: 1.5rem;
            left: 1.5rem;
            z-index: 1000;
            color: var(--muted);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            transition: 0.3s;
        }

        .home-link:hover {
            color: var(--cyan);
        }

        .auth-container.sign-up-mode .overlay-container {
            transform: translateX(-100%);
        }

        .auth-container.sign-up-mode .overlay {
            transform: translateX(50%);
        }

        .auth-container.sign-up-mode .overlay-left {
            transform: translateX(0);
        }

        .auth-container.sign-up-mode .overlay-right {
            transform: translateX(20%);
        }

        .auth-container.sign-up-mode .sign-in-panel {
            transform: translateX(100%);
            opacity: 0;
            z-index: 1;
        }

        .auth-container.sign-up-mode .sign-up-panel {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
        }

        @media(max-width: 768px) {
            .auth-container { height: 100vh; border-radius: 0; }
            .overlay-container { display: none; }
            .form-panel { width: 100%; left: 0; }
            .sign-up-panel { transform: translateX(0); display: none; }
            .auth-container.sign-up-mode .sign-in-panel { display: none; }
            .auth-container.sign-up-mode .sign-up-panel { transform: translateX(0); display: flex; }
        }
    </style>
"""

login_content = re.sub(r'<style>.*?</style>', new_login_style, login_content, flags=re.DOTALL)

with open(login_path, 'w', encoding='utf-8') as f:
    f.write(login_content)

print(f"Updated login.html design and propagated logo changes to {len(html_files)} files.")
