<?php
// ARQUIVO: logout.php

// 1. INICIA OU RETOMA A SESSÃO EXISTENTE
// Para destruir uma sessão, primeiro precisamos acessá-la.
session_start();

// 2. LIMPA TODAS AS VARIÁVEIS DA SESSÃO
// Esvazia o array $_SESSION, removendo todos os dados como 'usuario_id', 'usuario_nome', etc.
$_SESSION = array();

// 3. DESTRÓI O COOKIE DA SESSÃO (BOA PRÁTICA)
// Se um cookie de sessão estiver sendo usado, a melhor forma de eliminá-lo
// é setar seu tempo de vida para uma data no passado.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. FINALMENTE, DESTRÓI A SESSÃO NO SERVIDOR
// Isso remove o arquivo da sessão do lado do servidor.
session_destroy();

// 5. REDIRECIONA PARA A PÁGINA INICIAL (index.php)
// Em vez de retornar um JSON, agora redirecionamos o navegador.
header('Location: index.php');

// Garante que nenhum outro código seja executado após o redirecionamento.
exit();
?>
