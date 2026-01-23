<?php
// Minimal upload->email endpoint (PHP hosting required)
// Returns JSON: {ok:true|false, message:"..."}

header('Content-Type: application/json; charset=utf-8');

function out($ok, $msg){
  echo json_encode(['ok'=>$ok, 'message'=>$msg]);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(false, 'Invalid request.');

$to = 'm.matzinger@gmx.at';
$from = 'no-reply@' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost');

$vorname = trim($_POST['vorname'] ?? '');
$nachname = trim($_POST['nachname'] ?? '');
$email = trim($_POST['email'] ?? '');
$consent = isset($_POST['consent']);

if ($vorname === '' || $nachname === '' || $email === '' || !$consent) out(false, 'Missing fields.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) out(false, 'Invalid email.');

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) out(false, 'File upload failed.');
$f = $_FILES['file'];

$maxBytes = 10 * 1024 * 1024; // 10MB
if ($f['size'] > $maxBytes) out(false, 'File too large (max 10MB).');

$allowed = [
  'application/pdf' => '.pdf',
  'image/jpeg' => '.jpg',
  'image/png' => '.png',
  'image/webp' => '.webp'
];

$mime = mime_content_type($f['tmp_name']);
if (!isset($allowed[$mime])) out(false, 'Unsupported file type.');

$filename = 'unterstuetzung_' . preg_replace('/[^a-z0-9_-]+/i','_', strtolower($nachname . '_' . $vorname)) . $allowed[$mime];

// Basic de-dup (best-effort) by email+ip, stored in a local json file (works on simple PHP hosts)
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$hash = hash('sha256', strtolower($email) . '|' . $ip);

$storeDir = __DIR__ . '/_store';
$storeFile = $storeDir . '/submissions.json';
if (!is_dir($storeDir)) @mkdir($storeDir, 0755, true);
$sub = [];
if (file_exists($storeFile)) {
  $raw = @file_get_contents($storeFile);
  $sub = json_decode($raw, true);
  if (!is_array($sub)) $sub = [];
}
// if already submitted within last 30 days
$now = time();
if (isset($sub[$hash]) && ($now - intval($sub[$hash])) < (30 * 24 * 60 * 60)) {
  out(true, 'Already received.');
}
$sub[$hash] = $now;
@file_put_contents($storeFile, json_encode($sub));

$subject = 'Unterstützungserklärung (Upload) – Lebenswertes Alland';
$messageText =
"Neue Unterstützungserklärung (Upload)\n\n"
."Vorname: {$vorname}\n"
."Familienname: {$nachname}\n"
."E-Mail: {$email}\n"
."IP: {$ip}\n"
."Zeit: " . date('c') . "\n";

$boundary = md5(uniqid(time()));

$headers = [];
$headers[] = "From: {$from}";
$headers[] = "Reply-To: {$email}";
$headers[] = "MIME-Version: 1.0";
$headers[] = "Content-Type: multipart/mixed; boundary="{$boundary}"";

$body = "--{$boundary}\r\n";
$body .= "Content-Type: text/plain; charset=utf-8\r\n";
$body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
$body .= $messageText . "\r\n";

$fileContent = file_get_contents($f['tmp_name']);
$body .= "--{$boundary}\r\n";
$body .= "Content-Type: {$mime}; name="{$filename}"\r\n";
$body .= "Content-Transfer-Encoding: base64\r\n";
$body .= "Content-Disposition: attachment; filename="{$filename}"\r\n\r\n";
$body .= chunk_split(base64_encode($fileContent)) . "\r\n";
$body .= "--{$boundary}--";

$ok = @mail($to, $subject, $body, implode("\r\n", $headers));

if ($ok) out(true, 'Sent.');
out(false, 'Mail send failed.');
