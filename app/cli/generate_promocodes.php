<?php

require_once __DIR__ . '/../include/common.php';

const MAX_COUNT = 1e6;

if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "This script can only be run from the command line.\n");
    exit(1);
}

if (!isset($_SERVER['argv'][1])) {
    fwrite(STDERR, "The count argument is required. Usage: php generate_promocodes.php <count>\n");
    exit(1);
}

$count = $_SERVER['argv'][1];
if (!preg_match('/^\d+$/', $count)) {
    fwrite(STDERR, "The count argument must be a positive integer.\n");
    exit(1);
}

if ($count < 1 || $count > MAX_COUNT) {
    fprintf(STDERR, "The count argument must be between 1 and %d.\n", MAX_COUNT);
    exit(1);
}

$pdo = create_pdo();
$start = microtime(true);
if (!generate_promocodes($pdo, $count)) {
    fwrite(STDERR, "Failed to generate promocodes.\n");
    exit(1);
}
$time = microtime(true) - $start;

fprintf(STDOUT, "%d promocodes were generated in %.3f seconds.\n", $count, $time);
