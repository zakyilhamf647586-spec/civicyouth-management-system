<?php
$navigationCssPath = FCPATH
    . 'assets/css/admin-website-navigation.css';

$navigationJsPath = FCPATH
    . 'assets/js/admin-website-navigation.js';

$navigationCssVersion = is_file($navigationCssPath)
    ? (string) filemtime($navigationCssPath)
    : '1';

$navigationJsVersion = is_file($navigationJsPath)
    ? (string) filemtime($navigationJsPath)
    : '1';
?>

<link
    rel="stylesheet"
    href="<?= base_url(
        'assets/css/admin-website-navigation.css'
    ) ?>?v=<?= esc($navigationCssVersion, 'attr') ?>"
>

<script
    src="<?= base_url(
        'assets/js/admin-website-navigation.js'
    ) ?>?v=<?= esc($navigationJsVersion, 'attr') ?>"
    defer
></script>
