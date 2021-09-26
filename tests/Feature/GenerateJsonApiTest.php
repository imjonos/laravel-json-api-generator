<?php

namespace Nos\JsonApiGenerator\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GenerateJsonApiTest extends TestCase
{
    /**
     * Generate test
     *
     * @return void
     */
    public function testGenerate()
    {
        $this->artisan('json-api:generate', ['table' => 'users', '--force' => 1]);
        $this->assertFileExists(app_path("Http/Controllers/Api/V1/").'UserController.php');
        $this->assertFileExists(app_path("Http/Resources/Api/V1/User/").'UserIdentifierResource.php');
        $this->assertFileExists(app_path("Http/Resources/Api/V1/User/").'UserRelationshipResource.php');
        $this->assertFileExists(app_path("Http/Resources/Api/V1/User/").'UserResource.php');
        $this->assertFileExists(app_path("Http/Resources/Api/V1/User/").'UsersResource.php');
        $this->assertFileExists(app_path("Http/Requests/Api/V1/User/").'IndexRequest.php');
        $this->assertFileExists(app_path("Http/Requests/Api/V1/User/").'StoreRequest.php');
        $this->assertFileExists(app_path("Http/Requests/Api/V1/User/").'UpdateRequest.php');
        $this->assertFileExists(base_path("tests/Feature/Api/V1/").'UserControllerTest.php');
        $this->assertFileExists(base_path("database/factories/").'UserFactory.php');
        $this->assertFileExists(base_path("database/seeders/").'UsersTableSeeder.php');
    }
}
