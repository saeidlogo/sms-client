<?php

namespace Moontius\LaravelSMS\Drivers;

use GuzzleHttp\ClientInterface as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Psr\Http\Message\ResponseInterface;
use Moontius\LaravelSMS\Contracts\Driver;
use Moontius\LaravelSMS\Exceptions\DriverNotConfiguredException;

/**
 * Driver for Nexmo.
 * https://developer.nexmo.com/api/sms
 */
class Nexmo implements Driver {

    /**
     * Guzzle client.
     *
     * @var
     */
    public $client;

    /**
     * Guzzle response.
     *
     * @var
     */
    public $response;

    /**
     * Endpoint.
     *
     * @var
     */
    public $endpoint = 'https://rest.nexmo.com/sms/json';

    /**
     * API Key.
     *
     * @var
     */
    public $apiKey;

    /**
     * API Secret.
     *
     * @var
     */
    public $apiSecret;
    public $from;

    /**
     * Constructor.
     *
     * @param GuzzleClient      $client   The Guzzle Client instance.
     * @param ResponseInterface $response The response instance.
     * @param array             $config   The configuration array.
     * @throws DriverNotConfiguredException Driver not configured correctly.
     *
     * @return void
     */
    public function __construct(GuzzleClient $client, ResponseInterface $response, array $config) {
        $this->client = $client;
        $this->response = $response;
        if (!array_key_exists('api_key', $config) || !array_key_exists('api_secret', $config)) {
            throw new DriverNotConfiguredException();
        }
        $this->apiKey = $config['api_key'];
        $this->apiSecret = $config['api_secret'];

        $this->from = $config['from'];
    }

    /**
     * Get driver name.
     *
     * @return string
     */
    public function getDriver(): string {
        return 'Nexmo';
    }

    /**
     * Get endpoint URL.
     *
     * @return string
     */
    public function getEndpoint(): string {
        return $this->endpoint;
    }

    /**
     * Send the SMS.
     *
     * @param array $message An array containing the message.
     *
     * @throws \Matthewbdaly\SMS\Exceptions\ClientException  Client exception.
     * @throws \Matthewbdaly\SMS\Exceptions\ServerException  Server exception.
     * @throws \Matthewbdaly\SMS\Exceptions\RequestException Request exception.
     * @throws \Matthewbdaly\SMS\Exceptions\ConnectException Connect exception.
     *
     * @return boolean
     */
    public function sendRequest(array $message): bool {
        try {
            $message['api_key'] = $this->apiKey;
            $message['api_secret'] = $this->apiSecret;
            $message['from'] = $this->from;
            unset($message['content']);
            $this->response = $this->client->request('POST', $this->getEndpoint() . '?' . http_build_query($message));
        } catch (ClientException $e) {
            throw new \Moontius\LaravelSMS\Exceptions\ClientException();
        } catch (ServerException $e) {
            throw new \Moontius\LaravelSMS\Exceptions\ServerException();
        } catch (ConnectException $e) {
            throw new \Moontius\LaravelSMS\Exceptions\ConnectException();
        } catch (RequestException $e) {
            throw new \Moontius\LaravelSMS\Exceptions\RequestException();
        }

        return $this->response->getStatusCode() == 200;
    }

}
