<?php
require_once __DIR__ . '/../src/Auth.php';
Auth::requireAdmin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') Auth::check();
require_once __DIR__ . '/../src/QuestionRepository.php';
require_once __DIR__ . '/../src/OptionImage.php';

$id = (int)($_GET['id'] ?? 0);
$repo = new QuestionRepository();
$question = $id ? $repo->find($id) : null;
if (!$question) { http_response_code(404); exit('Questão não encontrada.'); }

$categories = $repo->categories();
$games = $repo->games();
    $guidelines = $repo->guidelines();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $options = [];
        foreach ($_POST['option_text'] ?? [] as $index => $text) {
            $options[] = [
                'text' => $text,
                'id' => (int)($_POST['option_id'][$index] ?? 0),
                'is_correct' => isset($_POST['correct_option']) && (string)$index === (string)$_POST['correct_option'],
            ];
        }

        $repo->update($id, [
            'question_text' => trim($_POST['question_text'] ?? ''),
            'author' => trim($_POST['author'] ?? ''),
            'fonte' => trim($_POST['fonte'] ?? ''),
            'category_id' => $_POST['category_id'] ?? '',
            'game_ids' => $_POST['game_ids'] ?? [],
            'question_type' => $_POST['question_type'] ?? 'multiple_choice',
            'notes' => trim($_POST['notes'] ?? ''),
            'guideline_ids' => $_POST['guideline_ids'] ?? [],
            'options' => $options,
        ]);
        header('Location: detalhes.php?id=' . $id);
        exit;
    } catch (Throwable $e) {
        $error = 'Não foi possível atualizar a questão.';
    }
}

