<?php

declare(strict_types=1);

namespace Fratily\Framework\Http;

use LogicException;
use Psr\Http\Message\ResponseInterface;

class ResponseSender implements ResponseSenderInterface
{
    public const STATUS_PHRASES = ResponseSenderInterface::STATUS_PHRASES + [
        418 => 'I\'m a teapot' // It's a famous joke and should be available.
    ];

    /**
     * @param int|null $body_seek_length
     *
     * @phpstan-param positive-int|null $body_seek_length
     */
    public function __construct(protected int|null $body_seek_length = null)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function send(ResponseInterface $response): void
    {
        $this->sendHeaders($response);
        $this->sendBody($response);
    }

    public function sendHeaders(ResponseInterface $response): void
    {
        if (headers_sent()) {
            return;
        }

        header(
            sprintf(
                'HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase() !== ''
                    ? $response->getReasonPhrase()
                    : static::STATUS_PHRASES[$response->getStatusCode()] ?? 'unknown status code'
            ),
            true,
            $response->getStatusCode()
        );

        foreach (array_keys($response->getHeaders()) as $name) {
            if (strtolower($name) === 'set-cookie') {
                foreach ($response->getHeader($name) as $cookie_value) {
                    header('Set-Cookie: ' . $cookie_value, false);
                }
                continue;
            }

            header($name . ': ' . $response->getHeaderLine($name), true);
        }
    }

    public function sendBody(ResponseInterface $response): void
    {
        $body = $response->getBody();

        if (!$body->isReadable()) {
            // TODO: 例外をちゃんとしたものにする
            throw new LogicException();
        }

        if (!$body->isSeekable() || $this->body_seek_length === null) {
            echo (string)$body;
            return;
        }

        $body->rewind();
        while (!$body->eof()) {
            echo $body->read($this->body_seek_length);
            $body->seek($this->body_seek_length, SEEK_CUR);
        }
    }
}
