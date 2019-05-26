<?php
/*
    Dale Christian Seen - 012151152
    CS174
    Decryptoid Final Project
*/

// Acquire Database Credentials
require_once("login.php");

// Acquire utility functions
require_once("utilityFunctions.php");

// Set up session
setupSession();

// Connect to database (Function is found in utilityFunctions.php)
$connection = connectToDatabase($dbHN, $dbUN, $dbPW, $db);

/* ----- Page main code ----- */

// Display page markup
displayLoginPage();

if (isLoggedIn() == true) {
    // If user is already logged in, redirect them to home page
    header("Location: " . $dbBN . "/Decryptoid/home.php");
    die();
} else if (isset($_POST["loginButtonPressed"])) {
    $sanitizedEmailInput = sanitizeMySQL($connection, $_POST["emailLogin"]);
    $sanitizedPasswordInput = sanitizeMySQL($connection, $_POST["passwordLogin"]);

    if (validateUserInput($sanitizedEmailInput, $sanitizedPasswordInput)) {
        if (authenticateUser($connection, $sanitizedEmailInput, $sanitizedPasswordInput)) {
            header("Location: " . $dbBN . "/Decryptoid/home.php");
            die();
        } else {
            echo <<< EOT
                <script>
                    incorrectEmailPassword();
                </script>
EOT;
        }
    }
}

$connection->close();

/* ----- Page functions ----- */

// Server-side validation for user input for login
// (The functions used are found in utilityFunctions.php)
function validateUserInput($email, $password) {
    if (validateEmail($email) && validatePassword($password)) {
        return true;
    } else {
        return false;
    }
}

// Authenticates users credentials using database
function authenticateUser($conn, $email, $password) {
    $loginInfoQuery = "SELECT hash,prefixsalt,postfixsalt,username FROM users WHERE email='$email'";
    $loginInfoResult = $conn->query($loginInfoQuery);
    if (!$loginInfoResult)
        die($conn->error);
    
    $loginQueryRows = $loginInfoResult->num_rows;
    if ($loginQueryRows != 0) {
        $loginInfoResult->data_seek(0);
        $loginQueryRow = $loginInfoResult->fetch_array(MYSQLI_ASSOC);
        $fetchedPassword = $loginQueryRow['hash'];
        $fetchedPreSalt = $loginQueryRow['prefixsalt'];
        $fetchedPostSalt = $loginQueryRow['postfixsalt'];
        
        $hashedPasswordInput = hash('ripemd128', $fetchedPreSalt . $password . $fetchedPostSalt);
        if ($hashedPasswordInput == $fetchedPassword) {
            $_SESSION["email"] = $email;
            $_SESSION["username"] = $loginQueryRow["username"];
            $_SESSION["check"] = hash("ripemd128", $_SESSION["email"] . $_SESSION["username"] . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
    $loginInfoResult->free();
}

/* ----- Page markup ----- */

function displayLoginPage() {
    echo file_get_contents("./html/header.html");

    if (logoutPressed()) {
        destroy_session_and_data();
    }

    $navbar = displayNavbar();
    $loginBox = file_get_contents("./html/loginBox.html");
    $ownership = file_get_contents("./html/ownership.html");

    echo <<< EOT
        <body>
            $navbar
            <div class="d-flex row justify-content-center align-items-center">
                <p id="largeFontInfo">Log in</p>
            </div>
            <div class="page-margins">
                <div class="d-flex row justify-content-center align-items-center">
                    <div class="dark-box pl-10 pr-10">
                    $loginBox
                    </div>
                </div>
                <div class="d-flex row justify-content-center align-items-center mt-20">
                    <p id="loginErrors" class="error-color bold white-space-prewrap"></p>
                </div>
            </div>
            $ownership
        </body>
EOT;
}
?>