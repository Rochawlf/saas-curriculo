<?php
// 1. Iniciar a sessão
// É crucial que isto seja a primeira coisa no ficheiro.
session_start();

// 2. Incluir a sua classe de conexão com a base de dados
// Certifique-se de que o caminho está correto.
require_once 'class/conexao.class.php';

// 3. Verificar se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 4. Obter e limpar os dados do formulário
    $email = $_POST['email'] ?? null;
    $senha = $_POST['senha'] ?? null;

    // Verifica se email e senha foram preenchidos
    if (empty($email) || empty($senha)) {
        // Se não, volta para o index com uma mensagem de erro
        header('Location: index.php?error=campos_vazios');
        exit();
    }

    try {
        // 5. Conectar-se à base de dados
        $conexao = new Conexao();
        $pdo = $conexao->getConexao();

        // 6. Preparar a consulta SQL de forma SEGURA com prepared statements
        // Isto previne injeção de SQL.
        $sql = "SELECT id, nome_completo, senha_hash FROM usuarios WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        
        // 7. Associar o parâmetro de email e executar a consulta
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        // 8. Obter o resultado
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // 9. Verificar se o utilizador existe E se a senha está correta
        // password_verify() compara a senha digitada com o hash guardado na base de dados.
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            
            // 10. SUCESSO! Guardar os dados do utilizador na sessão
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome_completo'];

            // 11. Redirecionar para o dashboard
            header('Location: dashboard.php');
            exit();

        } else {
            // Se o utilizador não existe ou a senha está incorreta, volta para o index com erro.
            header('Location: index.php?error=login_invalido');
            exit();
        }

    } catch (PDOException $e) {
        // Em caso de erro na base de dados, redireciona com uma mensagem genérica.
        // O ideal é logar o erro $e->getMessage() num ficheiro de log no servidor.
        header('Location: index.php?error=db_error');
        exit();
    }

} else {
    // Se alguém tentar aceder a login.php diretamente sem ser via POST, redireciona para o index.
    header('Location: index.php');
    exit();
}
?>
