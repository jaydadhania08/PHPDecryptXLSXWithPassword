# PHPDecryptXLSXWithPassword
Decrypt encrypted (for which password is already known) XLSX files with PHP.

**Update:** It also works for DOCX and PPTX files (can someone suggest a better name for this repo?).

I have not tested for XLS/DOC/PPT files yet.

Dependencies:
- [OLE](https://github.com/pear/OLE/)
- [PEAR](https://pear.php.net/package/PEAR/)

I have made a small change in [`System.php`](lib/System.php#L20) file of PEAR package - commented out `require_once 'Console/Getopt.php';` because this script does not need it, so we can avoid downloading [Console_Getopt](https://github.com/pear/Console_Getopt/) package.

# Usage:

```
<?php

require_once('PHPDecryptXLSXWithPassword.php');

$encryptedFilePath = '../path/to/encrypted/file.xlsx';
$password = 'mypassword'; // password to "open" the file
$decryptedFilePath = '../path/to/decrypted/file.xlsx';

decrypt($encryptedFilePath, $password, $decryptedFilePath);
```

# Acknowledgement
- This repo is made possible by Python's [msoffcrypto-tool](https://pypi.org/project/msoffcrypto-tool/), which is used for decrypting various files with a known password.
- I have also used encryption/decryption logic from Python's [cryptography](https://cryptography.io/en/latest/hazmat/primitives/) module, which is also used in the `msoffcrypto-tool`.

# Caution!

This will work if the input file is encrypted, and the password is correct. However, I have developed this just for a quick dirty task and there is no error checking whatsoever. **Please DO NOT use in production.**

**Note:** This is my first github repo, so any suggestions (regarding code or documentation or whatever) are more than welcome.
