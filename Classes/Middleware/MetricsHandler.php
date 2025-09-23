<?php

declare(strict_types=1);

namespace Mfd\Prometheus\Middleware;

use Mfd\Prometheus\Service\MetricsCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MetricsHandler implements MiddlewareInterface
{
    private const string METRICS_PATH = '/metrics';

    public function __construct(
        private readonly MetricsCollector $metricsCollector
    ) {
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        if ($path !== self::METRICS_PATH) {
            return $handler->handle($request);
        }

        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $authToken = (string)$extensionConfiguration->get('prometheus', 'authToken');

        if ($authToken !== '' && $request->getHeaderLine('Authorization') !== 'Bearer ' . $authToken) {
            return new HtmlResponse(
                'Unauthorized',
                401,
                [
                    'Content-Type' => 'text/plain; version=0.0.4',
                    'WWW-Authenticate' => 'Bearer',
                ]
            );
        }

        // This is a metrics request, gather metrics and return them
        $metrics = $this->metricsCollector->collectMetrics();

        $body = new Stream('php://temp', 'rw');
        $body->write($metrics);
        $body->rewind();

        return new Response(
            $body,
            200,
            [
                'Content-Type' => 'text/plain; version=0.0.4',
                'Cache-Control' => 'no-cache, private, no-store, must-revalidate',
            ]
        );
    }
}
