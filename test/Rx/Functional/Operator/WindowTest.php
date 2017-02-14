<?php

namespace Rx\Functional\Operator;

use Exception;
use Rx\Functional\FunctionalTestCase;

class WindowTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function should_emit_windows_that_close_and_reopen()
    {
        $xs = $this->createHotObservable([
            onNext(225, 1),
            onNext(250, 2),
            onNext(300, 3),
            onNext(350, 4),
            onNext(400, 5),
            onNext(450, 6),
            onNext(500, 7),
            onNext(550, 8),
            onNext(600, 9),
            onCompleted(700),
        ]);

        $window = $this->createHotObservable([
            onNext(325, 1),
            onNext(475, 1),
        ]);

        $results = $this->scheduler->startWithCreate(function() use ($xs, $window) {
            return $xs->window($window);
        });

        $this->assertMessages([
            onNext(200, $this->createColdObservable([
                onNext(25, 1),
                onNext(50, 2),
                onNext(100, 3),
                onCompleted(125),
            ])),
            onNext(325, $this->createColdObservable([
                onNext(25, 4),
                onNext(75, 5),
                onNext(125, 6),
                onCompleted(150),
            ])),
            onNext(475, $this->createColdObservable([
                onNext(25, 7),
                onNext(75, 8),
                onNext(125, 9),
                onCompleted(225),
            ])),
            onCompleted(700),
        ], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 700)], $xs->getSubscriptions());
        $this->assertSubscriptions([subscribe(200, 700)], $window->getSubscriptions());
    }

    /**
     * @test
     */
    public function should_return_a_single_empty_window_if_source_is_empty_and_closings_are_basic()
    {
        $xs = $this->createHotObservable([
            onCompleted(250),
        ]);

        $window = $this->createHotObservable([
            onNext(300, 1),
            onNext(400, 1),
        ]);

        $results = $this->scheduler->startWithCreate(function() use ($xs, $window) {
            return $xs->window($window);
        });

        $this->assertMessages([
            onNext(200, $this->createColdObservable([
                onCompleted(50)
            ])),
            onCompleted(250),
        ], $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 250)], $xs->getSubscriptions());
        $this->assertSubscriptions([subscribe(200, 250)], $window->getSubscriptions());
    }

}