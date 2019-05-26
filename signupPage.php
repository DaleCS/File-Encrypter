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

displaySignupPage();

if (isLoggedIn() == true) {
    // If user is already logged in, redirect them to home page
    header("Location: " . $dbBN . "/Decryptoid/home.php");
    die();
} else if (isset($_POST["signupButtonPressed"])) {
    $sanitizedEmailInput = sanitizeMySQL($connection, $_POST['emailSignup']);
    $sanitizedUsernameInput = sanitizeMySQL($connection, $_POST['usernameSignup']);
    $sanitizedPasswordInput = sanitizeMySQL($connection, $_POST["passwordSignup"]);

    if (validateUserInputSignup($sanitizedEmailInput, $sanitizedUsernameInput, $sanitizedPasswordInput)) {
        if (registerUser($connection, $sanitizedEmailInput, $sanitizedUsernameInput, $sanitizedPasswordInput)) {
            header("Location: " . $dbBN . "/Decryptoid/loginPage.php");
            die();
        } else {
            echo <<< EOT
                <script
                    >emailAlreadyInUse();
                </script>
EOT;
        }
    }
}

$connection->close();

/* ----- Page functions ----- */

// Server-side validation for user input for sign up
// (The functions used are found in utilityFunctions.php)
function validateUserInputSignup($email, $username, $password) {
    if (validateEmail($email) && validateUsername($username) && validatePassword($password)) {
        return true;
    } else {
        return false;
    }
}

// Registers users credentials to the database
function registerUser($conn, $email, $username, $password) {
    $registerInfoQuery = "SELECT * FROM users WHERE email='$email'";
    $registerInfoResult = $conn->query($registerInfoQuery);
    if (!$registerInfoResult)
        die($conn->error);
    
    $registerQueryRows = $registerInfoResult->num_rows;
    if ($registerQueryRows == 0) {
        $prefixSalt = generateRandomSalt32();
        $postfixSalt = generateRandomSalt32();

        // Hashing password with 2 salts
        $sanitizedAndSaltedPassword = $prefixSalt . $password . $postfixSalt;
        $hash = hash('ripemd128', $sanitizedAndSaltedPassword);
        
        $stmt = $conn->prepare("INSERT INTO users VALUES(?,?,?,?,?)");
        $stmt->bind_param('sssss', $email, $username, $hash, $prefixSalt, $postfixSalt);
        $stmt->execute();
        $stmt->close();
        return true;
    } else {
        return false;
    }
    $registerInfoQuery->free();
}

/* ----- Page markup ----- */

function displaySignupPage() {
    echo file_get_contents("./html/header.html");

    if (logoutPressed()) {
        destroy_session_and_data();
    }

    $navbar = displayNavbar();
    $signupBox = file_get_contents("./html/signupBox.html");
    $ownership = file_get_contents("./html/ownership.html");

    echo <<< EOT
        <body>
            $navbar
            <div class="d-flex row justify-content-center align-items-center">
                <p id="largeFontInfo">Sign up</p>
            </div>
            <div class="page-margins">
                <div class="d-flex row justify-content-center align-items-center">
                    <div class="dark-box pl-10 pr-10">
                        $signupBox
                    </div>
                </div>
                <div class="d-flex row justify-content-center align-items-center mt-20">
                    <p id="signupErrors" class="error-color bold white-space-prewrap"></p>
                </div>
            </div>
            $ownership
        </body>
    EOT;
}
?>