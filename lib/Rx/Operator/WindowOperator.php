<?php

namespace Rx\Operator;

use Rx\Observable;
use Rx\ObservableInterface;
use Rx\Observer\AutoDetachObserver;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;
use Rx\Subject\Subject;
use Rx\Disposable\BinaryDisposable;

class WindowOperator implements OperatorInterface
{
    /** @var Observable */
    private $windowBoundaries;

    /** @var Subject */
    private $window;

    public function __construct(Observable $windowBoundaries)
    {
        $this->windowBoundaries = $windowBoundaries;
        $this->window = new Subject();
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $observer->onNext($this->window->asObservable());

        $callbackObserver = new CallbackObserver(
            function($value) {
                $this->window->onNext($value);
            },
            function(\Exception $e) use ($observer) {
                $this->window->onError($e);
                $observer->onError($e);
            },
            function() use ($observer) {
                $this->window->onCompleted();
                $observer->onCompleted();
            }
        );

        $boundariesObserver = new CallbackObserver(
            function() use ($observer) {
                $this->window->onCompleted();
                $this->window = new Subject();
                $observer->onNext($this->window->asObservable());
            },
            function(\Exception $e) use ($observer) {
                $observer->onError($e);
            },
            function() use ($observer) {
                $this->window->onCompleted();
                $observer->onCompleted();
            }
        );

        return new BinaryDisposable(
            $observable->subscribe($callbackObserver),
            $this->windowBoundaries->subscribe($boundariesObserver)
        );
    }

}