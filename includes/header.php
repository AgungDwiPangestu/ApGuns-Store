<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Munif Store</title>
    <link rel="stylesheet" href="/Munif/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php
    $flash = get_flash();
    if ($flash): ?>
        <div class="flash-message <?php echo $flash['type']; ?>">
            <?php echo $flash['message']; ?>
            <span class="close-flash">&times;</span>
        </div>
    <?php endif; ?>