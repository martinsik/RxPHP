<?php

namespace Rx\Functional\Operator;

use Exception;
use Rx\Functional\FunctionalTestCase;
use Rx\Testing\TestScheduler;

class WindowTest extends FunctionalTestCase
{

    /**
     * @test
     */
    public function should_emit_windows_that_close_and_reopen()
    {
        $source = $this->createHot( '---a---b---c---d---e---f---g---h---i---|');
        $sourceSubs =               '^                                      !';
        $window = $this->createHot( '-------------w------------w------------|');
        $windowSub =                '^                                      !';
        $expected =                 'x------------y------------z------------|';
        $x      = $this->createCold('---a---b---c-|                          ');
        $y      = $this->createCold(             '--d---e---f--|             ');
        $z      = $this->createCold(                          '-g---h---i---|');
        $expectedValues = [ 'x' => $x, 'y' => $y, 'z' => $z ];

        $results = $this->scheduler->startWithCreate(function() use ($source, $window) {
            return $source->window($window);
        });

        $expectedMessages = $this->convertMarblesToMessages($expected, $expectedValues, null, TestScheduler::SUBSCRIBED);
        $this->assertMessages($expectedMessages, $results->getMessages());

        $this->assertSubscriptions($sourceSubs, $source->getSubscriptions(), TestScheduler::SUBSCRIBED);
        $this->assertSubscriptions($windowSub, $window->getSubscriptions(), TestScheduler::SUBSCRIBED);
    }

    /**
     * @test
     */
    public function should_return_a_single_empty_window_if_source_is_empty_and_closings_are_basic()
    {
        $source = $this->createCold('-|');
        $sourceSubs =               '^!';
        $window = $this->createCold('--x--x--|');
        $windowSub =                '^!';
        $expected =                 'w|';
        $w      = $this->createCold('-|');
        $expectedValues = [ 'w' => $w ];

        $results = $this->scheduler->startWithCreate(function() use ($source, $window) {
            return $source->window($window);
        });

        $expectedMessages = $this->convertMarblesToMessages($expected, $expectedValues, null, TestScheduler::SUBSCRIBED);
        $this->assertMessages($expectedMessages, $results->getMessages());

        $this->assertSubscriptions($sourceSubs, $source->getSubscriptions(), TestScheduler::SUBSCRIBED);
        $this->assertSubscriptions($windowSub, $window->getSubscriptions(), TestScheduler::SUBSCRIBED);
    }

    /**
     * @test
     */
    public function tmp_make_sure_marble_tests_can_fail()
    {
        $source = $this->createHot( '---a---b---c---d---e---f---g---h---i---|');
        $sourceSubs =               '^                                      !';
        $window = $this->createHot( '-------------w------------w------------|');
        $windowSub =                '^                                      !';
        $expected =                 'x------------y------------z------------|';
        $x      = $this->createCold('---a---b---c-|                          ');
        $y      = $this->createCold(             '--d---X---f--|             ');
        $z      = $this->createCold(                          '-g---h-------|');
        $expectedValues = [ 'x' => $x, 'y' => $y, 'z' => $z ];

        $results = $this->scheduler->startWithCreate(function() use ($source, $window) {
            return $source->window($window);
        });

        $expectedMessages = $this->convertMarblesToMessages($expected, $expectedValues, null, TestScheduler::SUBSCRIBED);
        $recordedMessages = $results->getMessages();

        $this->assertTrue($expectedMessages[0]->equals($recordedMessages[0]));
        $this->assertFalse($expectedMessages[1]->equals($recordedMessages[1]));
        $this->assertFalse($expectedMessages[2]->equals($recordedMessages[2]));
        $this->assertTrue($expectedMessages[3]->equals($recordedMessages[3]));

        $this->assertSubscriptions($sourceSubs, $source->getSubscriptions(), TestScheduler::SUBSCRIBED);
        $this->assertSubscriptions($windowSub, $window->getSubscriptions(), TestScheduler::SUBSCRIBED);
    }

}