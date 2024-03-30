<?php

namespace app\models;

use \PDO;

if (file_exists(__DIR__ . "/../../config/server.php")) {
    require_once __DIR__ . "/../../config/server.php";
}

class mainModel
{

    private $server = DB_SERVER;
    private $db = DB_NAME;
    private $user = DB_USER;
    private $pass = DB_PASS;

    protected function connect()
    {
        $connection = new PDO("mysql:host=" . $this->server . ";dbname=" . $this->db, $this->user, $this->pass);
        $connection->exec("SET CHARACTER SET utf8");
        return $connection;
    }

    // Ejecutar consulta
    protected function runQuery($query)
    {
        $sql = $this->connect()->prepare($query);
        $sql->execute();
        return $sql;
    }

    // Limpiar cadena de inyección SQL
    public function clearString($string)
    {
        $words = ["<script>", "</script>", "<script src", "<script type=", "SELECT * FROM", "SELECT ", " SELECT ", "DELETE FROM", "INSERT INTO", "DROP TABLE", "DROP DATABASE", "TRUNCATE TABLE", "SHOW TABLES", "SHOW DATABASES", "<?php", "?>", "--", "^", "<", ">", "==", "=", ";", "::"];

        $string = trim($string);
        $string = stripslashes($string);

        foreach ($words as $word) {
            $string = str_ireplace($word, "", $string);
        }

        $string = trim($string);
        $string = stripslashes($string);

        return $string;
    }

    protected function verifyData($filter, $string)
    {
        if (preg_match("/^" . $filter . "$/", $string)) {
            return false;
        } else {
            return true;
        }
    }

    protected function saveData($table, $data)
    {
        $query = "INSERT INTO $table (";
        $C = 0;

        foreach ($data as $key) {
            if ($C >= 1) {
                $query .= ",";
            }
            $query .= $key["campo_nombre"];
            $C++;
        }

        $query .= ") VALUES(";
        $C = 0;

        foreach ($data as $key) {
            if ($C >= 1) {
                $query .= ",";
            }
            $query .= $key["campo_marcador"];
            $C++;
        }

        $query .= ")";

        $sql = $this->connect()->prepare($query);       // Prepara la consulta para poder ejecutarla

        foreach ($data as $key) {
            $sql->bindParam($key["campo_marcador"], $key["campo_valor"]);       // Cambia el marcador por su valor real
        }

        $sql->execute();

        return $sql;
    }

    public function selectData($type, $table, $field, $id)
    {
        $type = $this->clearString($type);
        $table = $this->clearString($table);
        $field = $this->clearString($field);
        $id = $this->clearString($id);

        if ($type == "Unico") {
            $sql = $this->connect()->prepare("SELECT * FROM $table WHERE $field = :ID");
            $sql->bindParam(":ID", $id);
        } elseif ($type == "Normal") {
            $sql = $this->connect()->prepare("SELECT $field FROM $table");
        }

        $sql->execute();
        return $sql;
    }

    protected function updateData($table, $data, $condition)
    {
        $query = "UPDATE $table SET ";
        $C = 0;

        foreach ($data as $key) {
            if ($C >= 1) {
                $query .= ",";
            }
            $query .= $key["campo_nombre"] . " = " . $key["campo_marcador"];
            $C++;
        }

        $query .= " WHERE " . $condition["condicion_campo"] . "=" . $condition["condicion_marcador"];
        $sql = $this->connect()->prepare($query);

        foreach ($data as $key) {
            $sql->bindParam($key["campo_marcador"], $key["campo_valor"]);
        }

        $sql->bindParam($condition["condicion_marcador"], $condition["condicion_valor"]);
        $sql->execute();
        return $sql;
    }

    protected function deleteData($table, $field, $id)
    {
        $sql = $this->connect()->prepare("DELETE FROM $table WHERE $field=:id");
        $sql->bindParam(":id", $id);
        $sql->execute();
        return $sql;
    }

    // $page = página actual
    protected function tablePagination($page, $pageNumbers, $url, $buttons)
    {
        $table = '<nav class="pagination is-centered is-rounded" role="navigation" aria-label="pagination">';

        if ($page <= 1) {
            $table .= '
            <a class="pagination-previous is-disabled" disabled >Anterior</a>
	        <ul class="pagination-list">
	        ';
        } else {
            $table .= '
            <a class="pagination-previous" href="' . $url . ($page - 1) . '/">Anterior</a>
	        <ul class="pagination-list">
	        <li><a class="pagination-link" href="' . $url . '1/">1</a></li>
	        <li><span class="pagination-ellipsis">&hellip;</span></li>
	        ';
        }

        $ci = 0;        // Contador de iteración

        for ($i = $page; $i <= $pageNumbers; $i++) {
            if ($ci >= $buttons) {
                break;
            }

            if ($page == $i) {
                $table .= '<li><a class="pagination-link is-current" href="' . $url . $i . '/">' . $i . '</a></li>';
            } else {
                $table .= '<li><a class="pagination-link" href="' . $url . $i . '/">' . $i . '</a></li>';
            }

            $ci++;
        }

        if ($page == $pageNumbers) {
            $table .= '
            </ul>
            <a class="pagination-next is-disabled" disabled >Siguiente</a>
            ';
        } else {
            $table .= '
            <li><span class="pagination-ellipsis">&hellip;</span></li>
            <li><a class="pagination-link" href="' . $url . $pageNumbers . '/">' . $pageNumbers . '</a></li>
        </ul>
        <a class="pagination-next" href="' . $url . ($page + 1) . '/">Siguiente</a>
        ';
        }

        $table .= '</nav>';
        return $table;
    }
}
