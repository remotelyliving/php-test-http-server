<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPTestHttpServer;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Process\Process;

class Server {

    public const STUB_RESPONSE_HEADER = 'X-Stub-Response';

    private const DOCUMENT_ROOT = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public';

    private string $port;

    private string $host;

    private string $documentRoot;

    public ?Process $process = null;

    public function __construct(string $documentRoot = self::DOCUMENT_ROOT, string $host = '127.0.0.1', string $port = '8080')
    {
        $this->documentRoot = $documentRoot;
        $this->port = $port;
        $this->host = $host;
    }

    public function __destruct()
    {
        $this->stop();
    }

    public function getDocumentRoot() : string
    {
        return $this->documentRoot;
    }

    public function getServerUri() : string
    {
        return 'http://' . $this->host . ':' . $this->port;
    }

    public static function addStubResponse(RequestInterface $request, ResponseInterface $response) : RequestInterface {

        return $request->withHeader(self::STUB_RESPONSE_HEADER, self::encodeResponseStub($response));

    }

    public static function encodeResponseStub(ResponseInterface $response) : string
    {
        return ResponseWrapper::fromPSRResponse($response)->toBase64();
    }

    public static function decodeResponseStub(string $response) : ResponseInterface
    {
        return \unserialize(\base64_decode($response), ['allowed_classes' => [ResponseInterface::class]]);
    }

    public function start() : void
    {
        if ($this->process === null) {
            $this->process = Process::fromShellCommandline("exec php -S {$this->host}:{$this->port} -t {$this->documentRoot}");
            $this->process->setTimeout(null);
        }

        $this->process->start();

        do {
            $status = (new Process(['curl', $this->getServerUri()]))->run();
        } while($status !== 0);

    }

    public function stop() : void
    {
        $this->process->stop(3, SIGKILL);
        $this->process = null;
    }

}