"""
extract.py — Multi-provider AI document extraction pipeline for IAI DOCS
==========================================================================
Modes:
  docling    — Docling only (local, fast, free, ~2-5s)
  ai         — Gemini Vision Direct (PDF sent to Gemini, no Docling, ~15-30s)
  ai_clean   — Docling extraction + AI cleanup cascade (DeepSeek → OpenRouter → Gemini)

Usage:
  python extract.py <input_file> <doc_id> <doc_name> --mode docling
  python extract.py <input_file> <doc_id> <doc_name> --mode ai
  python extract.py <input_file> <doc_id> <doc_name> --mode ai_clean
"""

import os
import sys
import json
import argparse
import base64
from dotenv import load_dotenv

# Optional imports depending on availability
try:
    from docling.document_converter import DocumentConverter
except ImportError:
    DocumentConverter = None

try:
    from google import genai
    from google.genai import types
except ImportError:
    genai = None

try:
    from openai import OpenAI
except ImportError:
    OpenAI = None

try:
    import requests as http_requests
except ImportError:
    http_requests = None


# ── Load environment ────────────────────────────────────────
def load_env():
    env_path = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), '.env')
    load_dotenv(env_path)


# ── Shared system prompt ────────────────────────────────────
SYSTEM_PROMPT = r"""You are an expert academic document reconstruction assistant for a university documentation platform.

Your task is to reconstruct uploaded academic documents into clean, structured Markdown for publication on a student resource platform.

The platform stores documents such as:
- assignments
- exams / partiels
- assignment corrections
- exam corrections

Your task is NOT simple OCR transcription.
Your task is to rebuild the document into readable, structured, publication-ready Markdown compatible with Sphinx + Furo.

==================================================
PRIORITY
==================================================

Your top priority is to preserve the educational structure of the document.

That means you must carefully preserve:
- titles
- exercise names
- question numbering
- sub-question structure
- instructions
- answer sections if present
- code blocks
- tables
- formulas
- correction logic if this is a corrigé

==================================================
STRICT RULES
==================================================

1. Output ONLY Markdown.
2. Do NOT explain your work.
3. Do NOT add commentary.
4. Do NOT invent missing academic content.
5. Correct only obvious OCR mistakes.
6. Preserve the original language of the document.
7. Remove scanner noise and duplicated OCR garbage.
8. Keep the result readable and well-structured.
9. Keep the result compatible with Sphinx/Furo.
10. If uncertainty exists, prefer faithful approximation over hallucination.

==================================================
DOCUMENT RECONSTRUCTION RULES
==================================================

Rebuild the document with clean Markdown structure using:

- # Main title
- ## Exercise or major section
- ### Subsections when useful
- numbered lists for questions
- bullet lists for unordered content

Try to preserve or infer, when reasonably clear:
- document title
- subject name
- year
- session
- type of document (assignment, exam, correction)
- logical reading order

==================================================
IF THE DOCUMENT IS A CORRECTION
==================================================

If the content appears to be a correction / solution document:
- preserve the original exercise structure
- keep each answer under the corresponding question
- clearly separate questions and solutions
- make the correction easy to read for students
- preserve formulas, code, and explanation flow

==================================================
FORMATTING RULES
==================================================

### Code
Use fenced code blocks for programming content.

### SQL
Use sql code blocks for SQL queries.

### Math
Use LaTeX-style Markdown math where expressions are recognizable.

Examples:
- Inline: $A \cap B$
- Block:

$$
P(A \cup B) = P(A) + P(B) - P(A \cap B)
$$

### Tables
Reconstruct readable Markdown tables when possible.

### Lists
Keep lists and numbering coherent.

==================================================
CLEANUP RULES
==================================================

Remove or fix:
- broken line wrapping
- OCR duplication
- scanner artifacts
- page number noise
- repeated headers/footers
- broken punctuation where obvious
- meaningless spacing

==================================================
OUTPUT QUALITY STANDARD
==================================================

The result should look like a clean academic Markdown draft prepared for administrator review before publication on a university resource website.

It should be:
- readable
- structured
- clean
- faithful
- useful for students
"""


# ═══════════════════════════════════════════════════════════
# MODE 1: DOCLING ONLY (Fast, Free, Local)
# ═══════════════════════════════════════════════════════════

