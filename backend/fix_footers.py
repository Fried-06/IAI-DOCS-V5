import os

creators_html = """
                <!-- Creators -->
                <div class="footer-col">
                    <h3>Créateurs</h3>
                    <div class="creator-list">
                        <a href="#" class="creator-item" style="text-decoration:none; transition: transform 0.2s;" onmouseover="this.style.transform='translateX(5px)'" onmouseout="this.style.transform='translateX(0)'">
                            <div class="creator-avatar">DK</div>
                            <span class="creator-name">DOSSOU Komlan Krist</span>
                        </a>
                        <a href="#" class="creator-item" style="text-decoration:none; transition: transform 0.2s;" onmouseover="this.style.transform='translateX(5px)'" onmouseout="this.style.transform='translateX(0)'">
                            <div class="creator-avatar">AB</div>
                            <span class="creator-name">ADZEVI Boris</span>
                        </a>
                        <a href="#" class="creator-item" style="text-decoration:none; transition: transform 0.2s;" onmouseover="this.style.transform='translateX(5px)'" onmouseout="this.style.transform='translateX(0)'">
                            <div class="creator-avatar">KM</div>
                            <span class="creator-name">KOFFI Mikaela</span>
                        </a>
                        <a href="#" class="creator-item" style="text-decoration:none; transition: transform 0.2s;" onmouseover="this.style.transform='translateX(5px)'" onmouseout="this.style.transform='translateX(0)'">
                            <div class="creator-avatar">NF</div>
                            <span class="creator-name\">NOUGNANKEY Faure</span>
                        </a>
                        <a href="#" class="creator-item" style="text-decoration:none; transition: transform 0.2s;" onmouseover="this.style.transform='translateX(5px)'" onmouseout="this.style.transform='translateX(0)'">
                            <div class="creator-avatar">SW</div>
                            <span class="creator-name">SAMBO Wilfried</span>
                        </a>
                    </div>
                </div>
"""

files_to_check = ['index.html', 'login.html', 'contribute.html', 'profile.php', 'exams.php', 'search.php', 'contributors.php']

for f in files_to_check:
    if os.path.exists(f):
        with open(f, 'r', encoding='utf-8') as file:
            content = file.read()
        
        # fix the mangled word everywhere:
        content = content.replace('Cràƒ©ateurs', 'Créateurs').replace('CrÃ©ateurs', 'Créateurs')
        
        start_marker = '<!-- Creators -->'
        end_marker = '<!-- Contact Form -->'
        
        if start_marker in content and end_marker in content:
            parts = content.split(start_marker)
            first_half = parts[0]
            rest = parts[1]
            end_parts = rest.split(end_marker)
            second_half = end_marker + end_parts[1]
            
            new_content = first_half + creators_html.strip() + '\n\n                ' + second_half
            with open(f, 'w', encoding='utf-8') as out:
                out.write(new_content)
        
print('Footers updated successfully.')
