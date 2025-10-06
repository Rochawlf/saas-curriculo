
// // --- Carregamento da Chave API (sem alterações) ---
// $env = parse_ini_file('../.env');
// $apiKey = $env['GEMINI_API_KEY'] ?? null;
// if (empty($apiKey)) {
//     http_response_code(500);
//     echo json_encode(['success' => false, 'message' => 'Erro: Chave de API não encontrada.']);
//     exit;
// }
<?php
session_start();
header('Content-Type: application/json');

// 1. Segurança: Verifique se o usuário está logado
if (!isset($_SESSION['logado']) || !$_SESSION['logado']) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado.']);
    exit;
}

// 2. Insira sua Chave de API do Google Gemini
$env = parse_ini_file('../.env');
$apiKey = $env['GEMINI_API_KEY'] ?? null;
if (empty($apiKey)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A chave da API de IA não foi configurada no servidor.']);
    exit;
}

// 3. Inclua a biblioteca FPDF
require('../fpdf/fpdf.php');

// 4. Colete os dados do formulário
$nome = $_POST['nome'] ?? 'Nome não preenchido';
$email = $_POST['email'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$linkedin = $_POST['linkedin'] ?? '';
$resumo = $_POST['resumo'] ?? '';
$experiencias = $_POST['experiencia'] ?? [];
$educacoes = $_POST['educacao'] ?? [];
$habilidades = $_POST['habilidades'] ?? '';
$idiomas = $_POST['idiomas'] ?? [];
$projetos = $_POST['projetos'] ?? [];

// 5. Construa o prompt para a IA
$prompt = "Aja como um recrutador especialista e escritor de currículos. Sua tarefa é pegar os dados brutos a seguir e reescrevê-los para serem profissionais, concisos e focados em resultados. Para cada experiência e projeto, transforme a descrição em 3-4 bullet points de alto impacto. O resultado DEVE ser um objeto JSON ÚNICO, sem nenhum texto ou formatação extra (como ```json). A estrutura deve ser a seguinte:
{
  \"resumo_otimizado\": \"...\",
  \"experiencias_otimizadas\": [
    {
      \"cargo\": \"...\",
      \"empresa\": \"...\",
      \"periodo\": \"...\",
      \"descricao\": \"- Bullet point 1...\\n- Bullet point 2...\"
    }
  ],
  \"projetos_otimizados\": [
    {
      \"titulo\": \"...\",
      \"link\": \"...\",
      \"descricao\": \"- Bullet point 1...\\n- Bullet point 2...\"
    }
  ]
}

--- DADOS BRUTOS ---
Resumo: $resumo
Habilidades: $habilidades
Experiências: " . json_encode($experiencias, JSON_UNESCAPED_UNICODE) .
"Projetos: " . json_encode($projetos, JSON_UNESCAPED_UNICODE) .
"--- FIM DOS DADOS ---";


// 6. NOVA ABORDAGEM: Usar file_get_contents para evitar problemas de cURL
$url = '[https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=](https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=)' . $apiKey;
$requestBody = ['contents' => [['parts' => [['text' => $prompt]]]]];

$options = [
    'http' => [
        'header'  => "Content-Type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($requestBody),
        'ignore_errors' => true // Permite capturar a resposta mesmo em caso de erro
    ],
    // Contexto SSL para tentar resolver problemas de certificado automaticamente
    'ssl' => [
        'verify_peer' => true,
        'verify_peer_name' => true,
    ],
];

$context  = stream_context_create($options);
// O @ suprime warnings caso a conexão falhe, pois vamos tratar o erro manualmente
$response = @file_get_contents($url, false, $context);

// Tratamento de erro para esta nova abordagem
if ($response === false) {
    $error = error_get_last();
    http_response_code(500);
    $user_message = "Erro crítico de conexão de rede. ";
    $error_details = $error['message'] ?? 'Não foi possível conectar à API.';

    // Dá uma dica mais direcionada ao usuário
    if (strpos(strtolower($error_details), 'ssl') !== false || strpos(strtolower($error_details), 'certificate') !== false) {
        $user_message .= "Falha de SSL. Isso confirma que a configuração do 'curl.cainfo' no php.ini é necessária.";
    } else if (strpos(strtolower($error_details), 'ipv6') !== false) {
        $user_message .= "O problema de IPv6 persiste de forma profunda no ambiente.";
    } else {
         $user_message .= "Detalhe: " . $error_details;
    }
    
    echo json_encode(['success' => false, 'message' => $user_message]);
    exit;
}

// Extrai o código de status HTTP dos headers da resposta
$http_code = 0;
if (isset($http_response_header)) {
    preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
    if ($match) {
        $http_code = (int)$match[1];
    }
}

if ($http_code !== 200) {
    http_response_code(502); // Bad Gateway
    error_log("Erro na API Gemini - HTTP $http_code: $response");
    echo json_encode(['success' => false, 'message' => "Erro ao conectar com a IA (Código HTTP: $http_code). Detalhes: " . $response]);
    exit;
}

$responseData = json_decode($response, true);