def extract_with_docling(input_path):
    """Extract document to Markdown using Docling only (no AI)."""
    print(f"[DOCLING] Extracting from: {input_path}")
    if not DocumentConverter:
        raise ImportError("Docling not installed. Run: pip install docling")

    converter = DocumentConverter()
    result = converter.convert(input_path)
    markdown_output = result.document.export_to_markdown()
    print("[DOCLING] Extraction complete.")
    return markdown_output


# ═══════════════════════════════════════════════════════════
# MODE 2: GEMINI VISION DIRECT (PDF → Gemini, no Docling)
# ═══════════════════════════════════════════════════════════

def generate_with_gemini_vision(pdf_path):
    """Send PDF directly to Gemini's vision model — no Docling involved."""
    print(f"[GEMINI VISION] Sending PDF directly: {pdf_path}")
    load_env()
    api_key = os.environ.get("GEMINI_API_KEY")

    if not api_key or api_key == "your_api_key_here":
        raise ValueError("GEMINI_API_KEY is not configured in .env")

    if not genai:
        raise ImportError("google-genai package not installed. Run: pip install google-genai")

    client = genai.Client(api_key=api_key)

    # Read PDF as bytes and encode to base64
    with open(pdf_path, "rb") as f:
        pdf_bytes = f.read()

    print("[GEMINI VISION] Uploading PDF to Gemini...")

    try:
        response = client.models.generate_content(
            model='gemini-2.0-flash',
            contents=[
                types.Content(
                    role="user",
                    parts=[
                        types.Part.from_bytes(data=pdf_bytes, mime_type="application/pdf"),
                        types.Part.from_text("Please reconstruct this academic document into clean, structured Markdown."),
                    ],
                ),
            ],
            config=types.GenerateContentConfig(
                system_instruction=SYSTEM_PROMPT,
                temperature=0.2,
            ),
        )

        content = response.text
        # Strip markdown code block wrappers
        if content.startswith("```markdown"):
            content = content[len("```markdown"):]
        elif content.startswith("```"):
            content = content[3:]
        if content.endswith("```"):
            content = content[:-3]

        print("[GEMINI VISION] Generation complete.")
        return content.strip()

    except Exception as e:
        print(f"[GEMINI VISION] Error: {e}")
        raise


# ═══════════════════════════════════════════════════════════
# AI CLEANUP PROVIDERS (for ai_clean mode)
# ═══════════════════════════════════════════════════════════

def clean_with_deepseek(raw_markdown):
    """Clean raw markdown using DeepSeek API (OpenAI-compatible)."""
    print("[DEEPSEEK] Cleaning markdown...")
    load_env()
    api_key = os.environ.get("DEEPSEEK_API_KEY")

    if not api_key or api_key in ("your_deepseek_key_here", ""):
        raise ValueError("DEEPSEEK_API_KEY not configured")

    if not OpenAI:
        raise ImportError("openai package not installed. Run: pip install openai")

    client = OpenAI(api_key=api_key, base_url="https://api.deepseek.com")

    response = client.chat.completions.create(
        model="deepseek-chat",
        messages=[
            {"role": "system", "content": SYSTEM_PROMPT},
            {"role": "user", "content": f"Please reconstruct the following raw OCR markdown:\n\n{raw_markdown}"},
        ],
        temperature=0.2,
        max_tokens=8192,
    )

    content = response.choices[0].message.content
    if content.startswith("```markdown"):
        content = content[len("```markdown"):]
    elif content.startswith("```"):
        content = content[3:]
    if content.endswith("```"):
        content = content[:-3]

    print("[DEEPSEEK] Cleanup complete.")
    return content.strip()


def clean_with_openrouter(raw_markdown):
    """Clean raw markdown using OpenRouter API."""
    print("[OPENROUTER] Cleaning markdown...")
    load_env()
    api_key = os.environ.get("OPENROUTER_API_KEY")

    if not api_key or api_key in ("your_openrouter_key_here", ""):
        raise ValueError("OPENROUTER_API_KEY not configured")

    if not http_requests:
        raise ImportError("requests package not installed. Run: pip install requests")

    response = http_requests.post(
        url="https://openrouter.ai/api/v1/chat/completions",
        headers={
            "Authorization": f"Bearer {api_key}",
            "Content-Type": "application/json",
            "HTTP-Referer": "https://iai-docs.com",
            "X-Title": "IAI DOCS",
        },
        json={
            "model": "deepseek/deepseek-chat-v3-0324:free",
            "messages": [
                {"role": "system", "content": SYSTEM_PROMPT},
                {"role": "user", "content": f"Please reconstruct the following raw OCR markdown:\n\n{raw_markdown}"},
            ],
            "temperature": 0.2,
            "max_tokens": 8192,
        },
        timeout=120,
    )

    if response.status_code != 200:
        raise Exception(f"OpenRouter error {response.status_code}: {response.text}")

    data = response.json()
    content = data["choices"][0]["message"]["content"]

    if content.startswith("```markdown"):
        content = content[len("```markdown"):]
    elif content.startswith("```"):
        content = content[3:]
    if content.endswith("```"):
        content = content[:-3]

    print("[OPENROUTER] Cleanup complete.")
    return content.strip()


