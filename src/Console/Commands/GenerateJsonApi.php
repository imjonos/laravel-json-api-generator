<?php

namespace Nos\JsonApiGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class GenerateJsonApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jsonApi:generate {table : Table name from DB} {--route=v1} {--force=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Json Api By Model';

    /**
     * @var string
     */
    protected string $tableName;

    /**
     * @var int
     */
    protected int $force;

    /**
     * @var string
     */
    protected string $route;

    /**
     * @var array
     */
    protected array $templateVars = [];


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->tableName = $this->argument('table');
        $this->route = $this->option('route');
        $this->force = $this->option('force');

        $modelNamePlural = ucfirst(Str::camel($this->tableName));
        $modelName = Str::singular($modelNamePlural);
        $modelNameLowerCase = Str::lower($modelName);
        $pathName = ($this->route) ? ucfirst($this->route) : '';
        $namespacePath =  ($pathName) ? '\\'.$pathName : '';
        $customPath =  ($pathName) ? '/'.$pathName : '';
        $modelsNamespace = config('jsonapigenerator.modelsNamespace');

        $this->templateVars = [
            'modelName' => $modelName,
            'modelNamePlural' => $modelNamePlural,
            'modelsNamespace' => $modelsNamespace,
            'modelNameLowerCase' => $modelNameLowerCase,
            'modelNamePluralLowerCase' => $this->tableName,
            'pathName' => $pathName,
            'namespacePath' => $namespacePath,
            'customPath' => $customPath
        ];

        $this->info('Json Api Generator');
        $this->info('Creating controller...');
        $this->controller();
        $this->info('Creating requests...');
        $this->requests();
        $this->info('Creating resources...');
        $this->resources();
        $this->info('Creating routes...');
        $this->routes();
        $this->info('Creating test...');
        $this->test();
        $this->info('Done');

    }

    /**
     * Read the template
     * @param $type
     * @return false|string
     */
    protected function getStub(string $type)
    {
        return File::get(__DIR__ . "/../../../resources/stubs/$type.stub");
    }

    /**
     * Write to file
     * @param string $path
     * @param string $content
     * @param bool $overwrite
     */
    protected function writeToFile(string $path, string $content, $overwrite = false): void
    {
        if ($this->force == 1 || !file_exists($path) || $overwrite) {
            File::put($path, $content);
        }
    }

    /**
     * Make template from file stub
     * @param string $stubName
     * @return string
     */
    protected function makeTemplate(string $stubName): string
    {
        $keys = [];
        $vars = [];

        foreach ($this->templateVars AS $key => $value){
            $keys[] = "{{{$key}}}";
            $vars[] = $value;
        }

        return str_replace(
            $keys,
            $vars,
            $this->getStub($stubName)
        );
    }

    /**
     * Create the controller
     */
    protected function controller(): void
    {
        $customPath =  $this->templateVars['customPath'];
        $singularName = $this->templateVars['modelName'];
        $path = app_path("Http/Controllers/Api{$customPath}/");

        $controllerTemplate = $this->makeTemplate('Http/Controllers/Controller.php');

        if (!file_exists($path))
            mkdir($path, 0755, true);
        $this->writeToFile("$path{$singularName}Controller.php", $controllerTemplate);
    }

    /**
     * Generate resources
     */
    protected function resources():void
    {
        $modelName = $this->templateVars['modelName'];
        $modelNamePlural = $this->templateVars['modelNamePlural'];
        $customPath =  $this->templateVars['customPath'];

        $resources = [
            'ModelIdentifierResource' => $modelName.'IdentifierResource',
            'ModelRelationshipResource' => $modelName.'RelationshipResource',
            'ModelResource' => $modelName.'Resource',
            'ModelsResource' => $modelNamePlural.'Resource'
        ];

        $path = app_path("Http/Resources/Api{$customPath}/{$modelName}/");

        if (!file_exists($path))
            mkdir($path, 0755, true);

        foreach ($resources AS $key => $resource){
            $template = $this->makeTemplate("Http/Resources/{$key}.php");
            $this->writeToFile($path.$resource.".php", $template);
        }
    }

    /**
     * Generate resources
     */
    protected function requests():void
    {
        $modelName = $this->templateVars['modelName'];
        $customPath =  $this->templateVars['customPath'];

        $requests = [
            'IndexRequest',
            'StoreRequest',
            'UpdateRequest'
        ];

        $path = app_path("Http/Requests/Api{$customPath}/{$modelName}/");

        if (!file_exists($path))
            mkdir($path, 0755, true);

        foreach ($requests AS $request){
            $template = $this->makeTemplate("Http/Requests/{$request}.php");
            $this->writeToFile($path.$request.".php", $template);
        }
    }

    /**
     * Create the controller test
     */
    protected function test(): void
    {
        $customPath =  $this->templateVars['customPath'];
        $singularName = $this->templateVars['modelName'];
        $path = base_path("tests/Feature/Api{$customPath}/");

        $testTemplate = $this->makeTemplate('Tests/Feature/Test.php');

        if (!file_exists($path))
            mkdir($path, 0755, true);
        $this->writeToFile("$path{$singularName}ControllerTest.php", $testTemplate);
    }


    /**
     * Create the routes
     */
    protected function routes(): void
    {
        $namespacePath = $this->templateVars['namespacePath'];
        $modelName = $this->templateVars['modelName'];
        $modelNameLowerCase = $this->templateVars['modelNameLowerCase'];
        $route = $this->route . "/" . $this->tableName;
        $routesPath = base_path('routes/api.php');

        $routesFile = File::get($routesPath);

        $routes = [
            "Route::pattern('{$modelNameLowerCase}', '[0-9]+');",
            "Route::resource('" . $route . "', \App\Http\Controllers\Api" . $namespacePath . "\\". $modelName . "Controller::class, ['except'=> ['edit', 'create']]);",
        ];

        foreach ($routes as $route) {
            if (!stristr($routesFile, $route)) {
                File::append($routesPath, $route . PHP_EOL);
            }
        }
    }
}
