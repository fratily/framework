<?php

declare(strict_types=1);

namespace Fratily\Framework\Http;

use LogicException;
use Psr\Http\Message\ResponseInterface;

class ResponseSender implements ResponseSenderInterface
{
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
            sprintf('HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                // TODO: 空文字の時の対応
                $response->getReasonPhrase()
            ),
            true,
            // NOTE: この指定が必要なのか調べる
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

        if ($body->isSeekable()) {
            // NOTE: いらない気がする。
            $body->rewind();
        }

        // NOTE: とても大きなbodyの時にちょっとずつ出力するようにしたほうが良い？
        echo (string)$body;
    }
}
