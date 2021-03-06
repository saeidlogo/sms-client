<?php

namespace Moontius\LaravelSMS;

use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Moontius\LaravelSMS\Drivers\Nexmo;
use Moontius\LaravelSMS\Drivers\KavehNegar;
use Moontius\LaravelSMS\Drivers\Kannel;
use Moontius\LaravelSMS\Client;

/**
 * Service provider for the SMS service
 */
class SMSServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {
        $this->publishes([
            __DIR__ . '/config.php' => config_path('sms.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton('sms', function ($app, $params) {
            $drivers = config('sms.drivers');
            $number = $params['number'];
            $trunk = $this->find_trunk($number);
            $prefered = (isset($params['prefered']) && !empty($params['prefered'])) ? $params['prefered'] : $trunk['driver'];
            //TO DO handle $trunk null value

            $driver_config = $drivers[$prefered];

            //config sender
            $from = null;
            $config = array();
            $did_sender = isset($trunk['did_provider']) ? $trunk['did_provider'] : false;
            if ($did_sender) {
                $from = isset($trunk['cli_ovesender']) ? $trunk['cli_ovesender'] : null;
            } else {
                $from = isset($trunk['cli_override']) ? $trunk['cli_override'] : null;
            }

            if (!is_null($from)) {
                $config['from'] = $from;
            }

            $api_key = isset($driver_config['api_key']) ? $driver_config['api_key'] : null;
            if (isset($trunk['api_key'])) {
                $api_key = $trunk['api_key'];
            }

            switch ($prefered) {
                case 'kavehnegar':
                    if (!is_null($api_key)) {
                        $config['api_token'] = $api_key;
                    }
                    $driver = new KavehNegar(new GuzzleClient, new GuzzleResponse, $config);
                    break;
                case 'nexmo':
                    $api_secret = $driver_config['api_secret'];
                    if (isset($trunk['api_secret'])) {
                        $api_secret = $trunk['api_secret'];
                    }
                    $config['api_secret'] = $api_secret;

                    $driver = new Nexmo(new GuzzleClient, new GuzzleResponse, $config);
                    break;
                case 'kannel':
                    $username = $driver_config['username'];
                    $password = $driver_config['password'];

                    if (isset($trunk['username'])) {
                        $username = $trunk['username'];
                    }
                    if (isset($trunk['password'])) {
                        $password = $trunk['password'];
                    }

                    $url = $driver_config['url'];

                    $ip = '';

                    if (isset($trunk['ip'])) {
                        $ip = $trunk['ip'];
                    }

                    $driver = new Kannel(
                            new GuzzleClient, new GuzzleResponse, [
                        'username' => $username,
                        'password' => $password,
                        'url' => $url,
                        'from' => $from,
                        'ip' => $ip,
                            ]
                    );
                    break;
                default:
                    break;
            }
            return new Client($driver);
        });

        $this->app->bind(Client::class, function ($app) {
            return $app['sms'];
        });
    }

    public function get_number($phone_enum) {
        if (preg_match('/^\+([1-9][0-9]{1,})/i', $phone_enum, $phone)) {
            return $phone[1];
        } else {
            return false;
        }
    }

    public function find_trunk($phone_enum) {
        $routes = config('sms.route_prefix');
        $mobile=$this->get_number($phone_enum);
        foreach ($routes as $key => $value) {
            if (preg_match('/^' . $key . '\d+/s', $mobile) == true) {
                return config('sms.trunks.' . $value);
            }
        }
        return null;
    }

}
