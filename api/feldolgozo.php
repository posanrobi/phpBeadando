<?php
$filename = __DIR__ . "/../password.txt";

$decoded_text = '';
$line_start = 0;

//require "../vendor/autoload.php";
require __DIR__ . '/../vendor/autoload.php';


if (file_exists($filename)) {
    $text = file_get_contents($filename);

    if ($text !== false) {
        $key = array(5, -14, 31, -9, 3);

        // Visszafejtés
        $key_index = 0;

        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];
            $offset = $key[$key_index];
            $decoded_char = chr(ord($char) - $offset);

            $key_index = ($key_index + 1) % 5;

            if ($char === "\n") {
                $key_index = 0;
                $decoded_char = "<br>";
            }

            $decoded_text .= $decoded_char;
        }

        $submittedUsername = $_POST["username"];
        $submittedPassword = $_POST["password"];

        $userEntries = explode("<br>", $decoded_text);
        $isMatch = false;
        $isUsernameCorrect = false;
        $isPasswordCorrect = false;

        foreach ($userEntries as $userEntry) {
            
            $parts = explode("*", $userEntry);
            if (count($parts) === 2) {
                $username = trim($parts[0]);
                $password = trim($parts[1]);
            

            
            if ($submittedUsername === $username && $submittedPassword === $password) {
                $isMatch = true;
                break;
            } elseif ($submittedUsername === $username) {
                $isUsernameCorrect = true;
            } elseif ($submittedPassword === $password) {
                $isPasswordCorrect = true;
            }
        }
        }

        if ($isMatch) {
            echo "Sikeres bejelentkezés!" . "<br>";

            $color = getFillColor($submittedUsername);

            if ($color !== null) {
                echo "A felhasználó színe: $color";
                
                if ($color === "piros") {
                    echo '<style>body { background-color: red; }</style>';
                }
                else if ($color === "zold") {
                    echo '<style>body { background-color: green; }</style>';
                }
                else if ($color === "sarga") {
                    echo '<style>body { background-color: yellow; }</style>';
                }
                else if ($color === "kek") {
                    echo '<style>body { background-color: blue; }</style>';
                }
                else if ($color === "fekete") {
                    echo '<style>body { background-color: black; color: white; }</style>';
                }
                else if ($color === "feher"){
                    echo '<style>body { background-color: white; }</style>';
                }
            }

        } else {
            if (!$isUsernameCorrect && !$isPasswordCorrect) {
                header('Location: ../errorUsernameAndPassword.html');   
            } elseif (!$isMatch) {
                if ($isUsernameCorrect) {
                    header('Location: ../errorPassword.html');
                }
                if ($isPasswordCorrect) {
                    header('Location: ../errorUsername.html');
                }
            }
        }

        
    } else {
        echo "Hiba a fájl olvasása során.";
    }
} else {
    echo "A fájl nem található.";
}


/*function getFillColor($inputUsername) {

    //$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    //$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../'); 
    //$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    //$dotenv->load();

    /*
//---------------------------------

    $servername = $_ENV["DB_SERVERNAME"];
    $username = $_ENV["DB_USERNAME"];
    $password = $_ENV["DB_PASSWORD"];
    $dbname = $_ENV["DB_NAME"];

    $conn = new mysqli($servername, $username, $password, $dbname, 3306);

    if ($conn->connect_error) {
        die("Kapcsolat sikertelen: " . $conn->connect_error);
    }

    //szín lekérdezése
    $sql = "SELECT Titkos FROM tabla WHERE Username = '$inputUsername'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["Titkos"];
    }

    $conn->close();
    return null;
    //--------------------------
 
        $servername = $_ENV["DB_SERVERNAME"];
        $username = $_ENV["DB_USERNAME"];
        $password = $_ENV["DB_PASSWORD"];
        $dbname = $_ENV["DB_NAME"];
    
        // Establishing a connection to the database
        //$conn = new mysqli($servername, $username, $password, $dbname, 3306);
        //$conn = new mysqli($_ENV["DB_SERVERNAME"], $_ENV["DB_USERNAME"], $_ENV["DB_PASSWORD"], $_ENV["DB_NAME"]);
        $conn = new mysqli(null, $username, $password, $dbname, 0, $servername);
    

        // Check for a successful connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

    
        try {
            // Query to retrieve the 'Titkos' column based on the 'Username'
            $sql = "SELECT Titkos FROM tabla WHERE Username = '$inputUsername'";
            $result = $conn->query($sql);
    
            // Check if the query was successful
            if ($result) {
                // Check if any rows were returned
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    return $row["Titkos"];
                } else {
                    // No matching rows found
                    return null;
                }
            } else {
                throw new Exception("Query failed: " . $conn->error);
            }
        } catch (Exception $e) {
            // Handle exceptions, log errors, or return an appropriate value
            die("Error: " . $e->getMessage());
        } finally {
            // Always close the database connection, whether the operation was successful or not
            $conn->close();
        }

}*/



function getFillColor($inputUsername) {
    $servername = $_ENV["DB_SERVERNAME"];
    $username = $_ENV["DB_USERNAME"];
    $password = $_ENV["DB_PASSWORD"];
    $dbname = $_ENV["DB_NAME"];

    try {
        // Establishing a connection to the database
        //$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn = new PDO("mysql:host=$servername;dbname=$dbname;port=3306", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Query to retrieve the Titkos column for the given username
        $stmt = $conn->prepare("SELECT Titkos FROM tabla WHERE Username = :username");
        $stmt->bindParam(':username', $inputUsername);
        $stmt->execute();

        // Check if any rows are returned
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row["Titkos"];
        }

        return null;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    } finally {
        if ($conn) {
            $conn = null; // Close the connection
        }
    }
}
