<?php

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Http\Request;
use Engelsystem\Middleware\LegacyMiddleware;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class LegacyMiddlewareTest extends TestCase
{
    /**
     * @covers \Engelsystem\Middleware\LegacyMiddleware::__construct
     * @covers \Engelsystem\Middleware\LegacyMiddleware::process
     */
    public function testRegister()
    {
        /** @var ContainerInterface|MockObject $container */
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        /** @var LegacyMiddleware|MockObject $middleware */
        $middleware = $this->getMockBuilder(LegacyMiddleware::class)
            ->setConstructorArgs([$container])
            ->setMethods(['loadPage', 'renderPage'])
            ->getMock();
        /** @var Request|MockObject $defaultRequest */
        $defaultRequest = $this->createMock(Request::class);
        /** @var ParameterBag|MockObject $parameters */
        $parameters = $this->createMock(ParameterBag::class);
        /** @var ResponseInterface|MockObject $response */
        $response = $this->getMockForAbstractClass(ResponseInterface::class);
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);

        $middleware->expects($this->exactly(2))
            ->method('loadPage')
            ->withConsecutive(['user_worklog'], ['login'])
            ->willReturnOnConsecutiveCalls(
                ['title', 'content'],
                ['title2', 'content2']
            );

        $middleware->expects($this->exactly(3))
            ->method('renderPage')
            ->withConsecutive(
                ['user_worklog', 'title', 'content'],
                ['404', 'Page not found'],
                ['login', 'title2', 'content2']
            )
            ->willReturn($response);

        $container->expects($this->atLeastOnce())
            ->method('get')
            ->with('request')
            ->willReturn($defaultRequest);

        $defaultRequest->query = $parameters;
        $defaultRequest->expects($this->once())
            ->method('path')
            ->willReturn('user-worklog');

        $parameters->expects($this->exactly(3))
            ->method('get')
            ->with('p')
            ->willReturnOnConsecutiveCalls(
                null,
                'foo',
                '/'
            );

        $middleware->process($request, $handler);
        $middleware->process($request, $handler);
        $middleware->process($request, $handler);
    }
}
