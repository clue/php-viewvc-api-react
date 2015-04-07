<?php

use React\Promise\Deferred;

require __DIR__ . '/../vendor/autoload.php';

class TestCase extends PHPUnit_Framework_TestCase
{
    protected function expectCallableOnce()
    {
        $mock = $this->createCallableMock();

        $mock
            ->expects($this->once())
            ->method('__invoke');

        return $mock;
    }

    protected function expectCallableOnceWith($value)
    {
        $mock = $this->createCallableMock();

        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->equalTo($value));

        return $mock;
    }

    protected function expectCallableNever()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->never())
            ->method('__invoke');

        return $mock;
    }

    /**
     * @link https://github.com/reactphp/react/blob/master/tests/React/Tests/Socket/TestCase.php (taken from reactphp/react)
     */
    protected function createCallableMock()
    {
        return $this->getMock('CallableStub');
    }

    protected function expectPromiseResolve($promise)
    {
        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);

        $promise->then($this->expectCallableOnce(), $this->expectCallableNever());

        return $promise;
    }

    protected function expectPromiseResolveWith($value, $promise)
    {
        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);

        $promise->then($this->expectCallableOnceWith($value), $this->expectCallableNever());

        return $promise;
    }

    protected function expectPromiseReject($promise)
    {
        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);

        $promise->then($this->expectCallableNever(), $this->expectCallableOnce());

        return $promise;
    }

    protected function createPromiseResolved($value = null)
    {
        $deferred = new Deferred();
        $deferred->resolve($value);

        return $deferred->promise();
    }

    protected function createPromiseRejected($value = null)
    {
        $deferred = new Deferred();
        $deferred->reject($value);

        return $deferred->promise();
    }
}

class CallableStub
{
    public function __invoke()
    {
    }
}
