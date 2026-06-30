import os

# Update index.php
index_path = r"C:\Users\MSI\Music\IAI_MENTORIA5 - Copie\iai-resources\index.php"
with open(index_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Find the Examens Recents grid
grid_start = content.find('<div class="grid grid-cols-3">', content.find('Examens Récents'))
if grid_start != -1:
    content = content[:grid_start] + '<div id="recentExamsGrid" class="grid grid-cols-3">' + content[grid_start + len('<div class="grid grid-cols-3">'):]

# Append the script before closing body
script_index = """
<script>
document.addEventListener('DOMContentLoaded', function() {
    const grid = document.getElementById('recentExamsGrid');
    if (!grid) return;
    
    try {
        const recent = JSON.parse(localStorage.getItem('recentExams') || '[]');
        if (recent.length > 0) {
            grid.innerHTML = ''; // Clear static cards
            recent.forEach((exam, i) => {
                const tagStr = exam.tag ? `<span class="tag">${exam.tag}</span>` : '';
                grid.innerHTML += `
                    <div class="exam-card" style="animation-delay: ${i*0.1}s">
                        <div class="exam-header">
                            ${tagStr}
                            <div class="exam-meta">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                Récemment ouvert
                            </div>
                        </div>
                        <h3 class="exam-title">${exam.title}</h3>
                        <a href="${exam.link}" class="btn btn-outline" style="margin-top: auto;">Reprendre la lecture</a>
                    </div>
                `;
            });
        }
    } catch(e) { console.error(e); }
});
</script>
"""

if "recentExamsGrid" not in content[:grid_start] and "recentExamsGrid" in content: # means we replaced it
    content = content.replace("</body>", script_index + "\n</body>")
    with open(index_path, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Updated index.php")


# Update viewer.php
viewer_path = r"C:\Users\MSI\Music\IAI_MENTORIA5 - Copie\iai-resources\viewer.php"
with open(viewer_path, 'r', encoding='utf-8') as f:
    content = f.read()

script_viewer = """
<script>
    // Track recent exams
    document.addEventListener('DOMContentLoaded', function() {
        try {
            const docId = <?= json_encode($activeDoc['id'] ?? '') ?>;
            const docTitle = <?= json_encode($activeTitle ?? '') ?>;
            
            if (docId && docTitle) {
                // Determine tag from UI
                const activeLink = document.querySelector('.sidebar-doc-link.active');
                let tag = '';
                if (activeLink) {
                    const semGroup = activeLink.closest('.semester-group');
                    const levelGroup = activeLink.closest('.level-group');
                    const semName = semGroup ? semGroup.querySelector('.semester-title').textContent.trim() : '';
                    const levelName = levelGroup ? levelGroup.querySelector('.level-title').textContent.trim() : '';
                    tag = (levelName + ' - ' + semName).replace('Licence ', 'L');
                }
                
                let recent = JSON.parse(localStorage.getItem('recentExams') || '[]');
                recent = recent.filter(e => e.id != docId); // Remove existing
                recent.unshift({
                    id: docId,
                    title: docTitle,
                    tag: tag || 'Document',
                    link: 'viewer.php?id=' + docId
                });
                recent = recent.slice(0, 3);
                localStorage.setItem('recentExams', JSON.stringify(recent));
            }
        } catch(e) {}
    });
</script>
"""
if "recentExams" not in content:
    content = content.replace("</body>", script_viewer + "\n</body>")
    with open(viewer_path, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Updated viewer.php")
