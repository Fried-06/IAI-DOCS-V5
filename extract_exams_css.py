import re

def process_file(file_path, style_file, theme_map):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Fix mojibake
    content = content.replace('â€”', '—') # em dash
    content = content.replace('â€¢', '•') # bullet
    content = content.replace('â†’', '→') # arrow right
    content = content.replace('ðŸ“š', '📚') # book emoji
    
    style_match = re.search(r'<style>(.*?)</style>', content, re.DOTALL)
    if style_match:
        css = style_match.group(1)
        for old_color, new_var in theme_map.items():
            css = css.replace(old_color, new_var)
            
        with open(style_file, 'a', encoding='utf-8') as cf:
            cf.write(f'\n/* Styles from {file_path} */\n' + css)
            
        new_content = content[:style_match.start()] + content[style_match.end():]
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f'Processed {file_path}')

style_file = 'css/style.css'
theme_map = {
    '#040c18': 'var(--theme-bg)',
    '#0b1930': 'var(--theme-bg3)',
    '#0a1628': 'var(--theme-bg2)',
    '#ffb703': 'var(--theme-warning)',
    '#4a6a8a': 'var(--theme-text-muted)',
    '#07111f': 'var(--theme-bg2)',
    '#1e3558': 'var(--theme-border2)',
    '#c8ddf2': 'var(--theme-text)',
    '#152540': 'var(--theme-border)',
    '#00e5c4': 'var(--theme-accent)',
    '#a855f7': 'var(--theme-purple)',
    '#7c3aed': 'var(--theme-primary)',
    '#3b82f6': 'var(--theme-primary)'
}

process_file('exams.php', style_file, theme_map)
print('Done.')
