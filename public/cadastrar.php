<?php
require_once __DIR__ . '/../src/Auth.php';
Auth::requireAdmin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') Auth::check();
require_once __DIR__ . '/../src/QuestionRepository.php';

$message = null;
$error = null;
$categories = $games = $guidelines = [];

try {
    $repo = new QuestionRepository();
    $categories = $repo->categories();
    $games = $repo->games();
    $guidelines = $repo->guidelines();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $options = [];
        foreach ($_POST['option_text'] ?? [] as $index => $text) {
            $options[] = [
                'text' => $text,
                'is_correct' => isset($_POST['correct_option']) && (string)$index === (string)$_POST['correct_option'],
            ];
        }

        $repo->create([
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

        $message = 'Questão cadastrada com sucesso.';
    }
} catch (Throwable $e) {
    $error = 'Não foi possível salvar a questão. Confira config/database.php e a migração v0.6 descrita no README. Use schema.sql somente em instalação nova.';
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastrar questão - LESIC</title>
    <link rel="stylesheet" href="css/style.css?v=20260923-embed1">
</head>
<body>
<main class="container content">
<?php $pageTitle = 'Cadastrar questão'; $pageSubtitle = 'Adicione uma pergunta ao repositório do LESIC.'; $pageAction = '<a class="button secondary" href="index.php">Voltar ao banco</a>'; require __DIR__ . '/page-heading.php'; ?>
    <?php if ($message): ?><div class="success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="post" class="form-card">
<?= Auth::field() ?>
        <label>
            Pergunta
            <textarea name="question_text" rows="4" required></textarea>
        </label>

        <div class="grid-2">
            <label>
                Categoria
                <select name="category_id">
                    <option value="">Selecione</option>
                    <?php foreach ($categories as $item): ?>
                        <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <fieldset><legend>Jogos associados</legend><p class="field-hint">Selecione um ou mais jogos. Sem seleção, a questão será de uso geral.</p>
<div class="guideline-list"><?php foreach ($games as $item): ?><label class="guideline-item"><input type="checkbox" name="game_ids[]" value="<?= (int)$item['id'] ?>" <?= in_array((int)$item['id'], array_map('intval', $_POST['game_ids'] ?? []), true) ? 'checked' : '' ?>><span><?= htmlspecialchars($item['name']) ?></span></label><?php endforeach; ?></div></fieldset>

            <label>
                Tipo de questão
                <select name="question_type" id="question_type">
                    <option value="multiple_choice">Múltipla escolha</option>
                    <option value="yes_no">Sim / Não</option>
                    <option value="scale">Escala</option>
                    <option value="open_text">Texto aberto</option>
                    <option value="rating">Avaliação</option>
                </select>
            </label>

            <label>
                Autor da questão
                <input type="text" name="author" placeholder="Nome do autor" required>
            </label>
        </div>

        <label>Referência bibliográfica (opcional)<textarea name="fonte" rows="3" aria-describedby="fonte-hint"><?= htmlspecialchars($_POST['fonte'] ?? '') ?></textarea></label>
<p id="fonte-hint" class="field-hint">Informe a literatura, norma, diretriz ou trabalho que fundamentou a elaboração ou adaptação da pergunta. Deixe em branco se ainda não identificada.</p>
<fieldset id="options_box">
            <legend>Alternativas e resposta correta</legend>
            <p class="field-hint">Digite as alternativas e marque o círculo da resposta correta. Para questões avaliativas, deixe sem marcar se não existir resposta correta.</p>
            <?php for ($i = 0; $i < 5; $i++): ?>
                <div class="option-row">
                    <input type="radio" name="correct_option" value="<?= $i ?>" title="Marcar como correta">
                    <input type="text" name="option_text[]" placeholder="Alternativa <?= $i + 1 ?>">
                </div>
            <?php endfor; ?>
            
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
                                <input type="checkbox" name="guideline_ids[]" value="<?= $guideline['id'] ?>" >
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
                                        <input type="checkbox" name="guideline_ids[]" value="<?= $guideline['id'] ?>" >
                                        <span><?= htmlspecialchars($guideline['name']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </fieldset>

        <label>
            Observações
            <textarea name="notes" rows="3"></textarea>
        </label>

        <button type="submit" class="button">Salvar questão</button>
    </form>
</main>
<script>
const type = document.getElementById('question_type');
const box = document.getElementById('options_box');
function toggleOptions() {
  box.style.display = type.value === 'open_text' ? 'none' : 'block';
}
type.addEventListener('change', toggleOptions);
toggleOptions();
</script>
</body>
</html>

