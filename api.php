<?php

$host = "localhost";
$db = "databaseName";
$username = "username";
$password = "password";

// name of database table
$tablename = "tableName";

// table columns, id column needs to be first
$columns = [ "itemid", "columnOne", "language" ];

/*****************************************************************************/

try {
    $dbh = $conn = new PDO("mysql:dbname=$db;host=$host", $username, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    exit(1);
}

$requestBody = json_decode(file_get_contents('php://input'), true);

$columnCount = count($columns);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST request received ("https://example.ca/api/")
    try {
        // Construct INSERT query
        $stmtString = "INSERT INTO " . $tablename . " (";
        for ($i = 1; $i < $columnCount; $i++) {
            $stmtString .= $columns[$i] . ($i+1 == $columnCount ? "":", ");
        }
        $stmtString .=  ") VALUES (";
        for ($i = 1; $i < $columnCount; $i++) {
            $stmtString .= ":" . $columns[$i]
            . ($i+1 == $columnCount ? "":", ");
        }
        $stmtString .= ")";

        // prepare the statement
        $stmt = $dbh->prepare($stmtString);

        // bind values to statement
        for ($i = 1; $i < $columnCount; $i++) {
            $stmt->bindParam(":" . $columns[$i], $requestBody[$columns[$i]]);
        }

        // Execute query
        if ($stmt->execute()) {
            // Statement executed successfully
            // echo updated values
            $new_id = $dbh->lastInsertId();
            // set response code
            http_response_code(201);
        } else {
            // set response code
            http_response_code(400);
        }
    } catch (Exception $e) {
        // Error executing statement, set response code
        http_response_code(400);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT'
        && $_GET['id'] != "" && $_GET['id'] != null
) {
    // PUT request received with id ("https://example.ca/api/0")
    try {
        // Construct UPDATE query with id
        $stmtString = "UPDATE " . $tablename . " SET ";
        for ($i = 1; $i < $columnCount; $i++) {
            $stmtString .= $columns[$i] .  " = :" . $columns[$i]
                . ($i+1 == $columnCount ? " ":", ");
        }
        $stmtString .= "WHERE " . $columns[0] . " = :" . $columns[0];

        // prepare the statement
        $stmt = $dbh->prepare($stmtString);

        // bind values to statement
        for ($i = 1; $i < $columnCount; $i++) {
            $stmt->bindParam(":" . $columns[$i], $requestBody[$columns[$i]]);
        }

        // bind id to statement
        $stmt->bindParam(':' . $columns[0], $_GET['id']);

        // Execute query
        if ($stmt->execute()) {
            // Statement executed successfully, set response code
            http_response_code(204);
        } else {
            // set response code
            http_response_code(400);
        }
    } catch (Exception $e) {
        // Error executing statement, set response code
        http_response_code(400);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE'
        && $_GET['id'] != "" && $_GET['id'] != null
) {
    // DELETE request received with id ("https://example.ca/api/0")
    try {
        // Construct DELETE query with id
        $stmtString = "DELETE FROM " . $tablename
        . " WHERE " . $columns[0] . " = :" . $columns[0];

        // prepare the statement
        $stmt = $dbh->prepare($stmtString);

        // bind id to statement
        $stmt->bindParam(':' . $columns[0], $_GET['id']);

        // Execute query
        if ($stmt->execute()) {
            // Query executed successfully
            // set response code
            http_response_code(204);
        } else {
            // set response code
            http_response_code(400);
        }
    } catch (Exception $e) {
        // Error executing query, set response code
        http_response_code(400);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET'
          && ($_GET['id'] != null && $_GET['id'] != "")) {
    // GET request received with id ("https://example.ca/api/0")
    try {
        // Construct SELECT query with id
        $stmtString = "SELECT * FROM " . $tablename
        . " WHERE " . $columns[0] . " = :" . $columns[0];

        // prepare the statement
        $stmt = $dbh->prepare($stmtString);

        // bind id to statement
        $stmt->bindParam(':' . $columns[0], $_GET['id']);

        // Execute query
        if ($stmt->execute()) {
            // Query executed successfully, set response code
            http_response_code(200);

            if ($stmt->rowCount() > 0) {
                // Query returned results
                $resultArray = array();
                $tempArray = array();

                while ($row = $stmt->fetch()) {
                    // Add each row into results array
                    $tempArray = $row;
                    array_push($resultArray, $tempArray);
                }
                // set response code
                http_response_code(200);
                // set Content-Type header
                header('Content-Type: application/json');
                // echo the JSON encoded array
                echo json_encode($resultArray);
            } else {
                // No rows returned, set response code
                http_response_code(404);
            }
        } else {
            // set response code
            http_response_code(400);
        }
    } catch (Exception $e) {
        // Error executng query, set response code
        http_response_code(400);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // GET request received ("https://example.ca/api/")
    try {
        // Construct SELECT all query
        $stmt = $dbh->prepare("SELECT * FROM $tablename");

        // Execute query
        if ($stmt->execute()) {
            // Query executed successfully, set response code
            http_response_code(204);
            if ($stmt->rowCount() > 0) {
                // Query returned results
                $resultArray = array();
                $tempArray = array();

                while ($row = $stmt->fetch()) {
                    // Add each row into results array
                    $tempArray = $row;
                    array_push($resultArray, $tempArray);
                }
                // set response code
                http_response_code(200);
                // set Content-Type header
                header('Content-Type: application/json');
                // echo JSON encoded array
                echo json_encode($resultArray);
            } else {
                // No rows returned, set response code
                http_response_code(204);
            }
        } else {
            // set response code
            http_response_code(400);
        }
    } catch (Exception $e) {
        // Error executing query, set response code
        http_response_code(400);
    }
} else {
    // set response code
    http_response_code(400);
}
