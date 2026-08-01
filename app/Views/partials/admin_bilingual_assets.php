<?php
$bilingualCssPath = FCPATH
    . 'assets/css/admin-bilingual-editor.css';
$bilingualCssVersion = is_file($bilingualCssPath)
    ? (string) filemtime($bilingualCssPath)
    : '1';
?>

<link
    rel="stylesheet"
    href="<?= base_url(
        'assets/css/admin-bilingual-editor.css'
    ) ?>?v=<?= esc($bilingualCssVersion, 'attr') ?>"
>
