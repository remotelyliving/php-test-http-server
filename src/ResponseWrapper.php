<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPTestHttpServer;

use Psr\Http\Message\ResponseInterface;

class ResponseWrapper implements \Serializable
{
    private int $statusCode = 200;
    private $reasonPhrase = 'OK';
    private array $headers = [];
    private string $body = '';
    private string $protocolVersion = '1.1';

    public static function fromPSRResponse(ResponseInterface $response) : self
    {
        $response->getBody()->rewind();
        $responseWrapper = new self();
        $responseWrapper->statusCode = (int) $response->getStatusCode();
        $responseWrapper->reasonPhrase = (string) $response->getReasonPhrase();
        $responseWrapper->headers = (array) $response->getHeaders();
        $responseWrapper->body = $response->getBody()->getContents();
        $responseWrapper->protocolVersion = $response->getProtocolVersion();

        return $responseWrapper;
    }

    public static function fromBase64(string $encoded) : self
    {
        return \unserialize(\base64_decode($encoded), ['allowed_classes' => [self::class]]);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function serialize(): string
    {
        return \serialize([
            'statusCode' => $this->getStatusCode(),
            'reasonPhrase' => $this->getReasonPhrase(),
            'headers' => $this->getHeaders(),
            'body' => $this->getBody(),
            'protocolVersion' => $this->getProtocolVersion(),
                          ]);
    }

    public function unserialize($serialized)
    {
        $unserialized = \unserialize($serialized, ['allowed_classes' => [self::class]]);
        $this->statusCode = $unserialized['statusCode'];
        $this->reasonPhrase = $unserialized['reasonPhrase'];
        $this->headers = $unserialized['headers'];
        $this->body = $unserialized['body'];
        $this->protocolVersion = $unserialized['protocolVersion'];
    }

    public function toBase64() : string
    {
        return base64_encode(\serialize($this));
    }
}