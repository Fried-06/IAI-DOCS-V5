<?php
require_once __DIR__ . '/beta_check.php';
// backend/ai_handler.php — Traitement des questions par Gemini IA
require_once 'config.php';

header('Content-Type: application/json');

// Récupération des données POST
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';
$context = $input['context'] ?? '';
$webSearch = $input['webSearch'] ?? false;

if (empty($userMessage)) {
    echo json_encode(['reply' => "Désolé, je n'ai pas reçu votre message."]);
    exit;
}

if (GEMINI_API_KEY === 'VOTRE_CLE_API_ICI') {
    echo json_encode(['reply' => "⚠️ Configuration requise : Veuillez ajouter votre clé API Gemini dans backend/config.php pour activer le tuteur IA."]);
    exit;
}

// Construction du Prompt avec contexte
$prompt = "Tu es 'IAI-DOCS Copilot', un tuteur expert spécialisé dans l'accompagnement des étudiants de l'Institut Africain d'Informatique (IAI). 
Ton rôle actuel est d'aider un étudiant à travailler sur un SUJET D'EXAMEN ou un DEVOIR.

RÈGLES DE FORMATAGE OBLIGATOIRES :
- Pour TOUTE formule mathématique, utilise la syntaxe LaTeX : \$formule\$ pour les formules en ligne, \$\$formule\$\$ pour les formules centrées.
- Pour les matrices, utilise \$\$\\begin{pmatrix} a & b \\\\ c & d \\end{pmatrix}\$\$.
- Pour les tableaux de données, utilise des tableaux Markdown.
- Pour les graphes ou schémas, utilise des blocs de code ```mermaid.
- Utilise les titres (##, ###) et les listes pour structurer.

CONTEXTE DU DOCUMENT (EXAMEN/DEVOIR) :
---
$context
---

TES MISSIONS :
1. AIDE À LA RÉSOLUTION : Si l'étudiant te pose une question sur un exercice du document, aide-le à comprendre l'énoncé et guide-le vers la solution.
2. EXPLICATION PÉDAGOGIQUE : Explique POURQUOI c'est la bonne réponse en te basant sur les concepts informatiques concernés.
3. CORRECTION : Si l'étudiant est totalement bloqué, fournis-lui une correction détaillée.
4. TON : Reste professionnel, encourageant et très précis techniquement.";

// Règle spéciale si la recherche Web est activée
if ($webSearch) {
    $prompt .= "\n\nAUTORISATION SPÉCIALE WEB : L'étudiant a activé le mode 'Recherche Web'. Tu ES AUTORISÉ à répondre à des questions qui n'ont rien à voir avec le document (actualités, autres langages, définitions générales). Utilise ton outil de recherche Google pour lui donner une réponse à jour et précise, même si c'est hors-sujet par rapport à l'examen.";
} else {
    $prompt .= "\n\nCONTRAINTE : Tu dois rester concentré sur le document. Si la question est totalement hors-sujet, rappelle gentiment à l'étudiant que vous travaillez actuellement sur ce document précis.";
}

$prompt .= "\n\nQUESTION DE L'ÉTUDIANT : $userMessage";

// Appel à l'API Gemini (Modèle gemini-2.5-flash comme dans le Studio)
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . GEMINI_API_KEY;

$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.7,
        "maxOutputTokens" => 2048
    ]
];

// Activation de la Recherche Google si demandée
if ($webSearch) {
    $data["tools"] = [
        [
            "googleSearch" => new stdClass()
        ]
    ];
}

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpCode !== 200) {
    $errorData = json_decode($response, true);
    $errorMsg = $errorData['error']['message'] ?? "Erreur inconnue";
    echo json_encode(['reply' => "Désolé, l'IA a renvoyé une erreur (HTTP $httpCode) : $errorMsg"]);
    exit;
}

$result = json_decode($response, true);
$aiReply = $result['candidates'][0]['content']['parts'][0]['text'] ?? "Je n'ai pas pu générer de réponse.";

echo json_encode(['reply' => $aiReply]);
