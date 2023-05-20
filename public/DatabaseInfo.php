
<?php

class DatabaseInfo
{
    public $serverName; 
    public $userName; 
    public $password; 
    public $dbName; 
}

function getDatabaseInfo()
{
    $result = new DatabaseInfo(); 
    $result->serverName = "localhost";
    $result->userName = "nf-dienstplan-user";
    $result->password = "DesN3wForce,Ne,Des1sSch0EchtFet7.";
    $result->dbName = "NewForceRoster"; 
    return $result; 
}

?>