/* js/theme.js - Premium Phase 3 Theme Selector */

const THEMES = [
  { id: 'midnight', name: 'Midnight Blue', icon: '🌑' },
  { id: 'obsidian', name: 'Obsidian Gold', icon: '⚜️' },
  { id: 'graphite', name: 'Graphite Violet', icon: '🔮' },
  { id: 'emerald', name: 'Emerald Slate', icon: '🌿' },
  { id: 'arctic', name: 'Arctic Light', icon: '☀️' },
  { id: 'ivory', name: 'Ivory Indigo', icon: '🏛️' },
  { id: 'neon', name: 'Neon Circuit', icon: '⚡' }
];

const initializeTheme = () => {
  const savedTheme = localStorage.getItem('iai_theme') || 'midnight';
  document.documentElement.setAttribute('data-theme', savedTheme);
};

// Immediate execution to prevent flash of wrong theme
initializeTheme();

document.addEventListener('DOMContentLoaded', () => {
  const themeToggleContainers = document.querySelectorAll('.theme-toggle');
  
  if (themeToggleContainers.length === 0) return;

  // Insert styles for the dropdown
  const styleBlock = document.createElement('style');
  styleBlock.textContent = `
    .theme-dropdown-container {
      position: relative;
      display: inline-block;
    }
    .theme-dropdown-btn {
      background: rgba(255,255,255,0.05);
      border: 1px solid var(--border2);
      border-radius: 8px;
      padding: 0.5rem 0.8rem;
      color: var(--text);
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-family: 'JetBrains Mono', monospace;
      font-size: 0.8rem;
      transition: all var(--transition-normal);
    }
    .theme-dropdown-btn:hover {
      border-color: var(--cyan);
      box-shadow: 0 0 10px var(--theme-accent-glow);
    }
    .theme-dropdown-menu {
      position: absolute;
      top: 120%;
      right: 0;
      background: var(--bg2);
      border: 1px solid var(--border);
      border-radius: 12px;
      box-shadow: var(--shadow-lg), 0 10px 30px rgba(0,0,0,0.5);
      min-width: 180px;
      padding: 0.5rem;
      opacity: 0;
      visibility: hidden;
      transform: translateY(10px);
      transition: all var(--transition-fast);
      z-index: 9999;
      pointer-events: none;
      backdrop-filter: blur(16px);
    }
    .theme-dropdown-menu.active {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
      pointer-events: auto;
    }
    .theme-option {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.6rem 1rem;
      color: var(--text-muted);
      cursor: pointer;
      border-radius: 8px;
      transition: background 0.2s, color 0.2s;
      font-family: 'Inter', sans-serif;
      font-size: 0.85rem;
      font-weight: 500;
    }
    .theme-option:hover {
      background: var(--border2);
      color: var(--text);
    }
    .theme-option.active-theme {
      background: var(--theme-accent-hover);
      color: var(--cyan);
      font-weight: 600;
    }
  `;
  document.head.appendChild(styleBlock);

  // Replace each toggle with a full dropdown
  themeToggleContainers.forEach(container => {
    const parent = container.parentElement;
    
    const wrapper = document.createElement('div');
    wrapper.className = 'theme-dropdown-container';
    
    const btn = document.createElement('button');
    btn.className = 'theme-dropdown-btn';
    
    // Find active theme object
    const currentThemeId = document.documentElement.getAttribute('data-theme') || 'midnight';
    const activeThemeObj = THEMES.find(t => t.id === currentThemeId) || THEMES[0];
    
    btn.innerHTML = `<span>${activeThemeObj.icon}</span> <span>Thème</span>`;
    
    const menu = document.createElement('div');
    menu.className = 'theme-dropdown-menu';
    
    THEMES.forEach(t => {
      const option = document.createElement('div');
      option.className = 'theme-option';
      if (t.id === currentThemeId) option.classList.add('active-theme');
      
      option.innerHTML = `<span>${t.icon}</span> <span>${t.name}</span>`;
      
      option.addEventListener('click', () => {
        // Update DOM
        document.documentElement.setAttribute('data-theme', t.id);
        localStorage.setItem('iai_theme', t.id);
        
        // Update menu states across all open dropdowns
        document.querySelectorAll('.theme-option').forEach(opt => opt.classList.remove('active-theme'));
        document.querySelectorAll('.theme-option').forEach(opt => {
          if (opt.textContent.includes(t.name)) {
            opt.classList.add('active-theme');
          }
        });
        
        // Update button visual
        document.querySelectorAll('.theme-dropdown-btn').forEach(button => {
          button.innerHTML = `<span>${t.icon}</span> <span>Thème</span>`;
        });
        
        // Close menu
        menu.classList.remove('active');
      });
      
      menu.appendChild(option);
    });
    
    wrapper.appendChild(btn);
    wrapper.appendChild(menu);
    
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      menu.classList.toggle('active');
    });
    
    document.addEventListener('click', (e) => {
      if (!wrapper.contains(e.target)) {
        menu.classList.remove('active');
      }
    });
    
    // Replace the old button with our new premium wrapper
    parent.replaceChild(wrapper, container);
  });
});
