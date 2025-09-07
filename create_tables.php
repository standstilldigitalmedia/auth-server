<?php
    require_once("config.php");
    require_once("SSDMDatabase.php");
    $player_auth_sql = "CREATE TABLE player_auth (
        player_id VARCHAR(16) PRIMARY KEY,
        user_name VARCHAR(30) NOT NULL,
        display_name VARCHAR(30) NOT NULL,
        email VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        account_created DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_login DATETIME DEFAULT CURRENT_TIMESTAMP,
        auth TINYINT UNSIGNED
    )";

    $sessions_sql = "CREATE TABLE sessions (
        player_id VARCHAR(16) PRIMARY KEY,
        client_id VARCHAR(255),
        session_type TINYINT UNSIGNED,
        session_ticket VARCHAR(16),
        expiration_date DATETIME     
    )";
        $mysqli = SSDMDatabase::connect();
        if(!$mysqli)
        {
            return false;
        }
        $stmt = $mysqli->prepare($sessions_sql);
        if(!$stmt)
        {
            $mysqli->close();
            SSDMDatabase::write_db_error("Prepare failed. " . $sql);
            return false;
        }
        
        if(!$stmt->execute())
        {
            $mysqli->close();
            SSDMDatabase::write_db_error("Execute failed. " . $sql);
            return false;
        }

?>