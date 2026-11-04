<?php
$pageTitle = $pageTitle ?? 'Banco de Questões';
$pageEyebrow = $pageEyebrow ?? 'DESENVOLVIDO POR ANTONY GOES';
$pageSubtitle = $pageSubtitle ?? 'Repositório de perguntas para avaliação de jogos educacionais.';
$pageAction = $pageAction ?? '';
?>
<section class="page-banner" aria-labelledby="page-title">
    <div class="page-banner-inner">
        <div class="page-heading">
            <p class="eyebrow"><?= htmlspecialchars($pageEyebrow, ENT_QUOTES, 'UTF-8') ?></p>
            <h1 id="page-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
            <?php if ($pageSubtitle !== ''): ?><p><?= htmlspecialchars($pageSubtitle, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
            <?php if (!empty($pageGithubUrl)): ?><a class="button secondary github-link" href="<?= htmlspecialchars($pageGithubUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Ver código no GitHub</a><?php endif; ?>
        </div>
        <?php if ($pageAction !== ''): ?><div class="page-actions"><?= $pageAction ?></div><?php endif; ?>
    </div>
    <div class="page-rule" aria-hidden="true"></div>
</section>
