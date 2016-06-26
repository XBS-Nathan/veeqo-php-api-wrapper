<?php namespace Veeqo;

use Guzzle\Service\Loader\JsonLoader;
use GuzzleHttp\Client as BaseClient;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use Symfony\Component\Config\FileLocator;

class Client
{

    const VEEQO_API_URL = 'http://api.veeqo.com';

    /**
     * Guzzle service description
     *
     * @var \Veeqo\Description
     */
    private $description;


    /**
     * Guzzle base client
     *
     * @var \GuzzleHttp\Client
     */
    private $baseClient;


    /**
     * Api client services
     *
     * @var \GuzzleHttp\Command\Guzzle\GuzzleClient
     */
    private $serviceClient;


    /**
     * Veeqo client config settings
     *
     * @var array
     */
    private $settings = [];

    private $apiKey;


    public function __construct(array $settings = array())
    {
        $configDirectory = [ __DIR__ .'/resources' ];
        $this->locator = new FileLocator($configDirectory);
        $this->jsonLoader = new JsonLoader($this->locator);

        $this->settings = [
            'baseUrl' => self::VEEQO_API_URL
        ];

        $this->apiKey = $settings['X-Api-Key'];

        $this->buildClient();
    }


    /**
     * Build new service client from descriptions.
     *
     * @return void
     */
    private function buildClient()
    {
        $this->loadConfig();

        $client = $this->getBaseClient();

        $client->setDefaultOption('headers/X-Api-Key', $this->apiKey);

        $this->serviceClient = new GuzzleClient(
            $client,
            $this->description,
            array(
                'emitter'  => $this->baseClient->getEmitter(),
                'defaults' => $this->settings,
            )
        );
    }

    /**
     * Retrieve Guzzle base client.
     *
     * @return \GuzzleHttp\Client
     */
    private function getBaseClient()
    {
        return $this->baseClient ?: $this->baseClient = $this->loadBaseClient();
    }

    private function loadBaseClient()
    {
        return $this->baseClient = new BaseClient($this->settings);
    }

    /**
     * Load configuration file and parse resources.
     *
     * @return array
     */
    private function loadConfig()
    {
        $description = $this->jsonLoader->load($this->locator->locate('service-config.json'));

        $this->description = new Description(array_merge($description, ['baseUrl' => self::VEEQO_API_URL, ]));
    }

    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->serviceClient, $method], $parameters);
    }
}