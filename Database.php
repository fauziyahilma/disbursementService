<?php

class Database {

    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
 
    private $dbh;
    private $stmt;

    public function __construct()
    {
        $dsn='mysql:host='.$this->host.';dbname='.$this->dbname;
        $option = [
            PDO::ATTR_PERSISTENT => TRUE,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $option);
        }
        catch(PDOException $e) {
            die($e->getMessage());
        }
    }

    public function query($query)
    {
        $this->stmt = $this->dbh->prepare($query);
    }

    public function bind($param, $val, $type=null) 
    {
        if(is_null($type)){
            switch(true){
                case is_int($val):
                $type = PDO::PARAM_INT;
                break;

                case is_bool($val):
                $type = PDO::PARAM_BOOL;
                break;

                case is_null($val):
                $type = PDO::PARAM_NULL;
                break;

                default:
                $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $val, $type);

    }

    public function execute()
    {
        $this->stmt->execute();
    }

    public function resultSet()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function row()
    {
        return $this->stmt->rowCount();
    }

}

?>
