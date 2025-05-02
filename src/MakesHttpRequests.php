<?php

namespace Webimpian\BayarcashSdk;

use Webimpian\BayarcashSdk\Exceptions\FailedActionException;
use Webimpian\BayarcashSdk\Exceptions\NotFoundException;
use Webimpian\BayarcashSdk\Exceptions\RateLimitExceededException;
use Webimpian\BayarcashSdk\Exceptions\TimeoutException;
use Webimpian\BayarcashSdk\Exceptions\ValidationException;
use Psr\Http\Message\ResponseInterface;

/**
 * Trait for making HTTP requests to BayarCash API
 */
trait MakesHttpRequests
{
    /**
     * Make a GET request to Bayarcash servers and return the response.
     *
     * @param string $uri The URI to request
     * @return mixed The response data as an array or object
     * @throws \Webimpian\BayarcashSdk\Exceptions\NotFoundException If the resource is not found
     * @throws \Webimpian\BayarcashSdk\Exceptions\ValidationException If validation fails
     * @throws \Webimpian\BayarcashSdk\Exceptions\RateLimitExceededException If rate limit is exceeded
     * @throws \Exception For other errors
     */
    public function get(string $uri)
    {
        return $this->request('GET', $uri);
    }

    /**
     * Make a POST request to Bayarcash servers and return the response.
     *
     * @param string $uri The URI to request
     * @param array $payload The payload to send
     * @return mixed The response data as an array or object
     * @throws \Webimpian\BayarcashSdk\Exceptions\NotFoundException If the resource is not found
     * @throws \Webimpian\BayarcashSdk\Exceptions\ValidationException If validation fails
     * @throws \Webimpian\BayarcashSdk\Exceptions\RateLimitExceededException If rate limit is exceeded
     * @throws \Exception For other errors
     */
    public function post(string $uri, array $payload = [])
    {
        return $this->request('POST', $uri, $payload);
    }

    /**
     * Make a PUT request to Bayarcash servers and return the response.
     *
     * @param string $uri The URI to request
     * @param array $payload The payload to send
     * @return mixed The response data as an array or object
     * @throws \Webimpian\BayarcashSdk\Exceptions\NotFoundException If the resource is not found
     * @throws \Webimpian\BayarcashSdk\Exceptions\ValidationException If validation fails
     * @throws \Webimpian\BayarcashSdk\Exceptions\RateLimitExceededException If rate limit is exceeded
     * @throws \Exception For other errors
     */
    public function put(string $uri, array $payload = [])
    {
        return $this->request('PUT', $uri, $payload);
    }

    /**
     * Make a DELETE request to Bayarcash servers and return the response.
     *
     * @param string $uri The URI to request
     * @param array $payload The payload to send
     * @return mixed The response data
     */
    public function delete($uri, array $payload = [])
    {
        return $this->request('DELETE', $uri, $payload);
    }

    /**
     * Make request to Bayarcash servers and return the response.
     *
     * @param string $verb The HTTP verb to use
     * @param string $uri The URI to request
     * @param array $payload The payload to send
     * @return mixed The response data
     * @throws \Exception If the request fails
     */
    protected function request($verb, $uri, array $payload = [])
    {
        try {
            if (isset($payload['json'])) {
                $payload = ['json' => $payload['json']];
            } else {
                $payload = empty($payload) ? [] : ['form_params' => $payload];
            }

            $response = $this->guzzle->request($verb, $uri, $payload);

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                return $this->handleRequestError($response);
            }

            $responseBody = (string) $response->getBody();

            return json_decode($responseBody, true) ?: $responseBody;
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            throw new \Exception("Connection error: " . $e->getMessage(), $e->getCode(), $e);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                return $this->handleRequestError($e->getResponse());
            }
            
            throw new \Exception("Request error: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Handle the request error.
     *
     * @param ResponseInterface $response The error response
     * @throws FailedActionException If the action failed
     * @throws NotFoundException If the resource was not found
     * @throws RateLimitExceededException If the rate limit was exceeded
     * @throws ValidationException If validation failed
     * @throws \Exception For other errors
     */
    protected function handleRequestError(ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();
        $responseBody = (string) $response->getBody();
        $decodedResponse = json_decode($responseBody, true) ?: $responseBody;
        
        switch ($statusCode) {
            case 422:
                throw new ValidationException($decodedResponse);
            case 404:
                throw new NotFoundException("The requested resource was not found.");
            case 400:
                throw new FailedActionException($decodedResponse);
            case 429:
                $resetTime = $response->hasHeader('x-ratelimit-reset')
                    ? (int) $response->getHeader('x-ratelimit-reset')[0]
                    : null;
                throw new RateLimitExceededException($resetTime);
            default:
                throw new \Exception("HTTP Error: {$statusCode} - {$responseBody}");
        }
    }

    /**
     * Retry the callback or fail after x seconds.
     *
     * @param int $timeout The timeout in seconds
     * @param callable $callback The callback to retry
     * @param int $sleep The sleep time between retries
     * @return mixed The callback result
     * @throws TimeoutException If the timeout is reached
     */
    public function retry($timeout, $callback, $sleep = 5)
    {
        $start = time();
        $attempts = 0;
        $lastError = null;

        do {
            $attempts++;
            
            try {
                if ($output = $callback()) {
                    return $output;
                }
            } catch (\Exception $e) {
                $lastError = $e;
            }

            if (time() - $start < $timeout) {
                sleep($sleep);
                continue;
            }

            break;
        } while (true);

        throw new TimeoutException([
            'message' => 'Operation timed out after ' . $timeout . ' seconds.',
            'attempts' => $attempts,
            'last_error' => $lastError ? $lastError->getMessage() : null
        ]);
    }
}
