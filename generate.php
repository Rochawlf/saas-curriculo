<?php
// --- CONFIGURAÇÃO DE ERROS ---
// Desativa a exibição de erros no browser para não contaminar a resposta JSON.
ini_set('display_errors', 0);
// Ativa o log de erros para um ficheiro, para que possamos depurar se necessário.
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');
error_reporting(E_ALL);

// Aumenta o tempo limite de execução e o limite de memória.
set_time_limit(180); // 3 minutos
ini_set('memory_limit', '256M'); // Aumenta a memória para 256MB

// --- VERIFICAÇÃO E INCLUSÃO DE DEPENDÊNCIAS ---
$required_files = [
    'vendor/tecnickcom/tcpdf.php', // Caminho correto se a pasta 'tcpdf' estiver na raiz.
    'templates/template_moderno.php',
    'templates/template_minimalista.php',
    'templates/template_profissional.php',
    'templates/template_corporativo.php',
    'templates/template_criativo.php'
];
foreach ($required_files as $file) {
    if (!file_exists(__DIR__ . '/' . $file)) {
        send_json_response(['error' => "Erro crítico do servidor: O ficheiro necessário '{$file}' não foi encontrado."], 500);
    }
    require_once(__DIR__ . '/' . $file);
}

// --- FUNÇÕES AUXILIARES ---
function send_json_response($data, $http_code = 200)
{
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($http_code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}
function sanitize_cv_data($data)
{
    $defaults = [
        'nome_completo' => null,
        'cargo' => null,
        'contato' => ['email' => null, 'telefone' => null, 'linkedin' => null, 'localizacao' => null],
        'resumo_profissional' => null,
        'experiencia' => [],
        'educacao' => [],
        'habilidades' => []
    ];
    $data = is_array($data) ? $data : [];
    $data = array_merge($defaults, $data);
    $data['contato'] = array_merge($defaults['contato'], is_array($data['contato'] ?? null) ? $data['contato'] : []);
    foreach (['experiencia', 'educacao', 'habilidades'] as $key) {
        if (!is_array($data[$key])) {
            $data[$key] = [];
        }
    }
    return $data;
}

// --- FLUXO PRINCIPAL ---
session_start();
if (!isset($_SESSION['usuario_id'])) {
    send_json_response(['error' => 'Acesso não autorizado. Por favor, faça login.'], 401);
}

$userInput = $_POST['userInput'] ?? '';
$files = $_FILES['files'] ?? [];
if (empty($userInput) && empty($files['tmp_name'][0])) {
    send_json_response(['error' => 'Nenhuma informação ou ficheiro foi fornecido.'], 400);
}

// --- CARREGAR CHAVE .ENV ---
$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    send_json_response(['error' => 'Erro de configuração do servidor.'], 500);
}
$envVars = [];
foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    list($key, $value) = array_map('trim', explode('=', $line, 2));
    $envVars[$key] = trim($value, "\"'");
}
$apiKey = $envVars['GEMINI_API_KEY'] ?? '';
if (empty($apiKey)) {
    send_json_response(['error' => 'Erro de configuração: Chave da API não encontrada.'], 500);
}

// --- PREPARAR REQUISIÇÃO PARA GEMINI ---
$prompt = "Aja como um analista de dados de RH. Analise as informações de currículo fornecidas e estruture-as num objeto JSON. A estrutura deve ser: { \"nome_completo\": \"string|null\", \"cargo\": \"string|null\", \"contato\": { \"email\": \"string|null\", \"telefone\": \"string|null\", \"linkedin\": \"string|null\", \"localizacao\": \"string|null\" }, \"resumo_profissional\": \"string|null\", \"experiencia\": [ { \"cargo\": \"string\", \"empresa\": \"string\", \"periodo\": \"string\", \"descricao\": \"string\" } ], \"educacao\": [ { \"curso\": \"string\", \"instituicao\": \"string\", \"periodo\": \"string\" } ], \"habilidades\": [\"string\"] }. Use valores nulos ou arrays vazios para informações em falta. O texto do utilizador é: \"{$userInput}\". Sua resposta deve ser APENAS o JSON.";
$parts = [['text' => $prompt]];
if (!empty($files['tmp_name'])) {
    foreach ($files['tmp_name'] as $key => $tmpName) {
        if ($files['error'][$key] === UPLOAD_ERR_OK && file_exists($tmpName)) {
            $mimeType = mime_content_type($tmpName);
            if (str_starts_with($mimeType, 'image/') || $mimeType === 'application/pdf') {
                $parts[] = ['inline_data' => ['mime_type' => $mimeType, 'data' => base64_encode(file_get_contents($tmpName))]];
            }
        }
    }
}

$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-pro:generateContent?key=' . $apiKey;
$data = ['contents' => [['parts' => $parts]], 'generationConfig' => ['response_mime_type' => 'application/json', 'temperature' => 0.2]];

// --- ENVIAR REQUISIÇÃO ---
$ch = curl_init($url);
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($data), CURLOPT_SSL_VERIFYPEER => true, CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// --- TRATAR RESPOSTA DA API ---
if ($curlError) {
    send_json_response(['error' => 'Falha na comunicação com a API (cURL): ' . $curlError], 500);
}
if ($httpcode >= 400) {
    send_json_response(['error' => 'A API Gemini retornou um erro.', 'details' => json_decode($response, true)], $httpcode);
}
$responseData = json_decode($response, true);
$jsonStringFromAI = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? null;
if ($jsonStringFromAI === null) {
    send_json_response(['error' => 'A resposta da IA não continha o texto JSON esperado.', 'details' => $responseData], 500);
}
$structuredData = json_decode($jsonStringFromAI, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    send_json_response(['error' => 'A IA retornou um JSON malformado.', 'raw_response' => $jsonStringFromAI], 500);
}

$safeData = sanitize_cv_data($structuredData);

// --- GERAR PDFs E PRÉ-VISUALIZAÇÕES ---
$tempDir = 'temp_cvs';
if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);
$uniqueId = session_id() . '_' . time();
$generatedFiles = [];
$templates = ['Moderno' => 'create_template_moderno', 'Minimalista' => 'create_template_minimalista', 'Profissional' => 'create_template_profissional', 'Corporativo' => 'create_template_corporativo', 'Criativo' => 'create_template_criativo'];

try {
    foreach ($templates as $name => $functionName) {
        if (function_exists($functionName)) {
            $pdf = call_user_func($functionName, $safeData);

            $baseFileName = "cv_{$uniqueId}_" . strtolower(str_replace(' ', '_', $name));
            $filePath = __DIR__ . "/{$tempDir}/{$baseFileName}.pdf";
            $fileUrl = "{$tempDir}/{$baseFileName}.pdf";

            $pdf->Output($filePath, 'F');

            // A pré-visualização é definida como nula, pois removemos a dependência do Imagick.
            $previewUrl = null;

            $generatedFiles[] = ['template_name' => "Modelo {$name}", 'url' => $fileUrl, 'preview_url' => $previewUrl];
        }
    }
} catch (Exception $e) {
    send_json_response(['error' => 'Ocorreu um erro interno ao gerar os ficheiros PDF. Detalhes: ' . $e->getMessage()], 500);
}

// --- RESPOSTA FINAL ---
send_json_response(['files' => $generatedFiles]);
