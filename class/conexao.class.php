<?php 

    class Conexao 
    {
        private $host = 'localhost';
        private $dbname = 'db_curriculo_ia';
        private $user = 'root';
        private $pass = '';

        protected $conn;

        public function __construct()
        {
            try 
            {
                $dsm = "mysql:host ={$this->host}; dbname={$this->dbname}";
                $this->conn = new PDO($dsm, $this->user, $this->pass);

                // ----------CONFIGURAÇÕES EXTRA DO PDO(MUITO IMPORTANTE)--------

                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e){
            
                die("Erro ao tentar se conectar: " . $e->getMessage());
                }
        
        }
        public function getConexao(){

            return $this->conn;    
        }          
    
    }