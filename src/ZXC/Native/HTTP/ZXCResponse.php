<?php

namespace ZXC\Native\HTTP;

use ZXC\Interfaces\Psr\Http\Message\ResponseInterface;
use ZXC\ZXC;
use ZXC\Native\PSR\Stream;
use ZXC\Patterns\Singleton;
use ZXC\Native\PSR\Message;
use ZXC\Native\PSR\Response;
use InvalidArgumentException;

class ZXCResponse
{
    use Singleton;

    /**
     * @param ResponseInterface $response
     * @method sendResponse
     */
    public static function sendResponse(ResponseInterface $response)
    {
        if (!headers_sent()) {
            foreach ($response->getHeaders() as $name => $values) {
                $first = stripos($name, 'Set-Cookie') === 0 ? false : true;
                foreach ($values as $value) {
                    header(sprintf('%s: %s', trim($name), trim($value)), $first);
                    $first = false;
                }
            }
            header(sprintf('HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ), true, $response->getStatusCode());

            $body = $response->getBody();
            if ($body->isSeekable()) {
                $body->rewind();
            }
            while (!$body->eof()) {
                echo $body->read(4096);
                if (connection_status() != CONNECTION_NORMAL) {
                    break;
                }
            }
        }
    }

    public static function sendError(ZXC $ZXC, $code, $msg)
    {
        http_response_code($code);
    }

    /**
     * @param ResponseInterface $response
     * @param array $options
     * @method sendFile
     * @return Message|Response
     */
    public static function sendFile(ResponseInterface $response, array $options = [])
    {
        if (!isset($options['fileName'])) {
            throw new InvalidArgumentException('Parameter "fileName" is undefined in $options');
        }
        if (!isset($options['filePath'])) {
            throw new InvalidArgumentException('Parameter "filePath" is undefined in $options');
        }
        /**
         * @var Response $responseNew
         */
        $responseNew = $response->withHeader('Content-Description', 'File Transfer');
        $responseNew = $responseNew->withHeader('Content-Type', 'application/octet-stream');
        $responseNew = $responseNew->withHeader(
            'Content-Disposition',
            'attachment; filename=' . $options['fileName']
        );
        $responseNew = $responseNew->withHeader('Content-Transfer-Encoding', 'binary');
        $responseNew = $responseNew->withHeader('Expires', '0');
        $responseNew = $responseNew->withHeader('Cache-Control', 'must-revalidate');
        $responseNew = $responseNew->withHeader('Pragma', 'public');
        $body = new Stream($options['filePath']);
        $responseNew = $responseNew->withBody($body);
        $responseNew->offOutputBuffering();
        return $responseNew;
    }
}
