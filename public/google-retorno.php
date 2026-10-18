<?php
require_once __DIR__.'/../src/GoogleAuth.php';
Auth::start();header('Cache-Control: no-store');header('Referrer-Policy: no-referrer');
try{GoogleAuth::complete($_GET);header('Location: index.php');}
catch(Throwable $e){
 unset($_SESSION['admin'],$_SESSION['google_subject']);
 $_SESSION['login_error']=$e instanceof RuntimeException?$e->getMessage():'Não foi possível entrar com Google. Tente novamente.';
 header('Location: login.php');
}
exit;

