<?php
/**
 * Pharmacy ERP Basic - Secure File Server
 * Serves uploaded files through PHP, avoiding direct web access 403s
 * Developed by Shouvik
 */
if (!defined('ROOT')) define('ROOT', dirname(dirname(dirname(__FILE__))));
require_once ROOT.'/config/app.php';
startSession();
if (empty($_SESSION['admin_id'])) { http_response_code(403); exit('Access denied.'); }

$fname = basename(get('f', ''));
if (!$fname || !preg_match('/^[a-zA-Z0-9_\-\.]+$/', $fname)) {
    http_response_code(400); exit('Invalid file name.');
}

$path = DATA_DIR . '/uploads/prescriptions/' . $fname;
if (!is_file($path)) { http_response_code(404); exit('File not found.'); }

$ext  = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
$mime = match($ext) {
    'pdf'        => 'application/pdf',
    'jpg','jpeg' => 'image/jpeg',
    'png'        => 'image/png',
    'heic'       => 'image/heic',
    default      => 'application/octet-stream',
};

header('X-Content-Type-Options: nosniff');
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
header('Content-Disposition: inline; filename="' . $fname . '"');
header('Cache-Control: private, max-age=3600');
readfile($path);
exit;
