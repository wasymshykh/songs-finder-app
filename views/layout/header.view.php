<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$settings->fetch('site_name')?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700&display=swap" rel="stylesheet">

    <?php if (isset($css_before) && !empty($css_before)): ?>
        <?php foreach ($css_before as $css): ?>
            <?=$css?>
        <?php endforeach; ?>
    <?php endif; ?>

    <?=css_link('bootstrap.min', true)?>
    <?=css_link('style', true)?>

    <script src="https://kit.fontawesome.com/c833a0ca41.js" crossorigin="anonymous"></script>
    <?=js_link('jquery.min', true)?>
    
</head>
<body>

<?php if ($logged): ?>

    <div class="container-fluid bg-dark">

        <div class="container-md py-3">
            <div class="navbar navbar-expand-lg navbar-dark row align-items-center">
                <div class="col">
                    <a class="navbar-brand" href="<?=URL?>"><?=$settings->fetch('site_name')?></a>
                </div>
                <div class="col-auto">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a href="<?=href('playlists')?>" class="nav-link"><i class="fas fa-music pe-1"></i> Playlists</a>
                        </li>
                        <li class="nav-item ms-2">
                            <a href="<?=href('settings')?>" class="nav-link"><i class="fas fa-cog pe-1"></i> Settings</a>
                        </li>
                        <li class="nav-item ms-2">
                            <a href="<?=href('logout')?>" class="nav-link">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>

<?php endif; ?>
