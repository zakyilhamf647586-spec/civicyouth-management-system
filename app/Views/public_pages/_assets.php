<?php

$publicPageCssPath =
    FCPATH . 'assets/css/admin-public-pages.css';

$publicPageCssVersion = is_file($publicPageCssPath)
    ? (string) filemtime($publicPageCssPath)
    : '1';
?>

<link
    rel="stylesheet"
    href="<?= base_url(
        'assets/css/admin-public-pages.css'
    ) ?>?v=<?= esc(
        $publicPageCssVersion,
        'attr'
    ) ?>"
>
