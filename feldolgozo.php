<?php

$filename = "password.txt";
$decoded_text = '';
$line_start = 0;
require 'vendor/autoload.php';

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
                header('Location: errorUsernameAndPassword.html');         
            } elseif (!$isMatch) {
                if ($isUsernameCorrect) {
                    header('Location: errorPassword.html');
                }
                if ($isPasswordCorrect) {
                    header('Location: errorUsername.html');
                }
            }
        }

        
    } else {
        echo "Hiba a fájl olvasása során.";
    }
} else {
    echo "A fájl nem található.";
}


function getFillColor($inputUsername) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $servername = $_ENV["DB_SERVERNAME"];
    $username = $_ENV["DB_USERNAME"];
    $password = $_ENV["DB_PASSWORD"];
    $dbname = $_ENV["DB_NAME"];

    $conn = new mysqli($servername, $username, $password, $dbname);

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
}