def clean_with_gemini(raw_markdown):
    """Clean raw markdown using Gemini API (existing method, kept as last resort)."""
    print("[GEMINI TEXT] Cleaning markdown...")
    load_env()
    api_key = os.environ.get("GEMINI_API_KEY")

    if not api_key or api_key == "your_api_key_here":
        raise ValueError("GEMINI_API_KEY not configured")

    if not genai:
        raise ImportError("google-genai package not installed")

    client = genai.Client(api_key=api_key)
    prompt = f"Please reconstruct the following raw OCR markdown:\n\n{raw_markdown}"

    try:
        response = client.models.generate_content(
            model='gemini-2.0-flash',
            contents=prompt,
            config=types.GenerateContentConfig(
                system_instruction=SYSTEM_PROMPT,
                temperature=0.2,
            ),
        )

        content = response.text
        if content.startswith("```markdown"):
            content = content[len("```markdown"):]
        elif content.startswith("```"):
            content = content[3:]
        if content.endswith("```"):
            content = content[:-3]

        print("[GEMINI TEXT] Cleanup complete.")
        return content.strip()

    except Exception as e:
        print(f"[GEMINI TEXT] Error: {e}")
        raise


# ═══════════════════════════════════════════════════════════
# CASCADE: Try DeepSeek → OpenRouter → Gemini → Raw fallback
# ═══════════════════════════════════════════════════════════

def clean_with_ai_cascade(raw_markdown):
    """Try multiple AI providers in sequence. Return first successful result."""
    providers = [
        ("DeepSeek", clean_with_deepseek),
        ("OpenRouter", clean_with_openrouter),
        ("Gemini", clean_with_gemini),
    ]

    for name, func in providers:
        try:
            print(f"\n[CASCADE] Trying {name}...")
            result = func(raw_markdown)
            print(f"[CASCADE] ✅ {name} succeeded!")
            return result
        except Exception as e:
            print(f"[CASCADE] ❌ {name} failed: {e}")
            continue

    print("[CASCADE] ⚠️ All providers failed. Using raw Docling output.")
    return raw_markdown


# ═══════════════════════════════════════════════════════════
# MAIN
# ═══════════════════════════════════════════════════════════

