<?php

namespace Moontius\LaravelSMS\Drivers;

use GuzzleHttp\ClientInterface as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use Moontius\LaravelSMS\Contracts\Driver;
use Moontius\LaravelSMS\Exceptions\DriverNotConfiguredException;
use Moontius\LaravelSMS\SmsResult;
use Moontius\LaravelSMS\Exceptions\SMSException;

/**
 * Driver for KavehNegar.
 */
class KavehNegar implements Driver {

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
     * API Token.
     *
     * @var
     */
    public $apiToken;
    public $from;
    public $type;

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
        if (!array_key_exists('api_token', $config)) {
            throw new DriverNotConfiguredException();
        }
        $this->apiToken = $config['api_token'];
        $this->from = isset($config['from']) ? $config['from'] : null;
    }

    /**
     * Get driver name.
     *
     * @return string
     */
    public function getDriver(): string {
        return 'KavehNegar';
    }

    /**
     * Get endpoint URL.
     *
     * @return string
     */
    public function getEndpoint(): string {
        switch ($this->type) {
            case 'template':
                return "https://api.kavenegar.com/v1/$this->apiToken/verify/lookup.json";
            default:
        }
        return "https://api.kavenegar.com/v1/$this->apiToken/sms/send.json";
    }

    /**
     * Send the SMS.
     *
     * @param array $message An array containing the message.
     *
     *
     * @return boolean
     */
    public function sendRequest(array $message): SmsResult {
        $result = new SmsResult();
        try {
            $cleanMessage = [];
            $cleanMessage['receptor'] = $message['to'];
            $this->type = isset($message['template']) ? 'template' : 'simple';
            switch ($this->type) {
                case 'template':
                    $cleanMessage['template'] = $message['template'];
                    $cleanMessage['token'] = $message['text'];
                    break;
                default:
                    $cleanMessage['message'] = $message['text'];
                    if (isset($this->from)) {
                        $cleanMessage['sender'] = $this->from;
                    }
                    break;
            }

            $this->response = $this->client->request('POST', $this->getEndpoint(), [
                'form_params' => $cleanMessage
            ]);
        } catch (\Exception $e) {
            throw new SMSException($e->getMessage(), $e->getCode());
        }

        if ($this->response->getStatusCode() == 200) {
            $value = json_decode($this->response->getBody(), true);
            $entries = $value['entries'];
            if (!empty($entries)) {
                $response = $entries[0];
                $result->messageid = $response['messageid'];
                $result->network = $response['sender'];
                $result->cost = $response['cost'];
                return $result;
            }
        }
        throw new SMSException('unable to generate response after sending sms', $this->response->getStatusCode());
    }

}
