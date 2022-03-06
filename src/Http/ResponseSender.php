<?php

declare(strict_types=1);

namespace Fratily\Framework\Http;

use LogicException;
use Psr\Http\Message\ResponseInterface;

class ResponseSender implements ResponseSenderInterface
{
    /**
     * @var string[] Status code reason phrase map.
     * @phpstan-var array<int<100,599>,non-empty-string>
     *
     * @link https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *       HTTP Status Code Registry (version 19)
     */
    public const STATUS_PHRASES = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Content Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot', // It's a famous joke and should be available.
        421 => 'Misdirected Request',
        422 => 'Unprocessable Content',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
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
