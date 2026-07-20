<?php

$publicationCssPath =
    FCPATH . 'assets/css/admin-publications.css';

$publicationJsPath =
    FCPATH . 'assets/js/admin-publications.js';

$publicationCssVersion = is_file($publicationCssPath)
    ? (string) filemtime($publicationCssPath)
    : '1';

$publicationJsVersion = is_file($publicationJsPath)
    ? (string) filemtime($publicationJsPath)
    : '1';
?>

<link
    id="garda-publication-module-css"
    rel="stylesheet"
    href="<?= base_url(
        'assets/css/admin-publications.css'
    ) ?>?v=<?= esc($publicationCssVersion, 'attr') ?>"
>

<script
    id="garda-publication-module-js"
    src="<?= base_url(
        'assets/js/admin-publications.js'
    ) ?>?v=<?= esc($publicationJsVersion, 'attr') ?>"
    defer
></script>
