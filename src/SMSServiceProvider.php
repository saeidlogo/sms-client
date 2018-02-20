<?php

namespace Moontius\LaravelSMS;

use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Matthewbdaly\SMS\Drivers\Log as LogDriver;
use Moontius\LaravelSMS\Nexmo;
use Moontius\LaravelSMS\KavehNegar;
use Moontius\LaravelSMS\Kannel;
use Matthewbdaly\SMS\Client;

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
            $config = $app['config'];
            $default = config('sms.default');
            $drivers = config('sms.drivers');
            $number = $params['number'];
            $trunk = $this->find_trunk($config, $number);
            $prefered = isset($params['prefered']) ? $params['prefered'] : $trunk['driver'];
            //TO DO handle $trunk null value

            $driver_config = $drivers[$prefered];

            //config sender
            $from = null;
            $config = array();
            $config['type'] = $type;
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
                    $driver = new LogDriver(
                            $app['log']
                    );
                    break;
            }
            return new Client($driver);
        });

        $this->app->bind('Matthewbdaly\SMS\Client', function ($app) {
            return $app['sms'];
        });
    }

    public function find_trunk($config, $mobile) {
        $routes = config('sms.route_prefix');
        foreach ($routes as $key => $value) {
            if (preg_match('/^' . $key . '\d+/s', $mobile) == true) {
                return config('sms.trunks.' . $value);
            }
        }
        return null;
    }

}
