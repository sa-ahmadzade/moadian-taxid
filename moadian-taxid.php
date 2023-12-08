<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
 
date_default_timezone_set("Asia/Tehran");


function generateInvoiceId($clientId,$internalInvoiceId) 
{
	date_default_timezone_set("Asia/Tehran");
	
	$daysPastEpoch = (int)(time() / (3600 * 24));
	$daysPastEpochPadded = str_pad($daysPastEpoch, 6, '0', STR_PAD_LEFT);
	$hexDaysPastEpochPadded = str_pad(dechex($daysPastEpoch), 5, '0', STR_PAD_LEFT);

	$numericClientId = clientIdToNumber($clientId);

	$internalInvoiceIdPadded = str_pad($internalInvoiceId, 12, '0', STR_PAD_LEFT);
	$hexInternalInvoiceIdPadded = str_pad(dechex($internalInvoiceId), 10, '0', STR_PAD_LEFT);

	$decimalInvoiceId = $numericClientId . $daysPastEpochPadded . $internalInvoiceIdPadded;

	$checksum =  checkSum($decimalInvoiceId);

	return strtoupper($clientId . $hexDaysPastEpochPadded . $hexInternalInvoiceIdPadded . $checksum);
}


function clientIdToNumber(string $clientId) 
{
	$result = '';
	$CHARACTER_TO_NUMBER_CODING = [
	'A' => 65, 'B' => 66, 'C' => 67, 'D' => 68, 'E' => 69, 'F' => 70, 'G' => 71, 'H' => 72, 'I' => 73,
	'J' => 74, 'K' => 75, 'L' => 76, 'M' => 77, 'N' => 78, 'O' => 79, 'P' => 80, 'Q' => 81, 'R' => 82,
	'S' => 83, 'T' => 84, 'U' => 85, 'V' => 86, 'W' => 87, 'X' => 88, 'Y' => 89, 'Z' => 90,
	];
	
	foreach (str_split($clientId) as $char) {
		if (is_numeric($char)) {
			$result .= $char;
		} else {
			$result .= $CHARACTER_TO_NUMBER_CODING[$char];
		}
	}

	return $result;
}



$MULTIPLICATION_TABLE = [
	[0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
	[1, 2, 3, 4, 0, 6, 7, 8, 9, 5],
	[2, 3, 4, 0, 1, 7, 8, 9, 5, 6],
	[3, 4, 0, 1, 2, 8, 9, 5, 6, 7],
	[4, 0, 1, 2, 3, 9, 5, 6, 7, 8],
	[5, 9, 8, 7, 6, 0, 4, 3, 2, 1],
	[6, 5, 9, 8, 7, 1, 0, 4, 3, 2],
	[7, 6, 5, 9, 8, 2, 1, 0, 4, 3],
	[8, 7, 6, 5, 9, 3, 2, 1, 0, 4],
	[9, 8, 7, 6, 5, 4, 3, 2, 1, 0],
];

$PERMUTATION_TABLE = [
	[0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
	[1, 5, 7, 6, 2, 8, 3, 0, 9, 4],
	[5, 8, 0, 3, 7, 9, 6, 1, 4, 2],
	[8, 9, 1, 6, 0, 4, 3, 5, 2, 7],
	[9, 4, 5, 3, 1, 2, 6, 8, 7, 0],
	[4, 2, 8, 6, 5, 7, 3, 9, 0, 1],
	[2, 7, 9, 3, 8, 0, 6, 4, 1, 5],
	[7, 0, 4, 6, 9, 1, 3, 2, 5, 8],
];

$INVERSE_TABLE = [0, 4, 3, 2, 1, 5, 6, 7, 8, 9];

function checkSum($number) 
{
	global $MULTIPLICATION_TABLE;
	global $PERMUTATION_TABLE;
	global $INVERSE_TABLE;
	
	$c = 0;
	$len = strlen($number);

	for ($i = 0; $i < $len; ++$i) {
		$c = $MULTIPLICATION_TABLE[$c][$PERMUTATION_TABLE[(($i + 1) % 8)][$number[$len - $i - 1] - '0']];
	}

	return $INVERSE_TABLE[$c];
}

function validate($number) 
{
	global $MULTIPLICATION_TABLE;
	global $PERMUTATION_TABLE;
	global $INVERSE_TABLE;
	
	$c = 0;
	$len = strlen($number);

	for ($i = 0; $i < $len; ++$i) {
		$c = $MULTIPLICATION_TABLE[$c][$PERMUTATION_TABLE[($i % 8)][$number[$len - $i - 1] - '0']];
	}

	return $c == 0;
}
