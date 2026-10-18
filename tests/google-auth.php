<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/../src/GoogleAuth.php';
ini_set('session.save_path',sys_get_temp_dir());
Auth::start();
function check($ok,$message){if(!$ok)throw new RuntimeException($message);}
function denied(callable $fn){try{$fn();}catch(RuntimeException $e){return;}throw new RuntimeException('Acesso inválido foi aceito');}
function b64($s){return rtrim(strtr(base64_encode($s),'+/','-_'),'=');}
$options=['private_key_bits'=>2048,'private_key_type'=>OPENSSL_KEYTYPE_RSA];
if(is_file('C:/xampp/apache/conf/openssl.cnf'))$options['config']='C:/xampp/apache/conf/openssl.cnf';
$key=openssl_pkey_new($options);check($key!==false,'Gerar chave de teste');
$pub=openssl_pkey_get_details($key)['key'];
$sign=function(array $claims,string $algorithm='RS256')use($key){$data=b64(json_encode(['alg'=>$algorithm,'kid'=>'test'])).'.'.b64(json_encode($claims));openssl_sign($data,$signature,$key,OPENSSL_ALGO_SHA256);return $data.'.'.b64($signature);};
$claims=['iss'=>'https://accounts.google.com','aud'=>'test-client','sub'=>'test-subject','iat'=>time(),'exp'=>time()+300,'nonce'=>'test-nonce','email_verified'=>true,'email'=>'lesic.utfpr.pg@gmail.com'];
$verify=fn($jwt)=>GoogleAuth::validateToken($jwt,['test'=>$pub],'test-client','test-nonce');
check($verify($sign($claims))['sub']==='test-subject','Conta permitida');
foreach(['email'=>'outro@gmail.com','email_verified'=>false,'aud'=>'outro-client','iss'=>'https://outro.example','exp'=>time()-1,'nonce'=>'outro-nonce','iat'=>time()+3600,'azp'=>'outro-client','sub'=>''] as $field=>$value){$bad=$claims;$bad[$field]=$value;denied(fn()=>$verify($sign($bad)));}
denied(fn()=>$verify($sign($claims,'none')));
$parts=explode('.',$sign($claims));$parts[1]=b64(json_encode(array_merge($claims,['sub'=>'alterado'])));denied(fn()=>$verify(implode('.',$parts)));
$pending=['state'=>'state-test','nonce'=>'n','created'=>time(),'verifier'=>'v'];$_SESSION['google_oauth']=$pending;
check(GoogleAuth::consumeState('state-test')===$pending,'Estado válido');denied(fn()=>GoogleAuth::consumeState('state-test'));
$_SESSION['google_oauth']=$pending;denied(fn()=>GoogleAuth::consumeState('state-incorreto'));
$_SESSION['google_oauth']=array_merge($pending,['created'=>time()-601]);denied(fn()=>GoogleAuth::consumeState('state-test'));
$_SESSION['admin']='lesic.utfpr.pg';check(!Auth::loggedIn(),'Senha antiga não autoriza');
Auth::establishGoogle('test-subject');check(Auth::loggedIn(),'Sessão Google');
$_SESSION['last_seen']=time()-1801;session_write_close();Auth::start();check(!Auth::loggedIn(),'Expiração');
Auth::logout();
echo "PASS: assinatura, conta única, email verificado, emissor, destinatário, prazo, nonce, state, replay, sessão antiga e expiração.\n";

