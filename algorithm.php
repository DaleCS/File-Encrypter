<?php
function rc4($key, $plainText) {
    $s = array();
    $k = array();
    for ($i = 0; $i < 256; $i++) {
        $s[$i] = $i;
        $k[$i] = ord($key[$i % strlen($key)]);
    }

    $j = 0;
    for ($i = 0; $i < 256; $i++) {
        $j = ($j + $s[$i] + $k[$i]) % 256;
        $temp = $s[$i];
        $s[$i] = $s[$j];
        $s[$j] = $temp;
    }

    $i = 0;
    $j = 0;

    $cipherText = "";
    for ($a = 0; $a < strlen($plainText); $a++) {
        $i = ($i + 1) % 256;
        $j = ($j + $s[$i]) % 256;

        $temp = $s[$i];
        $s[$i] = $s[$j];
        $s[$j] = $temp;

        $byte = $s[($s[$i] + $s[$j]) % 256];
        $cipherText .= $plainText[$a] ^ chr($byte);
    }

    $s = array();
    return $cipherText;
}

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