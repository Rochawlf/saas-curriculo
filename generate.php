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
        'contato' => [
            'email' => null, 
            'telefone' => null, 
            'linkedin' => null, 
            'localizacao' => null
        ],
        'resumo_profissional' => null,
        'experiencia' => [],
        'educacao' => [],
        'habilidades' => [],
        'idiomas' => []
    ];
    
    $data = is_array($data) ? $data : [];
    $data = array_merge($defaults, $data);
    
    // Garantir que contato é um array
    $data['contato'] = array_merge($defaults['contato'], is_array($data['contato'] ?? null) ? $data['contato'] : []);
    
    // Garantir que arrays estão corretos
    foreach (['experiencia', 'educacao', 'habilidades', 'idiomas'] as $key) {
        if (!is_array($data[$key])) {
            $data[$key] = [];
        }
    }
    
    return $data;
}

function formatar_periodo($inicio, $fim = null) {
    if (empty($inicio)) return '';
    
    $inicio_formatado = date('m/Y', strtotime($inicio));
    $fim_formatado = $fim ? date('m/Y', strtotime($fim)) : 'Atual';
    
    return "{$inicio_formatado} - {$fim_formatado}";
}

// --- FUNÇÃO PARA MESCLAR DADOS MELHORADOS ---
function merge_improved_data($originalData, $improvedData) {
    // Manter dados originais estruturais, mas usar conteúdo melhorado da IA
    
    // Resumo profissional - sempre usar o melhorado se disponível
    if (!empty($improvedData['resumo_profissional'])) {
        $originalData['resumo_profissional'] = $improvedData['resumo_profissional'];
    }
    
    // Cargo - usar o sugerido se for mais específico
    if (!empty($improvedData['cargo']) && (empty($originalData['cargo']) || strlen($improvedData['cargo']) > strlen($originalData['cargo']))) {
        $originalData['cargo'] = $improvedData['cargo'];
    }
    
    // Experiência - manter estrutura mas melhorar descrições
    if (!empty($improvedData['experiencia']) && is_array($improvedData['experiencia'])) {
        foreach ($improvedData['experiencia'] as $index => $improvedExp) {
            if (isset($originalData['experiencia'][$index])) {
                // Manter cargo, empresa e período originais, mas usar descrição melhorada
                if (!empty($improvedExp['descricao'])) {
                    $originalData['experiencia'][$index]['descricao'] = $improvedExp['descricao'];
                }
            }
        }
    }
    
    // Habilidades - manter as originais, mas adicionar categorização se a IA sugerir
    if (!empty($improvedData['habilidades']) && is_array($improvedData['habilidades'])) {
        // Se a IA sugeriu habilidades mais específicas, usar uma combinação
        $combinedSkills = array_unique(array_merge($originalData['habilidades'], $improvedData['habilidades']));
        $originalData['habilidades'] = array_slice($combinedSkills, 0, 15); // Limitar a 15 habilidades
    }
    
    return $originalData;
}

// --- FLUXO PRINCIPAL ---
session_start();
if (!isset($_SESSION['usuario_id'])) {
    send_json_response(['error' => 'Acesso não autorizado. Por favor, faça login.'], 401);
}

// --- PROCESSAR DADOS DO FORMULÁRIO ---
$formData = $_POST;
$files = $_FILES['files'] ?? [];

// Verificar se temos dados do formulário
$hasFormData = !empty($formData['nome']) || !empty($formData['resumo']);
$hasFiles = !empty($files['tmp_name'][0]);

if (!$hasFormData && !$hasFiles) {
    send_json_response(['error' => 'Nenhuma informação ou ficheiro foi fornecido.'], 400);
}

// --- ESTRUTURAR DADOS DO FORMULÁRIO ---
$structuredData = [];

// Dados Pessoais
$structuredData['nome_completo'] = $formData['nome'] ?? null;
$structuredData['cargo'] = $formData['cargo_principal'] ?? null;

// Contato
$structuredData['contato'] = [
    'email' => $formData['email'] ?? null,
    'telefone' => $formData['telefone'] ?? null,
    'linkedin' => $formData['linkedin'] ?? null,
    'localizacao' => $formData['localizacao'] ?? null
];

// Resumo Profissional
$structuredData['resumo_profissional'] = $formData['resumo'] ?? null;

