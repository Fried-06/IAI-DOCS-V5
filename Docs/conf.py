# Configuration file for Sphinx documentation builder.
project = 'IAI DOCS'
copyright = '2024-2026, IAI Togo'
author = 'IAI Community'
release = '1.0'

extensions = ['myst_parser','sphinx.ext.togglebutton','sphinx.ext.mathjax']
source_suffix = {'.rst': 'restructuredtext', '.md': 'markdown'}
templates_path = ['_templates']
exclude_patterns = []
html_theme = 'sphinx_rtd_theme'
html_static_path = ['_static']
