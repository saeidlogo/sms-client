<?php

namespace Moontius\LaravelSMS;

use GuzzleHttp\ClientInterface as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use Matthewbdaly\SMS\Contracts\Driver;
use Matthewbdaly\SMS\Exceptions\DriverNotConfiguredException;

/**
 * Driver for KavehNegar.
 */
class Kannel implements Driver {

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
    public $username;
    public $password;
    public $from;
    public $url;
    public $ip;

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
        if (!array_key_exists('username', $config) || !array_key_exists('password', $config)) {
            throw new DriverNotConfiguredException();
        }
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->from = $config['from'];
        $this->url = $config['url'];
        $this->ip = $config['ip'];
    }

    /**
     * Get driver name.
     *
     * @return string
     */
    public function getDriver(): string {
        return 'Kannel';
    }

    /**
     * Get endpoint URL.
     *
     * @return string
     */
    public function getEndpoint(): string {
        $url = str_replace('[ip]', $this->ip, $this->url);
        $url = str_replace('[username]', $this->username, $url);
        $url = str_replace('[password]', $this->password, $url);
        $url = str_replace('[from]', $this->from, $url);
        return $url;
    }

    /**
     * Send the SMS.
     *
     * @param array $message An array containing the message.
     *
     *
     * @return boolean
     */
    public function sendRequest(array $message): bool {
        try {
            $end_point = $this->getEndpoint();
            $end_point = str_replace('[to]', $message['to'], $end_point);
            $end_point = str_replace('[text]', urlencode($message['text']), $end_point);
            $this->response = $this->client->request('GET', $end_point);
        } catch (\Exception $e) {
            report($e);
            return false;
        }

        return $this->response->getStatusCode() == 202;
    }

}
