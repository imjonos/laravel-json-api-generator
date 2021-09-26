<?php
/**
 * Eugeny Nosenko 2021
 * https://toprogram.ru
 * info@toprogram.ru
 */

namespace Nos\JsonApiGenerator\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use  Tests\CreatesApplication;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * @var string
     */
    protected string $token = '';

    protected ?Client $apiClient = null;

    /**
     * Setup method
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Ajax request
     *
     * @param string $method
     * @param string $url
     * @param array $parameters
     * @param string $token
     * @return TestResponse
     */
    protected function ajax(string $method = 'get', string $url = '', array $parameters = [], string $token = ''): TestResponse
    {
        $headers = ['HTTP_X-Requested-With' => 'XMLHttpRequest'];

        if ($token) {
            $headers = [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ];
        }

        return $this->json(
            $method, $url, $parameters, $headers
        );
    }


    /**
     * API request
     *
     * @param string $method
     * @param string $url
     * @param array $parameters
     * @param string $token
     * @return TestResponse
     */
    protected function APIRequest(string $method = 'get', string $url = '', array $parameters = []): TestResponse
    {
        if (!$this->token) {
            $this->token = $this->getClientCredentialsToken();
        }
        return $this->ajax($method, $url, $parameters, $this->token);
    }

    /**
     * Get API token
     *
     * @return string
     */
    protected function getClientCredentialsToken(): string
    {
        $client = $this->getApiClient();
        $response = $this->json('post', '/oauth/token', [
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => '*',
            'grant_type' => 'client_credentials',
        ]);

        return $response->json('access_token');
    }

    /**
     * Get api client
     * @return Client
     */
    protected function getApiClient(): Client
    {
        if (!$this->apiClient) {
            $this->addApiClients();
            $this->apiClient = Client::where('name', 'TestClient1')->first();
            //$clientRepo = new ClientRepository();
            //$this->apiClient = $clientRepo->createPasswordGrantClient(null, 'Test App ' . Str::random(5), url('/' . Str::random(5)));
        }
        return $this->apiClient;
    }

    /**
     * Add test clients
     */
    public function addApiClients()
    {
        Artisan::call('passport:install', ['-vvv' => true]);
        Artisan::call('passport:client --client --name=TestClient1 --no-interaction');
        Artisan::call('passport:client --client --name=TestClient2 --no-interaction');
        Artisan::call('passport:client --client --name=TestClient3 --no-interaction');
    }
}
