import sys
import json
import base64
import os
from google import genai
from google.genai import types

def load_env():
    env_path = os.path.join(os.path.dirname(os.path.dirname(os.path.dirname(__file__))), '.env')
    if os.path.exists(env_path):
        with open(env_path, 'r') as f:
            for line in f:
                if '=' in line and not line.strip().startswith('#'):
                    key, val = line.strip().split('=', 1)
                    val = val.strip().strip('"').strip("'")
                    os.environ[key.strip()] = val

def get_mime_type(ext):
    ext = ext.lower()
    if ext == 'pdf': return 'application/pdf'
    if ext in ['png', 'jpg', 'jpeg']: return 'image/jpeg'
    return 'text/plain' # Fallback for markdown config

def main():
    try:
        # Read JSON from stdin (avoids Windows shell escaping issues)
        raw_input = sys.stdin.read()
        if not raw_input.strip():
            print(json.dumps({"success": False, "error": "No input received on stdin"}))
            sys.exit(1)

        data = json.loads(raw_input)
        action = data.get('action')
        files = data.get('files', [])
        user_prompt = data.get('prompt', '')

        load_env()
        api_key = os.environ.get("GEMINI_API_KEY")
        if not api_key:
            print(json.dumps({"success": False, "error": "API Key missing"}))
            sys.exit(1)

        client = genai.Client(api_key=api_key)
        contents = []

        for f in files:
            path = f.get('path')
            if not os.path.exists(path):
                continue
            
            ext = path.split('.')[-1].lower()
            if ext == 'md' or ext == 'txt':
                # Read as text
                with open(path, 'r', encoding='utf-8', errors='ignore') as text_file:
                    contents.append(text_file.read())
            else:
                # Read as inline data (PDF, DOCX via vision)
                with open(path, "rb") as bin_file:
                    pdf_data = bin_file.read()
                    encoded = base64.b64encode(pdf_data).decode("utf-8")
                    contents.append(
                        types.Part.from_bytes(data=pdf_data, mime_type=get_mime_type(ext))
                    )

        # Universal rendering instructions appended to all prompts
        rendering_rules = """

RÈGLES DE FORMATAGE OBLIGATOIRES :
- Pour TOUTE formule mathématique, utilise la syntaxe LaTeX : $formule$ pour les formules en ligne, $$formule$$ pour les formules centrées.
- Pour les matrices, utilise $$\\begin{pmatrix} a & b \\\\ c & d \\end{pmatrix}$$.
- Pour les ensembles, utilise $\\mathbb{R}$, $\\mathbb{Z}$, etc.
- Pour les tableaux de données ou comparaisons, utilise TOUJOURS des tableaux Markdown (| col1 | col2 |).
- Pour les graphes, organigrammes ou schémas, utilise des blocs de code ```mermaid.
- Utilise les titres Markdown (##, ###) pour structurer tes réponses.
- Utilise les listes à puces et numérotées quand c'est approprié.
- Utilise les blocs de citation (>) pour les remarques et avertissements.
- Réponds TOUJOURS en français."""

        # Action specific prompts
        sys_instructions = ""
        if action == "quiz":
            sys_instructions = "Tu es un professeur de l'IAI expert. Génère un QCM interactif et éducatif contenant 10 questions difficiles basées EXCLUSIVEMENT sur les documents fournis."
        elif action == "mindmap":
            sys_instructions = "Tu es un expert en synthèse. Génère une carte mentale représentant les concepts clés des documents. Formate UNIQUEMENT la réponse en un seul bloc de code ```mermaid utilisant la syntaxe 'mindmap' ou 'graph TD'."
        elif action == "audio":
            sys_instructions = "Tu es scénariste de podcast éducatif. Rédige un script de discussion passionnante de 3 minutes entre deux personnages (un expert et un étudiant curieux) résumant les documents. Formate comme un script de théâtre."
        elif action == "slides":
            sys_instructions = "Génère le plan détaillé d'une présentation de 10 diapositives qui synthétise les concepts majeurs des documents."
        elif action == "concepts":
            sys_instructions = "Tu es un tuteur expert. Extrais les concepts clés, définitions et le vocabulaire important des documents fournis. Utilise un tableau Markdown pour les définitions."
        elif action == "flashcards":
            sys_instructions = "Tu es un créateur de flashcards expert. Génère 15 flashcards sous forme de tableau Markdown (| Recto (Question) | Verso (Réponse) |) basées sur les documents."
        elif action == "traps":
            sys_instructions = "Tu es un professeur expérimenté. Identifie les pièges fréquents et les erreurs classiques qui piègent les étudiants sur ces sujets. Donne des conseils pour les éviter."
        elif action == "resume":
            sys_instructions = "Tu es un synthétiseur expert. Fais un résumé complet et structuré des documents fournis. Utilise des titres, des puces et des formules mathématiques correctes."
        elif action == "infographie":
            sys_instructions = "Tu es un designer d'information. Présente les informations des documents sous forme d'infographie textuelle structurée. Utilise des tableaux, des schémas mermaid et une hiérarchie visuelle claire."
        elif action == "chat":
            sys_instructions = "Tu es IAI-DOCS Copilot, un assistant IA intelligent et bienveillant pour les étudiants de l'IAI (Institut Africain d'Informatique). Tu réponds avec précision. Si des documents sont fournis, base-toi dessus. Sinon, réponds librement avec tes connaissances."
        else:
            sys_instructions = "Tu es un tuteur AI. Réponds à l'utilisateur."

        # Append universal rendering rules
        sys_instructions += rendering_rules

        # For chat mode, add the user's prompt as a text content
        if action == "chat" and user_prompt:
            contents.append(user_prompt)
        try:
            response = client.models.generate_content(
                model='gemini-2.5-flash',
                contents=contents,
                config=types.GenerateContentConfig(
                    system_instruction=sys_instructions,
                    temperature=0.7,
                )
            )
            print(json.dumps({"success": True, "result": response.text}))
        except Exception as api_err:
            print(json.dumps({"success": False, "error": str(api_err)}))

    except Exception as e:
        print(json.dumps({"success": False, "error": str(e)}))

if __name__ == "__main__":
    main()
