<?php
require_once __DIR__ . '/../src/Auth.php';
Auth::requireAdmin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') Auth::check();
require_once __DIR__ . '/../src/QuestionRepository.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {http_response_code(405);header('Allow: POST');exit('Use o botão Excluir nos detalhes.');}
$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $repo = new QuestionRepository();
    $repo->delete($id);
}
header('Location: index.php');
exit;

