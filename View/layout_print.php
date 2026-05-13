<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(isset($title) && $title !== '' ? (string) $title . ' · UniServe' : 'UniServe', ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="icon" href="<?= $this->asset('/View/shared/assets/img/logo.png') ?>" type="image/png">
</head>
<body class="bg-white">
<?= $content ?>
</body>
</html>
