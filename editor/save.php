<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['error' => 'POST required']);
	exit;
}

// Limit the request body to 4 KB
$maxBytes = 4096;
$contentLength = isset($_SERVER['CONTENT_LENGTH'])
	? (int)$_SERVER['CONTENT_LENGTH']
	: 0;

if ($contentLength > $maxBytes) {
	http_response_code(413);
	echo json_encode(['error' => 'Request too large']);
	exit;
}

$rawInput = file_get_contents('php://input', false, null, 0, $maxBytes + 1);

if ($rawInput === false || strlen($rawInput) > $maxBytes) {
	http_response_code(413);
	echo json_encode(['error' => 'Request too large or unreadable']);
	exit;
}

$input = json_decode($rawInput, true);

if (!is_array($input) || json_last_error() !== JSON_ERROR_NONE) {
	http_response_code(400);
	echo json_encode(['error' => 'Invalid JSON']);
	exit;
}

$defaults = [
	'blobColor' => '#e4ecfa',
	'upperLight' => '#a7f8ab',
	'lowerLight' => '#b7b7b4',
	'ambientLight' => '#013404',
	'speed' => 0.5,
	'spikes' => 1.75,
	'processing' => 1.0
];

$expectedKeys = array_keys($defaults);

if (count($input) !== count($expectedKeys) ||
	count(array_diff(array_keys($input), $expectedKeys)) !== 0) {
	http_response_code(400);
	echo json_encode(['error' => 'Unexpected or missing fields']);
	exit;
}

$colors = ['blobColor', 'upperLight', 'lowerLight', 'ambientLight'];

$config = [];

foreach ($colors as $key) {
	if (!isset($input[$key]) ||
		!is_string($input[$key]) ||
		!preg_match('/^#[0-9a-fA-F]{6}$/', $input[$key])) {
		http_response_code(400);
		echo json_encode(['error' => "Invalid color: $key"]);
		exit;
	}

	$config[$key] = strtolower($input[$key]);
}

$ranges = [
	'speed' => [0, 2],
	'spikes' => [0.1, 5],
	'processing' => [0.1, 2]
];

foreach ($ranges as $key => $range) {
	if (!isset($input[$key]) ||
		!is_numeric($input[$key]) ||
		!is_finite((float)$input[$key])) {
		http_response_code(400);
		echo json_encode(['error' => "Invalid value: $key"]);
		exit;
	}

	$value = (float)$input[$key];

	if ($value < $range[0] || $value > $range[1]) {
		http_response_code(400);
		echo json_encode(['error' => "Out of range: $key"]);
		exit;
	}

	$config[$key] = $value;
}

$file = dirname(__DIR__) . '/blob-config.json';
$temp = $file . '.tmp';

$json = json_encode(
	$config,
	JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
);

if ($json === false || file_put_contents($temp, $json . "\n", LOCK_EX) === false) {
	@unlink($temp);
	http_response_code(500);
	echo json_encode(['error' => 'Could not write temporary configuration']);
	exit;
}

if (!rename($temp, $file)) {
	@unlink($temp);
	http_response_code(500);
	echo json_encode(['error' => 'Could not replace configuration']);
	exit;
}

echo json_encode(['success' => true]);
?>