<?php
// 1. Incluir a sua classe de conexão
require_once 'class/conexao.class.php';

/**
 * Função para gerar um UUID v4 (ID único universal)
 * Essencial para criar o ID BINARY(16) de forma segura.
 * @return string O UUID em formato binário.
 */
function uuidv4() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    return $data;
}

// 2. Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 3. Obtém os dados do formulário
    $nome_completo = $_POST['nome_completo'] ?? null;
    $email = $_POST['email'] ?? null;
    $senha = $_POST['senha'] ?? null;
    $confirma_senha = $_POST['confirma_senha'] ?? null;

    // --- VALIDAÇÕES ---
    if (empty($nome_completo) || empty($email) || empty($senha) || empty($confirma_senha)) {
        header('Location: index.php?error=campos_vazios#register');
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: index.php?error=email_invalido#register');
        exit();
    }
    if (strlen($senha) < 6) {
        header('Location: index.php?error=senha_curta#register');
        exit();
    }
    if ($senha !== $confirma_senha) {
        header('Location: index.php?error=senhas_nao_conferem#register');
        exit();
    }

    try {
        // --- INTERAÇÃO COM A BASE DE DADOS ---
        $conexao = new Conexao();
        $pdo = $conexao->getConexao();

        // 4. Verifica se o email já existe
        $sql_check = "SELECT id FROM usuarios WHERE email = :email";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt_check->execute();

        if ($stmt_check->fetch()) {
            // Email já registado
            header('Location: index.php?error=email_existente#register');
            exit();
        }

        // 5. Se o email não existe, prossegue com o registo
        $id_binario = uuidv4();
        // **SEGURANÇA**: Cria um hash seguro da senha. NUNCA guarde senhas em texto simples.
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        $sql_insert = "INSERT INTO usuarios (id, nome_completo, email, senha_hash) VALUES (:id, :nome_completo, :email, :senha_hash)";
        $stmt_insert = $pdo->prepare($sql_insert);

        // Associa os parâmetros
        $stmt_insert->bindParam(':id', $id_binario, PDO::PARAM_LOB);
        $stmt_insert->bindParam(':nome_completo', $nome_completo, PDO::PARAM_STR);
        $stmt_insert->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt_insert->bindParam(':senha_hash', $senha_hash, PDO::PARAM_STR);

        // Executa a inserção
        if ($stmt_insert->execute()) {
            // Sucesso! Redireciona com uma mensagem de sucesso.
            header('Location: index.php?success=cadastro_sucesso');
            exit();
        } else {
            // Erro na execução do SQL
            header('Location: index.php?error=db_error#register');
            exit();
        }

    } catch (PDOException $e) {
        // Em caso de erro na conexão ou consulta, redireciona.
        // O ideal é logar o erro: error_log($e->getMessage());
        header('Location: index.php?error=db_error#register');
        exit();
    }
} else {
    // Se o acesso não for via POST, redireciona para o index.
    header('Location: index.php');
    exit();
}
?>
