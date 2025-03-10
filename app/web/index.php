<?php

const MAX_COUNT_BY_IP = 1000;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../include/common.php';

    function redirect(string $code): never {
        header('Location: https://www.google.com/?query=' . urlencode($code));
        exit;
    }

    session_start();
    $_SESSION['user_uuid'] ??= random_bytes(16);
    $ip = $_SERVER['REMOTE_ADDR'];

    $pdo = create_pdo();
    $code = find_promocode_by_user_uuid($pdo, $_SESSION['user_uuid']);
    if ($code) {
        redirect($code);
    }

    if (count_ip($pdo, $ip) >= MAX_COUNT_BY_IP) {
        $error = 'Для данного ip адреса превышено количество выданных промокодов';
        goto end;
    }

    $code = assign_promocode_to_user($pdo, $_SESSION['user_uuid'], $ip);
    if (!$code) {
        $error = 'Промокоды закончились';
        goto end;
    }

    redirect($code);
    end:
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Promo Code</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
<div class="form-container">
    <form method="POST" action="">
        <?php if (!empty($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif ?>
        <button type="submit">Получить промокод</button>
    </form>
</div>
</body>
</html>
