# Configuration file for Sphinx documentation builder.
project = 'IAI DOCS'
copyright = '2024-2026, IAI Togo'
author = 'IAI Community'
release = '1.0'

extensions = ['myst_parser', 'sphinx_togglebutton', 'sphinx.ext.mathjax']
source_suffix = {'.rst': 'restructuredtext', '.md': 'markdown'}
templates_path = ['_templates']
exclude_patterns = ['_build', '_static', '_templates', 'source', 'build', '**/.ipynb_checkpoints']
html_theme = 'furo'
html_static_path = ['_static']

# Enable myst-parser math extensions so $...$ and $$...$$ are rendered by MathJax
myst_enable_extensions = [
    "dollarmath",
    "amsmath",
]
