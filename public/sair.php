<?php
require_once __DIR__.'/../src/Auth.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);header('Allow: POST');exit;}
Auth::check();Auth::logout();header('Location: index.php');exit;

