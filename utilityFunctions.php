<?php
/*
    Dale Christian Seen - 012151152
    CS174
    Decryptoid Final Project

    This file contains utility functions used to sanitize strings, destroy sessions, generate random salts,
    as well as any other functions that are used in multiple .php files. 
*/

// Called in all pages. 
function setupSession() {
    // Setup session timer to 1 hour
    ini_set("session.gc_maxlifetime", 60 * 60);
    session_start();

    // Check if current session is the first session
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id();
        $_SESSION['initiated'] = 1;
    }
}

// Connect to database and return database object
function connectToDatabase($dbHN, $dbUN, $dbPW, $db) {
    // Connect to database
    $connection = new mysqli($dbHN, $dbUN, $dbPW, $db);
    if ($connection->connect_error)
        die(mysql_fatal_error($connection->connect_error, $connection));

    return $connection;
}

// Sanitizes string using MySQL
function sanitizeMySQL($conn, $str) {
    $str = $conn->real_escape_string($str);
    $str = sanitizeString($str);
    return $str;
}

// Sanitizes string
function sanitizeString($str) {
    $str = stripslashes($str);
    $str = strip_tags($str);
    $str = htmlentities($str);
    return $str;
}

// Destroys current session and session data
function destroy_session_and_data() {
    $_SESSION = array();
    session_destroy();
}

// Generates a random salt that is 32 chars in length
function generateRandomSalt32() {
    $salt = "";
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()-=_+[]\;,./{}|:<>?";
    for ($i = 0; $i < 32; $i++) {
        $randomInt = rand(0, strlen($chars) - 1);
        $salt .= $chars[$randomInt]; 
    }
    return $salt;
}

// Verifies session integrity by checking if the user is using the same machine
function verifySessionIntegrity() {
    if (isset($_SESSION["check"]) 
    && $_SESSION["check"] == hash("ripemd128", $_SESSION["email"] . $_SESSION["username"] . $_SERVER["REMOTE_ADDR"] . $_SERVER["HTTP_USER_AGENT"])) {
        return true;
    }
    return false;
}

function isLoggedIn() {
    if (isset($_SESSION["check"])) {
        return true;
    }
    return false;
}

function logoutPressed() {
    return isset($_POST["logoutButtonPressed"]);
}

/* ----- Server-side validation ----- */

// Server-side validation for username input
function validateUsername($username) {
    $usernameRegex = "/[^\w\-]/";

    if (strlen($username) > 0 && trim($username) != "") {
        if (strlen($username) >= 4) {
            if (preg_match($usernameRegex, $username) == false) {
                return true;
            } else {
                echo "Username invalid: Illegal characters";
                return false;
            }
        } else {
            echo "Username invalid: < 4 characters";
            return false;
        }
    } else {
        echo "Username invalid: Empty string";
        return false;
    }
}

// Server-side validation for email input
function validateEmail($email) {
    $emailRegex = "/^[^\.](?!.*\.\.)[\w\.\-\+_]*[^\.@]@[^\.\-\_][\w\.\-\+_]+\.(?!.*web)[\w\.\-\+_\[\]]{2,}$/";

    if (strlen($email) > 0 && trim($email) != "") {
        if (preg_match($emailRegex, $email)) {
            return true;
        } else {
            echo "Email invalid: Invalid email format";
            return false;
        }
    } else {
        echo "Email invalid: Empty string";
        return false;
    }
}

// Server-side validation for password input
function validatePassword($password) {
    $passwordRegex1 = "/[a-z]/";
    $passwordRegex2 = "/[A-Z]/";
    $passwordRegex3 = "/[0-9]/";

    if (strlen($password) > 0) {
        if (strlen($password) >= 6) {
            if (preg_match($passwordRegex1, $password) && preg_match($passwordRegex2, $password) && preg_match($passwordRegex3, $password)) {
                return true;
            } else {
                echo "Password invalid: Lacks required characters";
                return false;
            }
        } else {
            echo "Password invalid: Password too short";
        }
    } else {
        echo "Password invalid: Empty string";
        return false;
    }
}

/* ----- Navbar markup ----- */

function displayNavbar() {
    if (isLoggedIn() && isset($_SESSION["username"])) {
        $username = $_SESSION["username"];
        return <<< EOT
            <div id="navbar">
                <div
                    class="d-flex row justify-content-space-between align-items-center page-margins"
                >
                    <h2 id="iconName" onClick="location.href='./home.php'">
                        Decryptoid
                    </h2>
                    <div class="m-0 p-0">
                        <form action="" method="post" class="d-flex row justify-content-center align-items-center m-0 p-0">
                            <h3 class="m-0 p-0 mr-20">Hi $username!</h3>
                            <input
                                name="logoutButtonPressed"
                                type="submit"
                                class="button"
                                value="Log out"
                            />
                        </form>
                    </div>
                </div>
            </div>
EOT;
    } else {
        return <<< EOT
            <div id="navbar">
                <div
                    class="d-flex row justify-content-space-between align-items-center page-margins"
                >
                    <h2 id="iconName" onClick="location.href='./home.php'">
                        Decryptoid
                    </h2>
                    <div class="m-0 p-0">
                        <button
                            class="button mr-10"
                            type="default"
                            onClick="location.href='./loginPage.php'"
                        >
                            Log in
                        </button>
                        <button
                            class="button"
                            type="default"
                            onClick="location.href='./signupPage.php'"
                        >
                            Sign up
                        </button>
                    </div>
                </div>
            </div>
EOT;
    }
}
?>