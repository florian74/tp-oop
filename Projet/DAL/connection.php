<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 23/03/2015
 * Time: 16:30
 */
class Connection {

    private $connection;
    private static $_instance = null;

    private function Connection()
    {
        $this->initConnection();
    }

    private function initConnection()
    {
        $servername = "localhost";
        $username = "root";
        $password = "014720";
        $dbname = "tp-oop";

        try {
            $this->connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            // set the PDO error mode to exception
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        }
        catch(PDOException $e)
        {
            echo  "<br>" . $e->getMessage();
        }
    }

    public static function getConnection() {
        if(is_null(self::$_instance)) {
            self::$_instance = new Connection();
        }

        return self::$_instance->connection;
    }

}


// Mysqli --> apparemment pas cool
/*$hostname = "localhost";
$user = "root";
$password = "014720";
$dbname = "tp-oop";

// Create connection
$conn = new mysqli($hostname, $user, $password, $dbname,3306);

if ($conn->connect_errno) {
    die("Connection failed: " . mysqli_connect_error());
}

echo $conn->host_info . "\n";


$res = $conn->query("SELECT * FROM feeds");

$res->data_seek(0);
while ($row = $res->fetch_assoc()) {
    echo " id = " . $row['id'] . "\n";
}*/




?>