
<?php
    $dbuser="root";
    $dbpass="";
    $host="localhost";
    $db="database_bnhs";
    $mysqli=new mysqli($host,$dbuser, $dbpass, $db);

    try {
        $pdo = new PDO("mysql:host=$host; dbname=$db", $dbuser, $dbpass);
    } catch (PDOException $e) {
        die("Database Connection Failed:" . $e->getMessage());
    }
?>