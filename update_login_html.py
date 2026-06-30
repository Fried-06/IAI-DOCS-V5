import re

file_path = r"c:\Users\MSI\Music\IAI_MENTORIA5 - Copie\iai-resources\login.html"
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Add Supabase CDN to head
if "supabase-js" not in content:
    content = content.replace(
        '</head>', 
        '    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>\n</head>'
    )

# 2. Add Social Login CSS
css = """
        /* Social Login Buttons */
        .social-btn-container {
            display: flex;
            gap: 1rem;
            width: 100%;
            margin-top: 1rem;
        }
        .social-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.8rem;
            border-radius: 8px;
            background: var(--bg3);
            border: 1px solid var(--border2);
            color: var(--text);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }
        .social-btn:hover {
            background: #0c1c38;
            border-color: var(--cyan);
            box-shadow: 0 0 10px rgba(0, 229, 196, 0.15);
        }
        .social-btn img {
            width: 20px;
            height: 20px;
        }
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            width: 100%;
            margin: 1.5rem 0 0.5rem 0;
            color: var(--muted);
            font-size: 0.85rem;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--border2);
        }
        .divider:not(:empty)::before {
            margin-right: 1em;
        }
        .divider:not(:empty)::after {
            margin-left: 1em;
        }
"""
if ".social-btn-container" not in content:
    content = content.replace('</style>', css + '\n    </style>')

# 3. Add Google and GitHub buttons to both forms
social_html = """
                <div class="divider">OU</div>
                <div class="social-btn-container">
                    <button type="button" class="social-btn btn-google">
                        <svg viewBox="0 0 24 24" width="20" height="20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Google
                    </button>
                    <button type="button" class="social-btn btn-github">
                        <svg viewBox="0 0 24 24" width="20" height="20" xmlns="http://www.w3.org/2000/svg">
                            <path fill="currentColor" d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
                        </svg>
                        GitHub
                    </button>
                </div>
"""

if "btn-google" not in content:
    content = content.replace('<button type="submit" class="btn">S\'inscrire</button>', 
                              '<button type="submit" class="btn">S\'inscrire</button>\n' + social_html)
    content = content.replace('<button type="submit" class="btn">Se connecter</button>', 
                              '<button type="submit" class="btn">Se connecter</button>\n' + social_html)

# 4. Include our custom supabase_auth.js at the end
if "supabase_auth.js" not in content:
    content = content.replace('</body>', '<script src="js/supabase_auth.js"></script>\n</body>')

# 5. Remove action="backend/auth.php" from forms since JS will handle it
content = content.replace('action="backend/auth.php"', 'action="#"')

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print("login.html updated successfully!")
