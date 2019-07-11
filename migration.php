<?php

require_once "config.php";

try {
    $conn = new PDO("mysql:host=".DB_HOST, DB_USER, DB_PASS);

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "CREATE DATABASE ".DB_NAME;

    $conn->exec($sql);
    echo "Database created successfully\n";
    }
catch(PDOException $e)
    {
    echo $sql . "\n" . $e->getMessage();
    }

$conn = null;

try {
    $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE transaction (
    id BIGINT(6) PRIMARY KEY, 
    amount INT(15) NOT NULL,
    status VARCHAR(10) NOT NULL,
    timestamp TIMESTAMP NOT NULL,
    bank_code VARCHAR(20) NOT NULL,
    account_number VARCHAR(15) NOT NULL,
    beneficiary_name VARCHAR(25) NOT NULL,
    remark VARCHAR(50) NOT NULL,
    receipt VARCHAR(150) ,
    time_served timestamp NOT NULL,
    fee int(5) NOT NULL
    )";

    $conn->exec($sql);
    echo "Table transaction created successfully";
    }
catch(PDOException $e)
    {
    echo $sql . "\n" . $e->getMessage();
    }

$conn = null;
?> 
