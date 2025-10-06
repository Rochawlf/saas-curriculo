<?php 


    // ----- BLOCO DE DEBUG (REMOVER EM PRODUÇÃO) -----
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    // ------------------------------------------------

    // Define que a resposta será em formato JSON
    header('Content-Type: application/json');


    // Inclui a classe de conexâo
    require_once '../class/conexao.class.php';


    // Função para enviar respostas JSON e encerrar o script
    function json_response($success = false, $message = '', $http_code = 400) {
        http_response_code($http_code);
        // <-- CORREÇÃO 2: 'sucess' mudado para 'success'
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }

    // VALIDAR OS DADOS DE ENTRADA
    $nome_completo = $_POST['nome_completo'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($nome_completo) || empty($email) || empty($senha)) {
        json_response(false, 'Todos os campos são obrigatórios.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(false, 'Formato de e-mail inválido.');
    }

    if (strlen($senha) < 6) {
        json_response(false, 'A senha deve ter pelo menos 6 caracteres.');
    }

    try {
         // Obter a conexão com banco de dados
        $conexao = new Conexao();       // 1. Cria o objeto da sua classe
        $pdo = $conexao->getConexao();  // 2. Pega a conexão PDO de dentro do objeto


        // VERIFICAR SE O EMAIL JÁ EXISTE
        $sql_check = "SELECT id FROM usuarios WHERE email = :email";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->bindParam(':email', $email);
        $stmt_check->execute();

        if ($stmt_check->fetch()) {
            json_response(false, 'Este e-mail já está cadastrado.', 409); // 409 Conflict
        }
        
        // CRIPTOGRAFAR A SENHA
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        // INSERIR O NOVO USUÁRIO NO BANCO
        
        // <-- CORREÇÃO 1: A linha abaixo estava faltando!
        $sql = "INSERT INTO usuarios (id, nome_completo, email, senha_hash) VALUES (UUID(), :nome, :email, :senha)";
        
        $stmt_insert = $pdo->prepare($sql);

        // Associa as variáveis PHP aos placeholders
        $stmt_insert->bindParam(':nome', $nome_completo);
        $stmt_insert->bindParam(':email', $email);
        $stmt_insert->bindParam(':senha', $senha_hash);

        if ($stmt_insert->execute()) {
            // RETORNAR SUCESSO
            json_response(true, 'Usuário cadastrado com sucesso!', 201); // 201 Created
        } else {
            json_response(false, 'Ocorreu um erro inesperado ao cadastrar o usuário.');
        }

    } catch (PDOException $e) {
        // Em produção, é melhor logar o erro do que exibi-lo
        // error_log($e->getMessage());
        json_response(false, 'Erro no banco de dados: ' . $e->getMessage(), 500);
    }