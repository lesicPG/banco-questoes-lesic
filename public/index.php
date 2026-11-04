<?php
require_once __DIR__ . '/../src/Auth.php';
Auth::start();
require_once __DIR__ . '/../src/QuestionRepository.php';

function typeLabel(string $type): string {
    return [
        'multiple_choice' => 'Múltipla escolha',
        'yes_no' => 'Sim / Não',
        'scale' => 'Escala',
        'open_text' => 'Texto aberto',
        'rating' => 'Avaliação',
    ][$type] ?? $type;
}

$error = null;
$questions = $categories = $games = [];
$types = ['multiple_choice','yes_no','scale','open_text','rating'];

try {
    $repo = new QuestionRepository();
    $search = trim($_GET['q'] ?? '');
    $categoryId = !empty($_GET['category']) ? (int)$_GET['category'] : null;
    $gameId = !empty($_GET['game']) ? (int)$_GET['game'] : null;
    $type = !empty($_GET['type']) ? $_GET['type'] : null;
    $questions = $repo->all($search, $categoryId, $gameId, $type);
    $categories = $repo->categories();
    $games = $repo->games();
} catch (Throwable $e) {
    $error = 'Não foi possível acessar o banco. Confira config/database.php e as instruções de instalação/migração no README.';
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Banco de Questões - LESIC</title>
    <link rel="stylesheet" href="css/style.css?v=20261103-github-link-1">
</head>
<body>
<?php
$pageTitle = 'Banco de Questões';
$pageEyebrow = 'Desenvolvido por Antony Goes';
$pageGithubUrl = 'https://github.com/lesicPG/banco-questoes-lesic';
$pageSubtitle = 'Pesquise perguntas e filtre por texto, categoria, jogo ou tipo para encontrar itens que podem ser reutilizados na avaliação de jogos educacionais.';
$pageAction = Auth::loggedIn()
    ? '<div class="actions"><a class="button" href="cadastrar.php">+ Nova questão</a><form method="post" action="sair.php">' . Auth::field() . '<button class="button secondary" type="submit">Sair</button></form></div>'
    : '<a class="button" href="login.php" target="_blank" rel="noopener">Entrar como administrador</a>';
?>

<main class="container content">
    <?php require __DIR__ . '/page-heading.php'; ?>
    <div class="content-body">
    <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form class="filters filters-5" method="get">
        <input type="search" name="q" placeholder="Pesquisar questão..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <select name="category">
            <option value="">Todas as categorias</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>" <?= (($_GET['category'] ?? '') == $category['id']) ? 'selected' : '' ?>><?= htmlspecialchars($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="game">
            <option value="">Todos os jogos</option>
            <?php foreach ($games as $game): ?>
                <option value="<?= $game['id'] ?>" <?= (($_GET['game'] ?? '') == $game['id']) ? 'selected' : '' ?>><?= htmlspecialchars($game['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="type">
            <option value="">Todos os tipos</option>
            <?php foreach ($types as $item): ?>
                <option value="<?= $item ?>" <?= (($_GET['type'] ?? '') === $item) ? 'selected' : '' ?>><?= htmlspecialchars(typeLabel($item)) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filtrar</button>
    </form>

    <?php if (!$error): ?>
        <p class="results-count"><?= count($questions) ?> <?= count($questions) === 1 ? 'questão encontrada' : 'questões encontradas' ?></p>
    <?php endif; ?>

    <section class="question-list">
        <?php if (!$error && empty($questions)): ?><div class="empty-state">Nenhuma questão cadastrada ainda.</div><?php endif; ?>

        <?php foreach ($questions as $question): ?>
            <article class="card clickable-card" onclick="location.href='detalhes.php?id=<?= (int)$question['id'] ?>'">
                <div class="card-head">
                    <div>
                        <span class="id">Q<?= str_pad((string)$question['id'], 3, '0', STR_PAD_LEFT) ?></span>
                        <span class="tag"><?= htmlspecialchars($question['category_name'] ?? 'Sem categoria') ?></span>
                    </div>
                    <span class="view-link">Ver detalhes →</span>
                </div>
                <h2><?= htmlspecialchars($question['question_text']) ?></h2>
                <p class="meta">
                    <?= htmlspecialchars($question['game_name'] ?? 'Uso geral') ?>
                    · <?= htmlspecialchars(typeLabel($question['question_type'])) ?>
                </p>
                <p class="meta fonte"><strong>Referência bibliográfica:</strong> <?= htmlspecialchars($question['fonte'] ?? 'Não informada') ?></p>
            </article>
        <?php endforeach; ?>
    </section>
</div>
</main>
</body>
</html>
