<?php

namespace Moontius\LaravelSMS\Drivers;

use GuzzleHttp\ClientInterface as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use Moontius\LaravelSMS\Contracts\Driver;
use Moontius\LaravelSMS\Exceptions\DriverNotConfiguredException;
use Moontius\LaravelSMS\SmsResult;
use Moontius\LaravelSMS\SMSException;

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
     *
     * @return SmsResult
     */
    public function sendRequest(array $message): SmsResult {
        $result = new SmsResult();
        try {
            $message['api_key'] = $this->apiKey;
            $message['api_secret'] = $this->apiSecret;
            $message['from'] = $this->from;
            unset($message['content']);
            $this->response = $this->client->request('POST', $this->getEndpoint() . '?' . http_build_query($message));
        } catch (\Exception $e) {
            throw new SMSException($e->getMessage(), $e->getCode());
        }

        if ($this->response->getStatusCode() == 200) {
            $value = json_decode($this->response->getBody(), true);

            if ($value['messages'][0]['status'] == 0) {
                $result->messageid = $value['messages'][0]['message-id'];
                $result->network = $value['messages'][0]['network'];
                $result->cost = $value['messages'][0]['message-price'];
                return $result;
            }
        }
        throw new SMSException('unable to generate response after sending sms', $this->response->getStatusCode());
    }

}
