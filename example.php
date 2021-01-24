<?php

require_once('PHPDecryptXLSXWithPassword.php');

$encryptedFilePath = 'encrypted.xlsx';
$password = 'testpass'; // password to "open" the XLSX file
$decryptedFilePath = 'decrypted.xlsx';

decrypt($encryptedFilePath, $password, $decryptedFilePath);