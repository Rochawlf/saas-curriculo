<?php
    // ARQUIVO: api/login.php

    // 1. INICIAR A SESSÃO
    // É crucial iniciar a sessão ANTES de qualquer output (echo, print, etc.)
    session_start();

    // Define que a resposta será em formato JSON
    header('Content-Type: application/json');

    // Inclui a classe de conexão
    require_once '../class/conexao.class.php';

    // Função para enviar respostas JSON e encerrar o script
    function json_response($success = false, $message = '', $data = [], $http_code = 400) {
        http_response_code($http_code);
        echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
        exit;
    }

    // 2. VERIFICAR O MÉTODO DA REQUISIÇÃO
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(false, 'Método não permitido.', [], 405);
    }

    // 3. VALIDAR OS DADOS DE ENTRADA
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        json_response(false, 'Email e senha são obrigatórios.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(false, 'Formato de e-mail inválido.');
    }

 try {
        // Obter a conexão com banco de dados
        $conexao = new Conexao();       // 1. Cria o objeto da sua classe
        $pdo = $conexao->getConexao();  // 2. Pega a conexão PDO de dentro do objeto


        // 4. BUSCAR O USUÁRIO PELO E-MAIL
        $sql = "SELECT id, nome_completo, senha_hash FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $usuario = $stmt->fetch();

        // 5. VERIFICAR SE O USUÁRIO EXISTE E SE A SENHA ESTÁ CORRETA
        // password_verify() é a função que compara a senha pura com o hash
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            
            // 6. LOGIN BEM-SUCEDIDO! INICIAR A SESSÃO DO USUÁRIO.
            
            // Regenera o ID da sessão para prevenir ataques de fixação de sessão.
            session_regenerate_id(true);
            
            // Armazena os dados do usuário na sessão.
            // Convertemos o ID binário para texto para ser mais fácil de usar.
            $_SESSION['usuario_id'] = bin2hex($usuario['id']); // Usamos bin2hex para o ID binário
            $_SESSION['usuario_nome'] = $usuario['nome_completo'];
            $_SESSION['logado'] = true;

            // Prepara os dados para enviar de volta ao frontend
            $user_data = [
                'nome' => $usuario['nome_completo']
            ];

            json_response(true, 'Login realizado com sucesso!', $user_data, 200);

        } else {
            // Se o usuário não foi encontrado ou a senha está incorreta
            json_response(false, 'Email ou senha inválidos.', [], 401); // 401 Unauthorized
        }

    } catch (PDOException $e) {
        // Em produção, é melhor logar o erro do que exibi-lo
        // error_log($e->getMessage());
        json_response(false, 'Erro no banco de dados: ' . $e->getMessage(), [], 500);
    }