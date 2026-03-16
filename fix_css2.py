import os

css_path = r"C:\Users\MSI\Music\IAI_MENTORIA\iai-resources\css\style.css"

with open(css_path, "r", encoding="utf-8") as f:
    lines = f.readlines()

clean_css = """
/* --- Subject Page Sidebar Layout --- */
.subject-layout {
  display: flex;
  gap: 2rem;
  align-items: flex-start;
}
.sidebar {
  width: 250px;
  background-color: var(--bg-card);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  padding: 1.5rem;
  flex-shrink: 0;
  position: sticky;
  top: 6rem;
  border: 1px solid var(--border);
}
.sidebar-title {
  font-weight: 700;
  color: var(--primary);
  margin-bottom: 1rem;
  font-size: 1.1rem;
  border-bottom: 2px solid var(--border);
  padding-bottom: 0.75rem;
}
.year-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.year-btn {
  background-color: transparent;
  border: none;
  text-align: left;
  padding: 0.75rem 1rem;
  border-radius: var(--radius-sm);
  cursor: pointer;
  font-weight: 500;
  color: var(--text-muted);
  transition: all var(--transition-fast);
  border-left: 3px solid transparent;
}
.year-btn:hover {
  background-color: var(--secondary);
  color: var(--primary);
  border-left-color: var(--primary-light);
}
.year-btn.active {
  background-color: var(--primary);
  color: white;
  border-left-color: var(--accent);
}
.subject-content {
  flex: 1;
}
.subject-header {
  background-color: var(--bg-card);
  padding: 2.5rem;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  margin-bottom: 2rem;
  border: 1px solid var(--border);
}
.year-panels .year-panel {
  display: none;
  animation: fadeIn 0.3s ease-in-out;
}
.year-panels .year-panel.active {
  display: block;
}
.resource-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1.5rem;
}
.resource-card {
  background-color: var(--bg-card);
  border-radius: var(--radius-md);
  padding: 1.5rem 1rem;
  box-shadow: var(--shadow-sm);
  text-align: center;
  border-top: 3px solid var(--accent);
  transition: transform var(--transition-normal);
  border-left: 1px solid var(--border);
  border-right: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
}
.resource-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-hover);
  border-top-color: var(--primary);
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
  .subject-layout {
    flex-direction: column;
  }
  .sidebar {
    width: 100%;
    position: static;
    margin-bottom: 1.5rem;
  }
}
"""

# Find the start of the garbage CSS (around line 621, which corresponds to index 620)
# We know it starts right after `@media (max-width: 768px)` block ends which is at line 619.
start_idx = 620
end_idx = 730

new_lines = lines[:start_idx] + [clean_css + "\n"] + lines[end_idx:]

with open(css_path, "w", encoding="utf-8") as f:
    f.writelines(new_lines)
print("CSS replaced by line index.")
