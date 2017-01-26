<?php

include __DIR__ . '/../demo/bootstrap.php';

$files = $_SERVER['argc'] == 1 ? glob(__DIR__ . '/**/*.php') : array_slice($_SERVER['argv'], 1);

function run_benchmark($file, $repeats = 10)
{
    $totalTime = [];

    for ($i = 0; $i < $repeats; $i++) {
        $totalTime[] = run_file($file);
        echo ".";
    }

    return $totalTime;
}

function run_file($file)
{
    $dummyObserver = new Rx\Observer\CallbackObserver(
        function ($value) { },
        function ($error) { },
        function () { }
    );

    ob_start();
    $start = microtime(true);

    include $file;

    $duration = microtime(true) - $start;
    ob_end_clean();

    return $duration;
}

$repeats = 10;

foreach ($files as $file) {
    echo "Benchmarking " . str_replace(dirname(__DIR__), '', $file) . ' ';

    $results = run_benchmark($file, $repeats);

    echo " avg.: " . (round(array_sum($results), 3) / $repeats) . "s\n";
}