// ... (O resto do código para processar a resposta e gerar o PDF permanece o mesmo)
if (empty($responseData['candidates'])) {
    if (isset($responseData['promptFeedback']['blockReason'])) {
        http_response_code(400); // Bad Request
        $reason = $responseData['promptFeedback']['blockReason'];
        echo json_encode(['success' => false, 'message' => "O conteúdo foi bloqueado pela IA. Motivo: $reason. Tente reformular o texto."]);
    } else {
        http_response_code(500);
        error_log("Resposta da API Gemini vazia ou inválida: " . $response);
        echo json_encode(['success' => false, 'message' => 'A IA não retornou uma resposta válida.']);
    }
    exit;
}

$aiContentText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? null;

$jsonStart = strpos($aiContentText, '{');
$jsonEnd = strrpos($aiContentText, '}');
if ($jsonStart === false || $jsonEnd === false) {
    http_response_code(500);
    error_log("JSON não encontrado na resposta da IA: " . $aiContentText);
    echo json_encode(['success' => false, 'message' => 'A IA retornou uma resposta em formato não esperado (sem JSON).']);
    exit;
}
$aiContentText = substr($aiContentText, $jsonStart, $jsonEnd - $jsonStart + 1);

$aiContentJson = json_decode(trim($aiContentText), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    error_log("Erro ao decodificar JSON da IA: " . json_last_error_msg() . " | Resposta: " . $aiContentText);
    echo json_encode(['success' => false, 'message' => 'A IA retornou um formato de dados inesperado (JSON inválido).']);
    exit;
}

class PDF extends FPDF {
    function Header() {}
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 10, 'Gerado por Curriculo IA - ' . date('Y'), 0, 0, 'C');
    }
    function SectionTitle($title) {
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(230, 230, 230);
        $this->SetTextColor(0);
        $this->Cell(0, 8, mb_convert_encoding($title, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L', true);
        $this->Ln(4);
    }
    function BodyText($text) {
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(50, 50, 50);
        $this->MultiCell(0, 5, mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8'));
        $this->Ln(2);
    }
}

$pdf = new PDF();
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 24);
$pdf->SetTextColor(0);
$pdf->Cell(0, 10, mb_convert_encoding($nome, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(80, 80, 80);
$contact_info = implode(' | ', array_filter([$email, $telefone, $linkedin]));
$pdf->Cell(0, 5, mb_convert_encoding($contact_info, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
$pdf->Ln(10);

$pdf->SectionTitle('Resumo Profissional');
$pdf->BodyText($aiContentJson['resumo_otimizado'] ?? $resumo);
$pdf->Ln(5);

if (!empty($aiContentJson['experiencias_otimizadas'])) {
    $pdf->SectionTitle('Experiência Profissional');
    foreach ($aiContentJson['experiencias_otimizadas'] as $exp) {
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 6, mb_convert_encoding($exp['cargo'] ?? '', 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, mb_convert_encoding($exp['periodo'] ?? '', 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 6, mb_convert_encoding($exp['empresa'] ?? '', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $pdf->BodyText($exp['descricao'] ?? '');
        $pdf->Ln(3);
    }
}

if (!empty($aiContentJson['projetos_otimizados'])) {
    $pdf->SectionTitle('Projetos e Portfólio');
    foreach ($aiContentJson['projetos_otimizados'] as $proj) {
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 6, mb_convert_encoding($proj['titulo'] ?? '', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        if(!empty($proj['link'])){
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(74, 144, 226);
            $pdf->Cell(0, 5, mb_convert_encoding($proj['link'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L', false, $proj['link']);
            $pdf->SetTextColor(50, 50, 50);
        }
        $pdf->BodyText($proj['descricao'] ?? '');
        $pdf->Ln(3);
    }
}

if (!empty($educacoes)) {
    $pdf->SectionTitle('Educação');
    foreach ($educacoes as $edu) {
        if(empty($edu['curso'])) continue;
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 6, mb_convert_encoding($edu['curso'], 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, mb_convert_encoding($edu['periodo'] ?? '', 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->BodyText($edu['instituicao'] ?? '');
        $pdf->Ln(2);
    }
}
$pdf->Ln(3);

$pdf->SectionTitle('Habilidades e Idiomas');
$pdf->SetFont('Arial', 'B', 10);
$pdf->MultiCell(0, 5, mb_convert_encoding("Habilidades:", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 10);
$pdf->BodyText($habilidades);
$pdf->Ln(2);

if (!empty($idiomas)) {
    $idiomasText = '';
    foreach ($idiomas as $idioma) {
        if (!empty($idioma['idioma']) && !empty($idioma['nivel'])) {
            $idiomasText .= $idioma['idioma'] . ' (' . $idioma['nivel'] . ')   ';
        }
    }
    if(!empty(trim($idiomasText))) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->MultiCell(0, 5, mb_convert_encoding("Idiomas:", 'ISO-8859-1', 'UTF-8'));
        $pdf->SetFont('Arial', '', 10);
        $pdf->BodyText(trim($idiomasText));
    }
}

$filename = 'curriculo_' . $_SESSION['usuario_id'] . '_' . time() . '.pdf';
$filepath = '../resumes/' . $filename;
$pdf->Output('F', $filepath);

echo json_encode(['success' => true, 'file_url' => 'resumes/' . $filename]);
?>

