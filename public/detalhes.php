<?php
require_once __DIR__ . '/../src/Auth.php';
Auth::start();
require_once __DIR__ . '/../src/QuestionRepository.php';
require_once __DIR__ . '/../src/OptionImage.php';
function typeLabel(string $type): string {
    return ['multiple_choice'=>'Múltipla escolha','yes_no'=>'Sim / Não','scale'=>'Escala','open_text'=>'Texto aberto','rating'=>'Avaliação'][$type] ?? $type;
}
$id = (int)($_GET['id'] ?? 0);
$repo = new QuestionRepository();
$question = $id ? $repo->find($id) : null;
if (!$question) { http_response_code(404); exit('Questão não encontrada.'); }
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Q<?= str_pad((string)$question['id'],3,'0',STR_PAD_LEFT) ?> - Banco de Questões</title>
<link rel="stylesheet" href="css/style.css?v=20260923-embed1">
<link rel="stylesheet" href="css/option-images.css">
</head>
<body>
<main class="container content">
    <?php $pageTitle = 'Detalhes da questão'; $pageSubtitle = 'Consulte as informações e referências desta pergunta.'; $pageAction = '<a class="button secondary" href="index.php">Voltar ao banco</a>'; require __DIR__ . '/page-heading.php'; ?>
    <article class="detail-card">
        <div class="card-head">
            <div><span class="id">Q<?= str_pad((string)$question['id'],3,'0',STR_PAD_LEFT) ?></span> <span class="tag"><?= htmlspecialchars($question['category_name'] ?? 'Sem categoria') ?></span></div>
        </div>
        <h2 class="detail-question"><?= htmlspecialchars($question['question_text']) ?></h2>
        <div class="detail-grid">
            <div><strong>Tipo</strong><span><?= htmlspecialchars(typeLabel($question['question_type'])) ?></span></div>
            <div><strong>Jogos associados</strong><span><?= htmlspecialchars($question['game_name'] ?? 'Uso geral') ?></span></div>
            <div><strong>Autor</strong><span><?= htmlspecialchars($question['author'] ?? 'Autor não identificado') ?></span></div>
        </div>

        <div class="notes fonte"><strong>Referência bibliográfica</strong><p><?= nl2br(htmlspecialchars($question['fonte'] ?? 'Não informada')) ?></p></div>

        <div class="notes"><strong>Diretrizes WCAG/GAG</strong>
            <?php if (!empty($question['guidelines'])): ?>
                <p><?php foreach ($question['guidelines'] as $i => $g): ?><?= $i ? '<br>' : '' ?><?= htmlspecialchars($g['framework'] . (!empty($g['category']) ? ' · ' . $g['category'] : '') . (!empty($g['level']) ? ' · ' . $g['level'] : '') . ' — ' . ($g['code'] ? $g['code'] . ' — ' : '') . $g['name']) ?><?php endforeach; ?></p>
            <?php else: ?><p>Não se aplica / não informada.</p><?php endif; ?>
        </div>

        <?php if (!empty($question['options'])): ?>
            <h3>Alternativas</h3>
            <div class="options-list">
                <?php foreach ($question['options'] as $option): ?>
                    <div class="detail-option <?= !empty($option['is_correct']) ? 'correct-option' : '' ?>">
                        <?= OptionImage::render($option) ?>
                        <span><?= htmlspecialchars($option['option_text'] ?? '') ?></span>
                        <?php if (!empty($option['is_correct'])): ?><strong class="correct-badge">✓ Alternativa correta</strong><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($question['question_type'] === 'open_text'): ?>
            <p class="muted">Esta é uma pergunta de resposta aberta.</p>
        <?php endif; ?>

        <?php if (!empty($question['notes'])): ?><div class="notes"><strong>Observações</strong><p><?= nl2br(htmlspecialchars($question['notes'])) ?></p></div><?php endif; ?>

        <?php if(Auth::loggedIn()): ?><div class="actions">
            <a class="button" href="editar.php?id=<?= (int)$question['id'] ?>">Editar</a>
            <form method="post" action="excluir.php" onsubmit="return confirm('Excluir esta questão? Esta ação não pode ser desfeita.')">
            <?= Auth::field() ?><input type="hidden" name="id" value="<?= (int)$question['id'] ?>"><button class="button danger" type="submit">Excluir</button></form>
        </div><?php endif; ?>
    </article>
</main>
</body>
</html>

