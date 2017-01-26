<?php

include __DIR__ . '/../demo/bootstrap.php';

$createDummyObserver = function() {
    return new Rx\Observer\CallbackObserver(
        function ($value) { },
        function ($error) { },
        function () { }
    );
};

$dummyObserver = $createDummyObserver();