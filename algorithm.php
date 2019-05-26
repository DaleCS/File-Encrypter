<?php

/*
    Unfinished code for decryptoid encryption functionality
*/
$cipherText = encryptRot(1, "odjfasdjfjsdmf dkaowefoiklnlkwadnawmdaklwmd, nioawndioanwndlkanwdolkanwdkl");
echo decryptRot(1, $cipherText);

function encryptRot($rot, $plainText) {
    for ($i = 0; $i < strlen($plainText); $i++) {
        $asciiNum = ord($plainText[$i]);
        $asciiNum += $rot;

        if ($asciiNum > 126) {
            $asciiNum -= 95;
        }

        $plainText[$i] = chr($asciiNum);
    }
    
    return $plainText;
}

function decryptRot($rot, $cipherText) {
    for ($i = 0; $i < strlen($cipherText); $i++) {
        $asciiNum = ord($cipherText[$i]);
        $asciiNum -= $rot;

        if ($asciiNum <= 31) {
            $asciiNum += 95;
        }

        $cipherText[$i] = chr($asciiNum);
    }
    
    return $cipherText;
}

function encryptDoubleTransposition($key1, $key2, $plainText) {
    return encryptTranspose($key2, encryptTranspose($key1, $plainText)); 
}

function decryptDoubleTransposition($key1, $key2, $cipherText) {
    return decryptTranspose($key1, decryptTranspose($key2, $cipherText));
}

function encryptTranspose($key, $plainText) {
    $keyLen = strlen($key);
    $textLen = strlen($plainText);

    if ($textLen % $keyLen != 0) {
        $padding = $keyLen - ($textLen % $keyLen);
        $plainText = str_pad($plainText, $textLen + ($padding));
    }

    $colArr = array_fill(0, $keyLen, "");

    for ($i = 0, $j = 0; $i < $textLen; $i++, $j++) {
        $colArr[$j] .= $plainText[$i];

        if ($j >= $keyLen - 1) {
            $j = -1;
        }
    }

    $keyArr = array();
    for ($i = 0; $i < $keyLen; $i++) {
        $keyArr[(string)$i] = $key[$i];
    }

    asort($keyArr);

    $cipherText = "";
    foreach($keyArr as $key => $value) {
        $cipherText .= $colArr[$key];
    }

    return trim($cipherText);
}

function decryptTranspose($key, $cipherText) {
    $len = strlen($key);
    $rows = (strlen($cipherText) / $len) + (strlen($cipherText) % $len != 0 ? 1 : 0);

    $colArr = array_fill(0, $len, "");

    $keyArr = array();
    for ($i = 0; $i < $len; $i++) {
        $keyArr[(string)$i] = $key[$i];
    }

    asort($keyArr);

    $c_cipherText = $cipherText;
    foreach($keyArr as $key => $value) {
        if ($rows >= strlen($c_cipherText)) {
            $colArr[(int)$key] = substr($c_cipherText, 0);
        } else {
            $colArr[(int)$key] = substr($c_cipherText, 0, $rows);
            $c_cipherText = substr($c_cipherText, $rows);
        }
    }

    $plainText = "";
    for ($i = 0, $j = 0, $k = 0; $i < strlen($cipherText); $i++, $j++) {
        if ($k < strlen($colArr[$j])) {
            $plainText .= $colArr[$j][$k];
            if ($j == $len - 1) {
                $j = -1;
                $k++;
            }
        }
    }

    return $plainText;
}

function printArray($arr) {
    echo "[";
    for ($i = 0; $i < sizeof($arr); $i++) {
        if ($i < sizeof($arr) - 1) {
            echo "'" . $arr[$i] . "', ";
        } else {
            echo "'" . $arr[$i] . "'";
        }
    }
    echo "]";
}

?>