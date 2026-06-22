import re

with open('generate_structure_v4.py', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the layout logic
old_layout = """        <div class="subject-layout">
            <!-- Sidebar: Year Tabs -->
            <aside class="sidebar">
                <div class="sidebar-title">Annees Academiques</div>
                <div class="year-list">
                    <?php foreach ($years as $i => $yr): ?>
                    <button class="year-btn <?= $i === 0 ? 'active' : '' ?>"
                            data-year="<?= $yr ?>"
                            onclick="showYear(<?= $yr ?>)">
                        Annee <?= $yr ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </aside>

            <!-- Content Panels -->
            <div class="subject-content year-panels">
                <?php foreach ($byYear as $yr => $docs): ?>
                <?php $isFirst = ($yr === $firstYear); ?>
                <div id="panel-<?= $yr ?>" class="year-panel <?= $isFirst ? 'active' : '' ?>">
                    <h2 style="color:var(--primary);margin-bottom:1.5rem;border-bottom:1px solid var(--border);padding-bottom:0.5rem;">
                        Ressources de l'annee <?= $yr ?>
                    </h2>
                    <div class="resource-grid">
                        <?php foreach ($docs as $doc): ?>
                        <div class="resource-card">
                            <h3 style="margin-bottom:0.5rem;color:var(--text-main);font-size:0.95rem;"><?= htmlspecialchars($doc['title']) ?></h3>
                            <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:0.35rem;text-transform:capitalize;">
                                <?= htmlspecialchars(str_replace('_', ' ', $doc['type'])) ?>
                            </p>
                            <p style="font-size:0.75rem;color:var(--text-muted);margin-bottom:1rem;">
                                Par <?= htmlspecialchars($doc['user']) ?>
                            </p>
                            <div style="display:flex;gap:0.5rem;flex-direction:column;">
                                <?php if ($doc['hasHtml']): ?>
                                <a href="<?= htmlspecialchars($doc['htmlLink']) ?>"
                                   class="btn btn-primary"
                                   style="width:100%;font-size:0.82rem;text-align:center;padding:0.5rem;">
                                    &#128065; Voir HTML
                                </a>
                                <?php endif; ?>
                                <?php if ($doc['pdfLink'] !== '#'): ?>
                                <a href="<?= htmlspecialchars($doc['pdfLink']) ?>"
                                   class="btn btn-outline"
                                   style="width:100%;font-size:0.82rem;text-align:center;padding:0.5rem;"
                                   target="_blank" rel="noopener">
                                    &#128196; Voir PDF
                                </a>
                                <?php endif; ?>
                                <?php if (!$doc['hasHtml'] && $doc['pdfLink'] === '#'): ?>
                                <span style="color:var(--text-muted);font-size:0.8rem;text-align:center;">Document indisponible</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php endif; ?>
    </main>

    <script>
        function showYear(year) {
            document.querySelectorAll('.year-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.year-panel').forEach(panel => panel.classList.remove('active'));
            const btn = document.querySelector('.year-btn[data-year="' + year + '"]');
            const panel = document.getElementById('panel-' + year);
            if (btn) btn.classList.add('active');
            if (panel) panel.classList.add('active');
        }
    </script>"""

new_layout = """        <div class="subject-layout">
            <!-- Sidebar: Filters -->
            <aside class="sidebar">
                <div class="sidebar-title">Filtres</div>
                
                <h4 style="color:var(--text-muted); font-size:0.8rem; text-transform:uppercase; margin-bottom:0.5rem; letter-spacing:0.05em; margin-top:1rem;">Type de document</h4>
                <div class="type-filters" style="margin-bottom: 2rem;">
                    <button class="year-btn active" data-type="all">Tous les types</button>
                    <?php 
                    $typesPresent = [];
                    foreach($documents as $d) { $typesPresent[$d['type']] = true; }
                    foreach(array_keys($typesPresent) as $type): 
                    ?>
                    <button class="year-btn" data-type="<?= htmlspecialchars($type) ?>">
                        <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $type))) ?>
                    </button>
                    <?php endforeach; ?>
                </div>

                <h4 style="color:var(--text-muted); font-size:0.8rem; text-transform:uppercase; margin-bottom:0.5rem; letter-spacing:0.05em;">Année Académique</h4>
                <div class="year-filters">
                    <button class="year-btn active" data-year="all">Toutes les années</button>
                    <?php foreach ($years as $yr): ?>
                    <button class="year-btn" data-year="<?= $yr ?>">Année <?= $yr ?></button>
                    <?php endforeach; ?>
                </div>
            </aside>

            <!-- Content Panels -->
            <div class="subject-content">
                <div class="resource-grid" id="docs-grid">
                    <?php foreach ($documents as $doc): ?>
                    <div class="resource-card doc-item" data-year="<?= $doc['year'] ?>" data-type="<?= htmlspecialchars($doc['type']) ?>">
                        <h3 style="margin-bottom:0.5rem;color:var(--text-main);font-size:0.95rem;"><?= htmlspecialchars($doc['title']) ?></h3>
                        <div style="display:flex; justify-content:space-between; margin-bottom:0.35rem;">
                            <span style="font-size:0.8rem;color:var(--primary);text-transform:capitalize;font-weight:600;">
                                <?= htmlspecialchars(str_replace('_', ' ', $doc['type'])) ?>
                            </span>
                            <span style="font-size:0.8rem;color:var(--text-muted);font-weight:bold;">
                                <?= $doc['year'] ?>
                            </span>
                        </div>
                        <p style="font-size:0.75rem;color:var(--text-muted);margin-bottom:1rem;">
                            Par <?= htmlspecialchars($doc['user']) ?>
                        </p>
                        <div style="display:flex;gap:0.5rem;flex-direction:column;">
                            <?php if ($doc['hasHtml']): ?>
                            <a href="<?= htmlspecialchars($doc['htmlLink']) ?>"
                               class="btn btn-primary"
                               style="width:100%;font-size:0.82rem;text-align:center;padding:0.5rem;">
                                &#128065; Voir HTML
                            </a>
                            <?php endif; ?>
                            <?php if ($doc['pdfLink'] !== '#'): ?>
                            <a href="<?= htmlspecialchars($doc['pdfLink']) ?>"
                               class="btn btn-outline"
                               style="width:100%;font-size:0.82rem;text-align:center;padding:0.5rem;"
                               target="_blank" rel="noopener">
                                &#128196; Voir PDF
                            </a>
                            <?php endif; ?>
                            <?php if (!$doc['hasHtml'] && $doc['pdfLink'] === '#'): ?>
                            <span style="color:var(--text-muted);font-size:0.8rem;text-align:center;">Document indisponible</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div id="no-docs-message" style="display:none; text-align:center; padding: 4rem 2rem; color: var(--text-muted);">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto 1rem;display:block;opacity:0.4;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <p style="font-size:1.1rem; font-weight:600; margin-bottom:0.5rem;">Aucun document ne correspond à vos critères</p>
                    <p style="font-size:0.9rem;">Essayez de modifier vos filtres.</p>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </main>

    <script>
        let currentType = 'all';
        let currentYear = 'all';

        function filterDocs() {
            document.querySelectorAll('.type-filters .year-btn').forEach(btn => {
                btn.onclick = function() {
                    document.querySelectorAll('.type-filters .year-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentType = this.getAttribute('data-type');
                    applyFilters();
                };
            });
            document.querySelectorAll('.year-filters .year-btn').forEach(btn => {
                btn.onclick = function() {
                    document.querySelectorAll('.year-filters .year-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentYear = this.getAttribute('data-year');
                    applyFilters();
                };
            });
        }
        
        function applyFilters() {
            let visibleCount = 0;
            document.querySelectorAll('.doc-item').forEach(item => {
                const matchType = (currentType === 'all' || item.getAttribute('data-type') === currentType);
                const matchYear = (currentYear === 'all' || item.getAttribute('data-year') === currentYear);
                if (matchType && matchYear) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            document.getElementById('no-docs-message').style.display = visibleCount === 0 ? 'block' : 'none';
        }
        
        // Initialiser
        filterDocs();
    </script>"""

content = content.replace(old_layout, new_layout)

with open('generate_structure_v4.py', 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated UI Layout")
