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
        $this->artisan('jsonApi:generate', ['table' => 'posts', '--force' => 0]);
        $this->assertFileExists(app_path("Http/Controllers/Api/V1/").'PostController.php');
        $this->assertFileExists(app_path("Http/Resources/Api/V1/Post/").'PostIdentifierResource.php');
        $this->assertFileExists(app_path("Http/Resources/Api/V1/Post/").'PostRelationshipResource.php');
        $this->assertFileExists(app_path("Http/Resources/Api/V1/Post/").'PostResource.php');
        $this->assertFileExists(app_path("Http/Resources/Api/V1/Post/").'PostsResource.php');
        $this->assertFileExists(app_path("Http/Requests/Api/V1/Post/").'IndexRequest.php');
        $this->assertFileExists(app_path("Http/Requests/Api/V1/Post/").'StoreRequest.php');
        $this->assertFileExists(app_path("Http/Requests/Api/V1/Post/").'UpdateRequest.php');
    }
}