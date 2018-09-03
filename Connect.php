<?php

/**
 * Class Connect
 * Adatbázis kapcsolat letrehozását megvalósító osztály.
 */
class Connect
{
    /**
     * @var string
     */
    protected $userName;

    /**
     * @var string
     */
    protected $databseName;

    /**
     * @var string
     */
    protected $serverName;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var connection
     */
    protected $conn;

    /**
     * Connect constructor.
     * @param string $userName
     * @param string $databseName
     * @param string $serverName
     * @param string $password
     */
    public function __construct($serverName, $databseName, $userName, $password)
    {
        $this->userName = $userName;
        $this->databseName = $databseName;
        $this->serverName = $serverName;
        $this->password = $password;
    }

    /**
     * Sql kapcsolat inditása.
     */
    public function StartConnection()
    {
        $connectionInfo = array("UID" => $this->userName,
            "PWD" => $this->password,
            "Database" => $this->databseName);
        $this->conn = sqlsrv_connect($this->serverName, $connectionInfo);
        if ($this->conn === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        return $this->conn;
    }

    /**
     * Sql kapcsolat lezárása
     */
    public function StopConnection()
    {
        mssql_close($this->conn);
    }


}