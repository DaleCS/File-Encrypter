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
require_once("algorithm.php");

// Set up session
setupSession();

// Connect to database (Function is found in utilityFunctions.php)
$connection = connectToDatabase($dbHN, $dbUN, $dbPW, $db);

/* ----- Page main code ----- */

displayHomePage();

if (isLoggedIn() == true) {
    if (verifySessionIntegrity() == true) {
        echo displayPosts($connection);
    } else {
        terminationAlert();
        destroy_session_and_data();
    }
}

if (isset($_POST["postButtonPressed"])) {
    if (isLoggedIn()) {
        storePostToDatabase($connection);
    }
}

$connection->close();

echo file_get_contents("./html/ownership.html");

/* ----- Page functions ----- */

function storePostToDatabase($conn) {
    if ($_FILES && $_FILES['fileUpload']['type'] == "text/plain") {
        $file = $_FILES['fileUpload']['name'];
        move_uploaded_file($_FILES['fileUpload']['tmp_name'], $file);

        validateThenUploadUserInput($conn, file_get_contents($file));
    } else if (isset($_POST["textareaPost"])) {
        validateThenUploadUserInput($conn, $_POST["textareaPost"]);
    }
}

// Server-side validation for file upload 
function validateThenUploadUserInput($conn, $content) {
    $sanitizedContent = sanitizeMySQL($conn, $content);
    $sanitizedActivity = isset($_POST["activitySelect"]) ? sanitizeMySQL($conn, $_POST["activitySelect"]) : "";
    $sanitizedAlgorithm = isset($_POST["algorithmSelect"]) ? sanitizeMySQL($conn, $_POST["algorithmSelect"]) : "";
    $sanitizedTitle = isset($_POST["titlePost"]) ? sanitizeMySQL($conn, $_POST["titlePost"]) : "";
    $sanitizedKey = isset($_POST["keyPost"]) ? sanitizeMySQL($conn, $_POST["keyPost"]) : "";

    date_default_timezone_set("America/Los_Angeles");
    $timestamp = date("m/d/Y") . " at " . date("h:ia");
    
    $author_email = $_SESSION["email"];

    if (validateActivity($sanitizedActivity) && validateAlgorithm($sanitizedAlgorithm) && validateTextContent($content) && validateKeyOrRot($sanitizedKey)) {
        $cryptoContent = applyCrypto($sanitizedActivity, $sanitizedAlgorithm, $sanitizedContent, $sanitizedKey);

        $stmt = $conn->prepare("INSERT INTO posts VALUES(?,?,?,?,?,?)");
        $stmt->bind_param('ssssss', $author_email, $sanitizedTitle, $sanitizedAlgorithm, $sanitizedActivity, $timestamp, $cryptoContent);
        $stmt->execute();
        $stmt->close();
        echo "<meta http-equiv='refresh' content='0'>";
    } else {
        echo "Error: Invalid user input";
    }
}

// Server-side validation for activity select menu
function validateActivity($activity) {
    switch ($activity) {
        case "encrypt":
        case "decrypt":
            return true;
            break;
        default:
            return false;
    }
}

// Server-side validation for algorithm select menu
function validateAlgorithm($algorithm) {
    switch ($algorithm) {
        case "simple_substitution":
        case "double_transposition":
        case "rc4":
            return true;
            break;
        default:
            return false;
    }
}

// Server-side validation for post title input
function validateTitle($title) {
    if (strlen($title) > 0 && $title.trim() != "") {
        return true;
    } else {
        return false;
    }
}

// Validates user input for the text submitted (file upload or textarea) (Also accepts whitespace-only fields. Whitespace also gets encrypted)
function validateTextContent($content) {
    if (strlen($content) > 0) {
        return true;
    } else {
        return false;
    }
}


// Server-side validates the key submitted by user
function validateKeyOrRot($key) {
    if (strlen($key) <= 0) {
        echo "Key is empty";
        return false;
    }

    if (isset($_POST["algorithmSelect"]) && $_POST["algorithmSelect"] == "simple_substitution") {
        $keyRegex = "/[\d]+/";
        if (preg_match($keyRegex, $key)) {
            return true;
        } else {
            echo "Invalid key";
            return false;
        }
    } else {
        return true;
    }
}

function applyCrypto($activity, $algorithm, $content, $key) {
    switch ($algorithm) {
        case "simple_substitution":
            if ($activity == "encrypt") {
                return encryptRot($key, $content);
            } else if ($activity == "decrypt") {
                return decryptRot($key, $content);
            }
            break;
        case "double_transposition":
            if ($activity == "encrypt") {
                return encryptDoubleTransposition($key, $key, $content);
            } else if ($activity == "decrypt") {
                return decryptDoubleTransposition($key, $key, $content);
            }
            break;
        case "rc4":
            return rc4($key, $content);
            break;
        default:
            return "";
            break;
    }
}

// Displays previous posts that the user has submitted
function displayPosts($conn) {
    $postsMarkup = "";

    $email = $_SESSION["email"];

    $readQuery = "SELECT * FROM posts WHERE author_email='$email'";
    $readResult = $conn->query($readQuery);

    if (!$readResult)
        die($conn->error);
    
    $rows = $readResult->num_rows;
    if ($rows > 0) {
        for ($i = 0; $i < $rows; ++$i) {
            $readResult->data_seek($i);
            $row = $readResult->fetch_array(MYSQLI_ASSOC);

            $fetchedTitle = $row["title"];
            $fetchedContent = $row["content"];
            $fetchedEncryption = $row["encryption"];
            $fetchedTimestamp = $row["timestamp"];

            $postsMarkup .= generateSinglePostMarkup($fetchedTitle, $fetchedContent, $fetchedEncryption, $fetchedTimestamp);
        }
    }
    $readResult->free();
    return <<< EOT
        <div class="page-margins">
            $postsMarkup
        </div>
EOT;
}

// Generates markup for a single post that will be displayed
function generateSinglePostMarkup($title, $content, $encryption, $timestamp) {
    return <<< EOT
        <div class="d-flex row justify-content-center align-items-center mt-40">
            <div class="d-flex column align-items-center justify-content-center main-background-color border-radius-10 shadow w-100 p-10">
                <h3 class="dark-background-color border-radius-5 m-0 p-5">$title</h3>
                <p class="dark-background-color border-radius-5 m-0 mt-10 p-5">$content</p>
                <div class="d-flex row justify-content-space-between mt-10 w-100">
                    <p class="dark-background-color border-radius-5 m-0 p-5">Encrypted with $encryption</p>
                    <p class="dark-background-color border-radius-5 m-0 p-5">Posted on $timestamp</p>
                </div>
            </div>
        </div>
EOT;
}

// Alerts the user that their session is over due to security breach attempt
function terminationAlert() {
    echo <<< EOT
        <script>
            alert("An error has occured and you have been logged out");
        </script>
EOT;
}

/* ----- Page markup ----- */

function displayHomePage() {
    echo file_get_contents("./html/header.html");

    if (logoutPressed()) {
        destroy_session_and_data();
    }

    $navbar = displayNavbar();
    $inputBox = file_get_contents("./html/inputUI.html");

    echo <<< EOT
        <body>
            $navbar
            <div class="d-flex row justify-content-center align-items-center">
                <p id="largeFontInfo">Encrypt/Decrypt your .txt files!</p>
            </div>
            <div class="page-margins">
                $inputBox
                <div class="d-flex row justify-content-center align-items-center mt-20">
                    <p id="postErrors" class="error-color bold white-space-prewrap"></p>
                </div>
            </div>
        </body>
    EOT;
}
?>