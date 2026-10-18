<?php
class Auth {
 public static function start(): void {
  if(session_status()===PHP_SESSION_ACTIVE)return;
  ini_set('session.use_strict_mode','1');
  session_set_cookie_params(['httponly'=>true,'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off','samesite'=>'Lax','path'=>'/']);
  session_name('LESIC_GOOGLE_V08');
  if(!session_start())throw new RuntimeException('Não foi possível iniciar a sessão.');
  if(isset($_SESSION['last_seen']) && time()-$_SESSION['last_seen']>1800) unset($_SESSION['admin']);
  $_SESSION['last_seen']=time();
 }
 public static function loggedIn(): bool {self::start(); return ($_SESSION['admin']??null)==='lesic.utfpr.pg@gmail.com' && !empty($_SESSION['google_subject']);}
 public static function requireAdmin(): void {if(!self::loggedIn()){header('Location: login.php');exit;}}
 public static function token(): string {self::start();return $_SESSION['csrf']??($_SESSION['csrf']=bin2hex(random_bytes(32)));}
 public static function field(): string {return '<input type="hidden" name="csrf" value="'.self::token().'">';}
 public static function check(): void {if(!is_string($_POST['csrf']??null)||!hash_equals(self::token(),$_POST['csrf'])){http_response_code(403);exit('Sessão inválida. Atualize a página e tente novamente.');}}
 public static function establishGoogle(string $subject): void {
  self::start(); session_regenerate_id(true);
  $_SESSION['admin']='lesic.utfpr.pg@gmail.com';
  $_SESSION['google_subject']=$subject;
  $_SESSION['csrf']=bin2hex(random_bytes(32));
 }
 public static function logout(): void {self::start();$_SESSION=[];session_destroy();setcookie(session_name(),'',time()-3600,'/','',!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off',true);}
}

