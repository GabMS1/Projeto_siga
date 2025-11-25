<?php
// C:\xampp\htdocs\Projeto_siga\DAO\Conexao.php

class Conexao {
    private static $conn = null;

    public static function get_connection() {
        if (self::$conn === null) {
            $host = 'db';
            $usuario = 'siga_user';
            $senha = 'S!g@!2$';
            $banco = 'projeto_siga';

            // Desativa a exibição de erros para a conexão para tratar manualmente
            mysqli_report(MYSQLI_REPORT_OFF);

            self::$conn = new mysqli($host, $usuario, $senha, $banco);

            if (self::$conn->connect_error) {
                // Loga o erro em vez de expor ao usuário
                error_log("Erro de conexão com o banco de dados: " . self::$conn->connect_error);
                // Retorna null para que a camada de serviço possa tratar a falha
                return null;
            }
            self::$conn->set_charset("utf8");
        }
        return self::$conn;
    }
}
?>