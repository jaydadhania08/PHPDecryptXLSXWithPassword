<?php

// add lib folder to include path -- required by some files in OLE package
ini_set('include_path', ini_get('include_path') . ';lib');

require_once('OLE.php');

function getDataByName($oleObj, $name)
{
	$objArray = array_filter($oleObj -> _list, function($obj) use ($name) {
		return $name === $obj -> Name;
	});

	if(0 === count($objArray) > 0)
	{
		return false;
	}

	return $oleObj -> getData(array_values($objArray)[0] -> No, 0, -1);
}

function decrypt($encryptedFilePath, $password, $decryptedFilePath)
{
	$oleObj = new OLE();
	$oleObj -> read($encryptedFilePath);
	
	// parse info from XML
	{
		$xmlstr = substr(getDataByName($oleObj, 'EncryptionInfo'), 8);
		$xml =  new SimpleXMLElement($xmlstr);

		$info = [];

		$info['keyDataSalt'] = base64_decode((string) $xml -> keyData -> attributes() -> saltValue);

		$passwordAttributes = $xml -> xpath("//*[@spinCount]")[0] -> attributes();

		$info['passwordSalt'] = base64_decode((string) $passwordAttributes -> saltValue);
		$info['passwordHashAlgorithm'] = (string) $passwordAttributes -> hashAlgorithm;
		$info['encryptedKeyValue'] = base64_decode((string) $passwordAttributes -> encryptedKeyValue);
		$info['spinValue'] = (int) $passwordAttributes -> spinCount;
		$info['passwordKeyBits'] = (int) $passwordAttributes -> keyBits;
	}

	// get key
	{
		$h = hash('SHA512', $info['passwordSalt'] . iconv('UTF-8', 'UTF-16LE', $password), true);

		for($i = 0; $i < $info['spinValue']; $i++)
		{
			$h = hash('SHA512', pack('I', $i) . $h, true);
		}

		$blockKey = hex2bin('146e0be7abacd0d6');

		$h_final = hash('SHA512', $h . $blockKey, true);

		$encryptionKey = substr($h_final, 0, intval($info['passwordKeyBits'] / 8));

		$key = openssl_decrypt($info['encryptedKeyValue'], 'aes-256-cbc', $encryptionKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $info['passwordSalt']);
	}

	// decrypt data
	{
		// get encrypted payload
		$payload = getDataByName($oleObj, 'EncryptedPackage');

		// determine total size of decrypted data
		$totalSize = unpack('I', substr($payload, 0, 4))[1];

		// actual payload
		$payload = substr($payload, 8);

		$SEGMENT_LENGTH = 4096;

		$decrypted = '';

		for($i = 0; ; $i++)
		{
			$start = $i * $SEGMENT_LENGTH;
			$end = $start + $SEGMENT_LENGTH;

			$payloadChunk = substr($payload, $start, $SEGMENT_LENGTH);

			$saltWithBlockKey = $info['keyDataSalt'] . pack('I', $i);

			$iv = hash('SHA512', $saltWithBlockKey, true);

			$iv = substr($iv, 0, 16);

			$decryptedChunk = openssl_decrypt($payloadChunk, 'aes-256-cbc', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

			$decrypted .= $decryptedChunk;

			if($end >= strlen($payload))
			{
				break;
			}
		}

		$decrypted = substr($decrypted, 0, $totalSize);
	}

	// write to file
	file_put_contents($decryptedFilePath, $decrypted);
}
