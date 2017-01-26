<?php

use Rx\Observable;

Observable::range(1, pow(10, 4))
    ->distinct()
    ->subscribe($dummyObserver);
