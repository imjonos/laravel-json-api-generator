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
        $this->artisan('jsonApi:generate', ['table' => 'posts', '--force' => 1]);
        $this->assertFileExists(app_path("Http/Controllers/Api/V1/").'PostController.php');
        $this->assertFileExists(app_path("Http/Resources/Api/V1/").'PostIdentifierResource.php');
        $this->assertFileExists(app_path("Http/Resources/Api/V1/").'PostRelationshipResource.php');
        $this->assertFileExists(app_path("Http/Resources/Api/V1/").'PostResource.php');
        $this->assertFileExists(app_path("Http/Resources/Api/V1/").'PostsResource.php');
    }
}