def main():
    parser = argparse.ArgumentParser(
        description="Extract document and rebuild into Markdown using AI."
    )
    parser.add_argument("input_file", help="Path to the uploaded document (PDF, DOCX, etc.)")
    parser.add_argument("doc_id", help="Database ID of the document")
    parser.add_argument("doc_name", help="Safe name of the document (used to name the draft)")
    parser.add_argument("--display-title", help="Formatted title to enforce as H1", default=None)
    parser.add_argument(
        "--mode",
        choices=["docling", "ai", "ai_clean"],
        default="ai_clean",
        help="Generation mode: docling (fast/free), ai (Gemini Vision Direct), ai_clean (Docling + AI cascade)"
    )

    args = parser.parse_args()
    input_file = args.input_file
    doc_id = args.doc_id
    doc_name = args.doc_name
    display_title = args.display_title if args.display_title else doc_name
    mode = args.mode

    if not os.path.exists(input_file):
        print(f"File not found: {input_file}")
        sys.exit(1)

    # Setup drafts folder
    backend_dir = os.path.dirname(os.path.abspath(__file__))
    drafts_dir = os.path.join(os.path.dirname(backend_dir), 'drafts')
    os.makedirs(drafts_dir, exist_ok=True)
    draft_file = os.path.join(drafts_dir, f"{doc_name}.md")

    print(f"\n{'='*60}")
    print(f"  IAI DOCS — Document Extraction Pipeline")
    print(f"  Mode: {mode.upper()}")
    print(f"  File: {os.path.basename(input_file)}")
    print(f"{'='*60}\n")

    final_markdown = ""

    # ── MODE: DOCLING (fast, free) ──────────────────────────
    if mode == "docling":
        try:
            final_markdown = extract_with_docling(input_file)
        except Exception as e:
            print(f"Docling extraction failed: {e}")
            sys.exit(1)

    # ── MODE: AI (Gemini Vision Direct) ─────────────────────
    elif mode == "ai":
        try:
            final_markdown = generate_with_gemini_vision(input_file)
        except Exception as e:
            print(f"Gemini Vision failed: {e}")
            # Fallback: try via OpenRouter with a vision model
            print("[FALLBACK] Trying OpenRouter vision model...")
            try:
                final_markdown = generate_with_openrouter_vision(input_file)
            except Exception as e2:
                print(f"OpenRouter Vision also failed: {e2}")
                print("[FALLBACK] Last resort: Docling only.")
                try:
                    final_markdown = extract_with_docling(input_file)
                except Exception as e3:
                    print(f"All methods failed: {e3}")
                    sys.exit(1)

    # ── MODE: AI_CLEAN (Docling + AI cascade) ───────────────
    elif mode == "ai_clean":
        try:
            raw_markdown = extract_with_docling(input_file)
        except Exception as e:
            print(f"Docling extraction failed: {e}")
            sys.exit(1)

        final_markdown = clean_with_ai_cascade(raw_markdown)

    # ── Enforce Title ───────────────────────────────────────
    # Remove any existing topmost H1 headers to prevent Sphinx navigation issues
    # and strictly enforce the database document title as the # H1 line.
    lines = final_markdown.split('\n')
    filtered_lines = []
    for line in lines:
        # Strip all H1 and H2 headers that might conflict with the main title
        if line.startswith('# ') or line.startswith('## '):
            if "exercice" not in line.lower() and "question" not in line.lower():
                continue # Skip bad top headers
        filtered_lines.append(line)
        
    final_markdown = f"# {display_title}\n\n" + '\n'.join(filtered_lines).strip()

    # ── Save Draft ──────────────────────────────────────────
    with open(draft_file, "w", encoding="utf-8") as f:
        f.write(final_markdown)

    print(f"\n✅ Success: Draft saved to {draft_file}")
    sys.exit(0)


# ═══════════════════════════════════════════════════════════
# OPENROUTER VISION FALLBACK (for when Gemini direct fails)
# ═══════════════════════════════════════════════════════════

def generate_with_openrouter_vision(pdf_path):
    """Send PDF to OpenRouter vision model as fallback for Gemini Vision."""
    print(f"[OPENROUTER VISION] Sending PDF: {pdf_path}")
    load_env()
    api_key = os.environ.get("OPENROUTER_API_KEY")

    if not api_key or api_key in ("your_openrouter_key_here", ""):
        raise ValueError("OPENROUTER_API_KEY not configured")

    if not http_requests:
        raise ImportError("requests package not installed")

    # Read & encode PDF as base64
    with open(pdf_path, "rb") as f:
        pdf_b64 = base64.b64encode(f.read()).decode("utf-8")

    response = http_requests.post(
        url="https://openrouter.ai/api/v1/chat/completions",
        headers={
            "Authorization": f"Bearer {api_key}",
            "Content-Type": "application/json",
            "HTTP-Referer": "https://iai-docs.com",
            "X-Title": "IAI DOCS",
        },
        json={
            "model": "google/gemini-2.0-flash-001",
            "messages": [
                {"role": "system", "content": SYSTEM_PROMPT},
                {
                    "role": "user",
                    "content": [
                        {
                            "type": "file",
                            "file": {
                                "filename": os.path.basename(pdf_path),
                                "content_type": "application/pdf",
                                "data": pdf_b64,
                            },
                        },
                        {
                            "type": "text",
                            "text": "Please reconstruct this academic document into clean, structured Markdown.",
                        },
                    ],
                },
            ],
            "temperature": 0.2,
            "max_tokens": 8192,
        },
        timeout=120,
    )

    if response.status_code != 200:
        raise Exception(f"OpenRouter Vision error {response.status_code}: {response.text}")

    data = response.json()
    content = data["choices"][0]["message"]["content"]

    if content.startswith("```markdown"):
        content = content[len("```markdown"):]
    elif content.startswith("```"):
        content = content[3:]
    if content.endswith("```"):
        content = content[:-3]

    print("[OPENROUTER VISION] Generation complete.")
    return content.strip()


if __name__ == "__main__":
    main()