// Experiência Profissional
$structuredData['experiencia'] = [];
if (isset($formData['cargo']) && is_array($formData['cargo'])) {
    foreach ($formData['cargo'] as $index => $cargo) {
        if (!empty($cargo)) {
            $structuredData['experiencia'][] = [
                'cargo' => $cargo,
                'empresa' => $formData['empresa'][$index] ?? '',
                'periodo' => formatar_periodo(
                    $formData['inicio'][$index] ?? '',
                    $formData['fim'][$index] ?? ''
                ),
                'descricao' => $formData['descricao_experiencia'][$index] ?? ''
            ];
        }
    }
}

// Formação Acadêmica
$structuredData['educacao'] = [];
if (isset($formData['curso']) && is_array($formData['curso'])) {
    foreach ($formData['curso'] as $index => $curso) {
        if (!empty($curso)) {
            $structuredData['educacao'][] = [
                'curso' => $curso,
                'instituicao' => $formData['instituicao'][$index] ?? '',
                'periodo' => formatar_periodo(
                    $formData['inicio_curso'][$index] ?? '',
                    $formData['fim_curso'][$index] ?? ''
                )
            ];
        }
    }
}

// Habilidades
$structuredData['habilidades'] = [];
if (isset($formData['habilidades']) && is_array($formData['habilidades'])) {
    $structuredData['habilidades'] = array_filter($formData['habilidades']);
}

// Idiomas
$structuredData['idiomas'] = [];
if (isset($formData['idioma']) && is_array($formData['idioma'])) {
    foreach ($formData['idioma'] as $index => $idioma) {
        if (!empty($idioma)) {
            $structuredData['idiomas'][] = [
                'idioma' => $idioma,
                'nivel' => $formData['nivel_idioma'][$index] ?? ''
            ];
        }
    }
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

// --- PREPARAR DADOS PARA A IA (MELHORAR CONTEÚDO) ---
$useAI = $hasFiles || !empty($structuredData['resumo_profissional']) || !empty($structuredData['experiencia']);

if ($useAI) {
    // Construir prompt para a IA MELHORAR o conteúdo
    $userInput = "Dados do currículo para análise e melhoria:\n";
    $userInput .= "Nome: " . ($structuredData['nome_completo'] ?? 'Não informado') . "\n";
    $userInput .= "Email: " . ($structuredData['contato']['email'] ?? 'Não informado') . "\n";
    $userInput .= "Telefone: " . ($structuredData['contato']['telefone'] ?? 'Não informado') . "\n";
    $userInput .= "Localização: " . ($structuredData['contato']['localizacao'] ?? 'Não informado') . "\n";
    $userInput .= "LinkedIn: " . ($structuredData['contato']['linkedin'] ?? 'Não informado') . "\n";
    $userInput .= "Resumo atual: " . ($structuredData['resumo_profissional'] ?? 'Não informado') . "\n";
    
    if (!empty($structuredData['experiencia'])) {
        $userInput .= "\nExperiência Profissional:\n";
        foreach ($structuredData['experiencia'] as $exp) {
            $userInput .= "- {$exp['cargo']} na {$exp['empresa']} ({$exp['periodo']})\n";
            if (!empty($exp['descricao'])) {
                $userInput .= "  Descrição atual: {$exp['descricao']}\n";
            }
        }
    }
    
    if (!empty($structuredData['educacao'])) {
        $userInput .= "\nFormação Acadêmica:\n";
        foreach ($structuredData['educacao'] as $edu) {
            $userInput .= "- {$edu['curso']} na {$edu['instituicao']} ({$edu['periodo']})\n";
        }
    }
    
    if (!empty($structuredData['habilidades'])) {
        $userInput .= "\nHabilidades: " . implode(', ', $structuredData['habilidades']) . "\n";
    }
    
    if (!empty($structuredData['idiomas'])) {
        $userInput .= "\nIdiomas:\n";
        foreach ($structuredData['idiomas'] as $idioma) {
            $userInput .= "- {$idioma['idioma']} ({$idioma['nivel']})\n";
        }
    }

    $prompt = "Aja como um especialista em Recursos Humanos e redator profissional de currículos. 

**SUA TAREFA:** Analise as informações do currículo fornecidas e MELHORE significativamente o conteúdo, especialmente:
1. O RESUMO PROFISSIONAL - torne-o mais impactante, profissional e alinhado com o cargo desejado
2. As DESCRIÇÕES DE EXPERIÊNCIA - reescreva usando linguagem corporativa, verbos de ação e destacando conquistas
3. As HABILIDADES - organize e categorize de forma mais profissional
4. Sugira um CARGO apropriado se não estiver claro

**DIRETRIZES:**
- Use linguagem profissional e persuasiva
- Destaque conquistas e resultados mensuráveis
- Use verbos de ação no passado (coordenou, implementou, aumentou, etc.)
- Mantenha a veracidade das informações, apenas aprimorando a forma
- Seja específico e evite clichês

**FORMATO DE RESPOSTA:** Apenas JSON com a seguinte estrutura:
{
  \"nome_completo\": \"string\",
  \"cargo\": \"string\",
  \"contato\": {
    \"email\": \"string\", 
    \"telefone\": \"string\", 
    \"linkedin\": \"string\", 
    \"localizacao\": \"string\"
  },
  \"resumo_profissional\": \"string\",
  \"experiencia\": [
    {
      \"cargo\": \"string\",
      \"empresa\": \"string\", 
      \"periodo\": \"string\",
      \"descricao\": \"string\"
    }
  ],
  \"educacao\": [
    {
      \"curso\": \"string\",
      \"instituicao\": \"string\", 
      \"periodo\": \"string\"
    }
  ],
  \"habilidades\": [\"string\"],
  \"idiomas\": [
    {
      \"idioma\": \"string\", 
      \"nivel\": \"string\"
    }
  ]
}

