<?php
require_once __DIR__.'/Auth.php';
final class GoogleAuth {
 const ADMIN_EMAIL = 'lesic.utfpr.pg@gmail.com';
 public static function config(): array {
  $file=__DIR__.'/../config/google.php';
  $c=is_file($file)?require $file:[];
  foreach(['client_id','client_secret','redirect_uri'] as $key)
   if(!isset($c[$key]) || !is_string($c[$key]) || trim($c[$key])==='')throw new RuntimeException('Configure o acesso Google.');
  $url=parse_url($c['redirect_uri']);
  if(!$url || !isset($url['host'],$url['scheme']) || !($url['scheme']==='https' || ($url['scheme']==='http' && in_array($url['host'],['localhost','127.0.0.1'],true))))throw new RuntimeException('Endereço de retorno inválido.');
  return $c;
 }
 public static function configured(): bool {try{self::config();return true;}catch(Throwable $e){return false;}}
 public static function authorizationUrl(): string {
  $c=self::config();Auth::start();
  $state=bin2hex(random_bytes(32));$nonce=bin2hex(random_bytes(32));
  $verifier=rtrim(strtr(base64_encode(random_bytes(48)),'+/','-_'),'=');
  $_SESSION['google_oauth']=['state'=>$state,'nonce'=>$nonce,'verifier'=>$verifier,'created'=>time()];
  return 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
   'client_id'=>$c['client_id'],'redirect_uri'=>$c['redirect_uri'],'response_type'=>'code',
   'scope'=>'openid email','state'=>$state,'nonce'=>$nonce,'prompt'=>'select_account',
   'login_hint'=>self::ADMIN_EMAIL,'code_challenge'=>rtrim(strtr(base64_encode(hash('sha256',$verifier,true)),'+/','-_'),'='),'code_challenge_method'=>'S256',
  ],'', '&', PHP_QUERY_RFC3986);
 }
 public static function consumeState($state): array {
  Auth::start();$pending=$_SESSION['google_oauth']??null;unset($_SESSION['google_oauth']);
  if(!is_array($pending)||!is_string($state)||!hash_equals($pending['state'],$state)||time()-$pending['created']>600)throw new RuntimeException('Solicitação expirada ou inválida. Tente entrar novamente.');
  return $pending;
 }
 private static function request(string $url,?array $fields=null): array {
  if(!function_exists('curl_init'))throw new RuntimeException('Ative a extensão cURL no PHP.');
  $ch=curl_init($url);
  curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>20,
   CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_FOLLOWLOCATION=>false,
   CURLOPT_HTTPHEADER=>['Accept: application/json']]);
  if($fields!==null){curl_setopt($ch,CURLOPT_POST,true);curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($fields));}
  $body=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
  if($body===false || $status!==200)throw new RuntimeException('Não foi possível confirmar o acesso com o Google. Tente novamente.');
  $data=json_decode($body,true);
  if(!is_array($data))throw new RuntimeException('Resposta inválida do Google.');return $data;
 }
 private static function decode(string $value): string {
  if(!preg_match('/^[A-Za-z0-9_-]+$/D',$value))throw new RuntimeException('Token inválido.');
  $result=base64_decode(strtr($value,'-_','+/'),true);
  if($result===false)throw new RuntimeException('Token inválido.');return $result;
 }
 // The caller supplies only certificates downloaded from Google's fixed HTTPS endpoint.
 public static function validateToken(string $jwt,array $certificates,string $clientId,string $nonce): array {
  $parts=explode('.',$jwt);if(count($parts)!==3)throw new RuntimeException('Token inválido.');
  $header=json_decode(self::decode($parts[0]),true);$claims=json_decode(self::decode($parts[1]),true);
  if(!is_array($header)||!is_array($claims)||($header['alg']??null)!=='RS256'||!is_string($header['kid']??null)||!isset($certificates[$header['kid']]))throw new RuntimeException('Token inválido.');
  if(openssl_verify($parts[0].'.'.$parts[1],self::decode($parts[2]),$certificates[$header['kid']],OPENSSL_ALGO_SHA256)!==1)throw new RuntimeException('Assinatura inválida.');
  if(!in_array($claims['iss']??null,['https://accounts.google.com','accounts.google.com'],true)
    ||($claims['aud']??null)!==$clientId
    ||(isset($claims['azp']) && $claims['azp']!==$clientId)
    ||!is_int($claims['exp']??null)||$claims['exp']<=time()
    ||!is_int($claims['iat']??null)||$claims['iat']>time()+60
    ||!is_string($claims['nonce']??null)||!hash_equals($nonce,$claims['nonce'])
    ||!is_string($claims['sub']??null)||$claims['sub']==='')throw new RuntimeException('Identidade inválida ou expirada.');
  if(($claims['email_verified']??false)!==true || ($claims['email']??null)!==self::ADMIN_EMAIL)throw new RuntimeException('Esta conta não tem permissão de administrador. Use a conta LESIC autorizada.');
  return $claims;
 }
 public static function complete(array $query): void {
  $pending=self::consumeState($query['state']??null);
  if(isset($query['error']))throw new RuntimeException('A entrada com Google foi cancelada.');
  if(!is_string($query['code']??null)||$query['code']==='')throw new RuntimeException('Resposta inválida do Google.');
  $c=self::config();$tokens=self::request('https://oauth2.googleapis.com/token',[
   'code'=>$query['code'],'client_id'=>$c['client_id'],'client_secret'=>$c['client_secret'],
   'redirect_uri'=>$c['redirect_uri'],'grant_type'=>'authorization_code','code_verifier'=>$pending['verifier'],
  ]);
  if(!is_string($tokens['id_token']??null))throw new RuntimeException('Identidade não recebida.');
  $certs=self::request('https://www.googleapis.com/oauth2/v1/certs');
  $claims=self::validateToken($tokens['id_token'],$certs,$c['client_id'],$pending['nonce']);
  Auth::establishGoogle($claims['sub']);
 }
}

