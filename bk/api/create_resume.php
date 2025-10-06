<?php
// ATIVAR A EXIBIÇÃO DE TODOS OS ERROS PARA DEPURACAO
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicia o buffer de saída para capturar qualquer saida inesperada (incluindo erros)
ob_start();

session_start();
// Inclui a conexão com o banco de dados
require_once '../../class/conexao.class.php';

// Função para carregar variáveis de ambiente do arquivo .env
function loadEnv($path)
{
    if (!file_exists($path)) {
        // Usando uma forma mais segura de morrer para não quebrar o JSON
        throw new Exception(".env file not found at path: " . realpath(dirname($path)) . '/' . basename($path));
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// ---- Bloco TRY/CATCH para capturar exceções e enviar como JSON ----
try {
    // Carrega o arquivo .env
    loadEnv(__DIR__ . '/../../.env');

    // Pega a Chave da API do ambiente
    $apiKey = getenv('GEMINI_API_KEY');
    if (empty($apiKey)) {
        throw new Exception("A chave da API (GEMINI_API_KEY) não foi encontrada no arquivo .env");
    }
    $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=';

    // Garante que o cabeçalho da resposta seja JSON
    header('Content-Type: application/json');

    // Verifica se o método é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido.');
    }

    // Pega os dados brutos do POST
    $data = json_decode(file_get_contents('php://input'), true);

    // Construção do Prompt para a IA
    $prompt = "
    Você é um especialista em recrutamento (RH) e um web designer sênior.
    Sua tarefa é criar um currículo profissional, moderno e visualmente atraente em HTML a partir das informações brutas fornecidas pelo usuário.
    **Instruções Críticas:**
    1.  **Melhore o Conteúdo:** Analise a experiência, habilidades e formação. Reescreva as descrições para que soem mais profissionais, focadas em resultados e conquistas. Use verbos de ação e termos da indústria.
    2.  **Gere um HTML Completo:** O resultado final deve ser um ÚNICO arquivo HTML.
    3.  **CSS Interno:** TODO o CSS deve estar dentro de uma tag `<style>` no `<head>` do HTML. Não use links para folhas de estilo externas ou CSS inline nos elementos. Crie um design limpo, com boa tipografia e espaçamento.
    4.  **Estrutura Lógica:** Organize o currículo nas seções clássicas: Contato, Resumo Profissional, Experiência, Formação Acadêmica, Habilidades.
    5.  **Seja Criativo:** Crie um layout único e profissional.
    --- INFORMAÇÕES DO USUÁRIO ---
    Nome Completo: " . ($data['nome'] ?? '') . "
    Email: " . ($data['email'] ?? '') . "
    Telefone: " . ($data['telefone'] ?? '') . "
    Endereço: " . ($data['endereco'] ?? '') . "
    LinkedIn: " . ($data['linkedin'] ?? '') . "
    Resumo/Objetivo Profissional: " . ($data['resumo'] ?? '') . "
    Experiência Profissional:";
    if (isset($data['experiencias']) && is_array($data['experiencias'])) {
        foreach ($data['experiencias'] as $exp) {
            $prompt .= "\n- Cargo: " . ($exp['cargo'] ?? '') . " Empresa: " . ($exp['empresa'] ?? '') . " Período: " . ($exp['periodo'] ?? '') . " Descrição: " . ($exp['descricao'] ?? '');
        }
    }
    $prompt .= "\nFormação Acadêmica:";
    if (isset($data['formacoes']) && is_array($data['formacoes'])) {
        foreach ($data['formacoes'] as $form) {
            $prompt .= "\n- Curso: " . ($form['curso'] ?? '') . " Instituição: " . ($form['instituicao'] ?? '') . " Período: " . ($form['periodo'] ?? '');
        }
    }
    $prompt .= "\nHabilidades: " . ($data['habilidades'] ?? '') . "\n--- FIM DAS INFORMAÇÕES ---";

    // Configuração da requisição para a API da IA
    $headers = ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey];
    $body = ['model' => 'gpt-3.5-turbo', 'messages' => [['role' => 'user', 'content' => $prompt]], 'temperature' => 0.7];

    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 120,
    ]);

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) throw new Exception('Erro na chamada da API: ' . $error);
    if ($httpcode !== 200) throw new Exception('A API retornou um erro: ' . $response);

    $result = json_decode($response, true);
    $htmlContent = $result['choices'][0]['message']['content'] ?? '';
    $htmlContent = preg_replace(['/^```html\s*/', '/\s*```$/'], '', $htmlContent);

    $resumesDir = '../../resumes/';
    if (!is_dir($resumesDir)) mkdir($resumesDir, 0777, true);
    if (!is_writable($resumesDir)) throw new Exception("O diretório 'resumes' não tem permissão de escrita.");
    
    $fileName = 'curriculo_' . uniqid() . '.html';
    $filePath = $resumesDir . $fileName;

    if (file_put_contents($filePath, $htmlContent)) {
        // Limpa qualquer saída anterior antes de enviar o JSON final
        ob_end_clean();
        echo json_encode(['success' => true, 'file_path' => 'resumes/' . $fileName]);
    } else {
        throw new Exception('Falha ao salvar o arquivo do currículo.');
    }

} catch (Exception $e) {
    // Captura qualquer erro do bloco try e envia como JSON
    $php_error_output = ob_get_clean(); // Pega qualquer warning/notice que ocorreu
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_php_output' => $php_error_output, // Envia os erros capturados
        'trace' => $e->getTraceAsString()
    ]);
}

exit; // Garante que nada mais seja executado


?>

