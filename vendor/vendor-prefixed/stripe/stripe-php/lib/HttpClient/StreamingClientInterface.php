<?php
/**
 * @license MIT
 *
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace SimpleSecureWP\SimpleSecureStripe\Stripe\HttpClient;

interface StreamingClientInterface
{
    /**
     * @param 'delete'|'get'|'post' $method The HTTP method being used
     * @param string $absUrl The URL being requested, including domain and protocol
     * @param array $headers Headers to be used in the request (full strings, not KV pairs)
     * @param array $params KV pairs for parameters. Can be nested for arrays and hashes
     * @param bool $hasFile Whether or not $params references a file (via an @ prefix or
     *                         CURLFile)
     * @param callable $readBodyChunkCallable a function that will be called with chunks of bytes from the body if the request is successful
     *
     * @throws \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiConnectionException
     * @throws \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\UnexpectedValueException
     *
     * @return array an array whose first element is raw request body, second
     *    element is HTTP status code and third array of HTTP headers
     */
    public function requestStream($method, $absUrl, $headers, $params, $hasFile, $readBodyChunkCallable);
}
