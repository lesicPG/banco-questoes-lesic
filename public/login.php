<?php
require_once __DIR__.'/../src/GoogleAuth.php';
Auth::start();
header('Cache-Control: no-store');
if(Auth::loggedIn()){header('Location: index.php');exit;}
$error=$_SESSION['login_error']??null;unset($_SESSION['login_error']);
if($_SERVER['REQUEST_METHOD']==='POST'){
 Auth::check();
 try{header('Location: '.GoogleAuth::authorizationUrl());exit;}
 catch(Throwable $e){$error='O acesso Google ainda não está configurado. Consulte o responsável pela instalação.';}
}
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Entrar — LESIC</title><link rel="stylesheet" href="css/style.css?v=20260923-embed1"></head><body>
<main class="container content">
<?php $pageTitle = 'Acesso administrativo'; $pageSubtitle = 'Área de gestão do Banco de Questões LESIC.'; $pageAction = '<a class="button secondary" href="index.php">Consultar questões</a>'; require __DIR__ . '/page-heading.php'; ?>
<?php if($error): ?><p class="alert"><?= htmlspecialchars($error,ENT_QUOTES,'UTF-8') ?></p><?php endif; ?>
<?php if(!GoogleAuth::configured()): ?><p class="alert">O acesso Google ainda não está configurado. Consulte o responsável pela instalação.</p><?php else: ?>
<form method="post" class="form-card"><?= Auth::field() ?>
<p>Entre com a conta Google autorizada do LESIC para administrar as questões.</p>
<button type="submit">Entrar com Google</button></form><?php endif; ?></main></body></html>