**DADOS PARA MELHORIA:**
{$userInput}";

    $parts = [['text' => $prompt]];
    
    // Adicionar ficheiros se existirem
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
    $data = [
        'contents' => [['parts' => $parts]], 
        'generationConfig' => [
            'response_mime_type' => 'application/json', 
            'temperature' => 0.7, // Aumentado para mais criatividade
            'topP' => 0.8,
            'topK' => 40
        ]
    ];

    // --- ENVIAR REQUISIÇÃO ---
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, 
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'], 
        CURLOPT_POST => true, 
        CURLOPT_POSTFIELDS => json_encode($data), 
        CURLOPT_SSL_VERIFYPEER => true, 
        CURLOPT_TIMEOUT => 60,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
    ]);
    
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // --- TRATAR RESPOSTA DA API ---
    $aiFailed = false;
    if ($curlError) {
        error_log("Erro na API Gemini: " . $curlError);
        $aiFailed = true;
    } elseif ($httpcode >= 400) {
        error_log("API Gemini retornou erro HTTP: " . $httpcode . " - Response: " . $response);
        $aiFailed = true;
    } else {
        $responseData = json_decode($response, true);
        $jsonStringFromAI = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? null;
        
        if ($jsonStringFromAI !== null) {
            $aiStructuredData = json_decode($jsonStringFromAI, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // COMBINAR DADOS: Manter estrutura do formulário mas usar conteúdo melhorado da IA
                $structuredData = merge_improved_data($structuredData, $aiStructuredData);
                error_log("IA aplicou melhorias no currículo com sucesso");
            } else {
                error_log("JSON malformado da IA: " . $jsonStringFromAI);
                $aiFailed = true;
            }
        } else {
            error_log("Resposta da IA vazia: " . $response);
            $aiFailed = true;
        }
    }
    
    if ($aiFailed) {
        error_log("Usando dados originais do formulário devido a falha na IA");
    }
}

$safeData = sanitize_cv_data($structuredData);

// --- GERAR PDFs E PRÉ-VISUALIZAÇÕES ---
$tempDir = 'temp_cvs';
if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);
$uniqueId = session_id() . '_' . time();
$generatedFiles = [];
$templates = [
    'Moderno' => 'create_template_moderno', 
    'Minimalista' => 'create_template_minimalista', 
    'Profissional' => 'create_template_profissional', 
    'Corporativo' => 'create_template_corporativo', 
    'Criativo' => 'create_template_criativo'
];

try {
    foreach ($templates as $name => $functionName) {
        if (function_exists($functionName)) {
            $pdf = call_user_func($functionName, $safeData);

            $baseFileName = "cv_{$uniqueId}_" . strtolower(str_replace(' ', '_', $name));
            $filePath = __DIR__ . "/{$tempDir}/{$baseFileName}.pdf";
            $fileUrl = "{$tempDir}/{$baseFileName}.pdf";

            $pdf->Output($filePath, 'F');

            $previewUrl = null;

            $generatedFiles[] = [
                'template_name' => "Modelo {$name}", 
                'url' => $fileUrl, 
                'preview_url' => $previewUrl
            ];
        }
    }
} catch (Exception $e) {
    send_json_response(['error' => 'Ocorreu um erro interno ao gerar os ficheiros PDF. Detalhes: ' . $e->getMessage()], 500);
}

// --- RESPOSTA FINAL ---
send_json_response(['files' => $generatedFiles]);