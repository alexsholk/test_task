<?php

const PER_PAGE = 100;

require_once __DIR__ . '/../include/common.php';

$pdo = create_pdo();
$count = count_promocodes($pdo);

$totalPages = ceil($count / PER_PAGE);
$currentPage = $_GET['page'] ?? 1;
if ($currentPage < 1 || $currentPage > $totalPages) {
    header("Location: /promocodes.php?page=1");
    die;
}

$promocodes = paginate_promocodes($pdo, $currentPage, PER_PAGE);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promocodes</title>
    <link rel="stylesheet" href="css/promocodes.css">
</head>
<body>
<h1>Promocodes</h1>


<div class="table-container">
    <?php ob_start() ?>
    <div class="pagination-container">
        <a href="?page=1" class="<?= $currentPage == 1 ? 'disabled' : '' ?>">First</a>
        <a href="?page=<?= max(1, $currentPage - 1) ?>" class="<?= $currentPage == 1 ? 'disabled' : '' ?>">Previous</a>
        <form action="" method="get" class="pagination-form">
            <input
                    type="number"
                    name="page"
                    value="<?= $currentPage ?>"
                    min="1"
                    max="<?= $totalPages ?>"
                    class="pagination-input"
            />
            <button type="submit">Go</button>
        </form>
        <a href="?page=<?= min($totalPages, $currentPage + 1) ?>" class="<?= $currentPage == $totalPages ? 'disabled' : '' ?>">Next</a>
        <a href="?page=<?= $totalPages ?>" class="<?= $currentPage == $totalPages ? 'disabled' : '' ?>">Last</a>
    </div>
    <?php $pagination = ob_get_flush() ?>
    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Code</th>
            <th>Issue Date</th>
            <th>User UUID</th>
            <th>User IP</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($promocodes as $index => $promocode): ?>
            <tr>
                <td><?= $index + 1 + (($_GET['page'] ?? 1) - 1) * PER_PAGE ?></td>
                <td><?= $promocode['code'] ?></td>
                <td><?= $promocode['issue_date'] ?? 'Not Issued' ?></td>
                <td><?= $promocode['user_uuid'] ?? '' ?></td>
                <td><?= $promocode['user_ip'] ?? '' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?= $pagination ?>
</div>
</body>
</html>