$options = $question['options'] ?? [];
while (count($options) < 5) {
    $options[] = ['option_text' => '', 'is_correct' => 0];
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Editar questão - LESIC</title>
<link rel="stylesheet" href="css/style.css?v=20260923-embed1">
<link rel="stylesheet" href="css/option-images.css">
</head>
<body>
<main class="container content">
<?php $pageTitle = 'Editar questão'; $pageSubtitle = 'Q' . str_pad((string)$id, 3, '0', STR_PAD_LEFT) . ' — Banco de Questões LESIC'; $pageAction = '<a class="button secondary" href="detalhes.php?id=' . (int)$id . '">Cancelar</a>'; require __DIR__ . '/page-heading.php'; ?>
<?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="post" class="form-card">
<?= Auth::field() ?>
<label>Pergunta<textarea name="question_text" rows="4" required><?= htmlspecialchars($question['question_text']) ?></textarea></label>
<div class="grid-2">
<label>Categoria<select name="category_id"><option value="">Selecione</option><?php foreach ($categories as $item): ?><option value="<?= $item['id'] ?>" <?= $question['category_id']==$item['id']?'selected':'' ?>><?= htmlspecialchars($item['name']) ?></option><?php endforeach; ?></select></label>
<fieldset><legend>Jogos associados</legend><p class="field-hint">Selecione um ou mais jogos. Sem seleção, a questão será de uso geral.</p>
<div class="guideline-list"><?php foreach ($games as $item): ?><label class="guideline-item"><input type="checkbox" name="game_ids[]" value="<?= (int)$item['id'] ?>" <?= in_array((int)$item['id'], $_SERVER['REQUEST_METHOD']==='POST' ? array_map('intval', $_POST['game_ids'] ?? []) : ($question['game_ids'] ?? []), true) ? 'checked' : '' ?>><span><?= htmlspecialchars($item['name']) ?></span></label><?php endforeach; ?></div></fieldset>
<label>Tipo de questão<select name="question_type" id="question_type">
<?php foreach (['multiple_choice'=>'Múltipla escolha','yes_no'=>'Sim / Não','scale'=>'Escala','open_text'=>'Texto aberto','rating'=>'Avaliação'] as $value=>$label): ?><option value="<?= $value ?>" <?= $question['question_type']===$value?'selected':'' ?>><?= $label ?></option><?php endforeach; ?>
</select></label>
<label>Autor da questão<input type="text" name="author" value="<?= htmlspecialchars($question['author'] ?? '') ?>" required></label>
</div>
<label>Referência bibliográfica (opcional)<textarea name="fonte" rows="3" aria-describedby="fonte-hint"><?= htmlspecialchars($_POST['fonte'] ?? $question['fonte'] ?? '') ?></textarea></label>
<p id="fonte-hint" class="field-hint">Informe a literatura, norma, diretriz ou trabalho que fundamentou a elaboração ou adaptação da pergunta. Deixe em branco se ainda não identificada.</p>
<fieldset id="options_box"><legend>Alternativas e resposta correta</legend>
<p class="field-hint">Digite as alternativas e marque o círculo da resposta correta. Para questões avaliativas, deixe sem marcar se não existir resposta correta.</p>
<?php foreach ($options as $i => $option): ?><div class="option-row"><input type="hidden" name="option_id[]" value="<?= (int)($option['id'] ?? 0) ?>"><?= OptionImage::render($option) ?><input type="radio" name="correct_option" value="<?= $i ?>" <?= !empty($option['is_correct'])?'checked':'' ?> title="Marcar como correta"><input type="text" name="option_text[]" value="<?= htmlspecialchars($option['option_text'] ?? '') ?>" placeholder="Alternativa <?= $i+1 ?>"></div><?php endforeach; ?>
</fieldset>
        <fieldset>
            <legend>Diretrizes de acessibilidade (opcional)</legend>
            <p class="field-hint">Selecione uma ou mais diretrizes relacionadas à questão. Deixe em branco quando não se aplicar.</p>

            <?php
            $wcag = array_values(array_filter($guidelines, fn($g) => $g['framework'] === 'WCAG'));
            $gag  = array_values(array_filter($guidelines, fn($g) => $g['framework'] === 'GAG'));
            ?>

            <?php if ($wcag): ?>
                <div class="guideline-group">
                    <h3>WCAG</h3>
                    <div class="guideline-list">
                        <?php foreach ($wcag as $guideline): ?>
                            <label class="guideline-item">
                                <input type="checkbox" name="guideline_ids[]" value="<?= $guideline['id'] ?>" <?php if (!empty($question['guidelines']) && in_array($guideline['id'], array_column($question['guidelines'], 'id'))) echo 'checked'; ?>>
                                <span><?= htmlspecialchars(($guideline['code'] ? $guideline['code'] . ' — ' : '') . $guideline['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($gag): ?>
                <div class="guideline-group">
                    <h3>GAG</h3>
                    <?php
                    $groups = [];
                    foreach ($gag as $guideline) {
                        $key = ($guideline['category'] ?? 'Geral') . '|' . ($guideline['level'] ?? '');
                        $groups[$key][] = $guideline;
                    }
                    ?>
                    <?php foreach ($groups as $key => $items): ?>
                        <?php [$category, $level] = explode('|', $key, 2); ?>
                        <div class="gag-subgroup">
                            <h4><?= htmlspecialchars($category . ($level ? ' — ' . $level : '')) ?></h4>
                            <div class="guideline-list">
                                <?php foreach ($items as $guideline): ?>
                                    <label class="guideline-item">
                                        <input type="checkbox" name="guideline_ids[]" value="<?= $guideline['id'] ?>" <?php if (!empty($question['guidelines']) && in_array($guideline['id'], array_column($question['guidelines'], 'id'))) echo 'checked'; ?>>
                                        <span><?= htmlspecialchars($guideline['name']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </fieldset>

<label>Observações<textarea name="notes" rows="3"><?= htmlspecialchars($question['notes'] ?? '') ?></textarea></label>
<button type="submit" class="button">Salvar alterações</button>
</form>
</main>
<script>
const type=document.getElementById('question_type'),box=document.getElementById('options_box');
function toggleOptions(){box.style.display=type.value==='open_text'?'none':'block';}
type.addEventListener('change',toggleOptions);toggleOptions();
</script>
</body>
</html>

