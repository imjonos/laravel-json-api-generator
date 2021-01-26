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

        $this->templateVars = [
            'modelName' => $modelName,
            'modelNamePlural' => $modelNamePlural,
            'modelNameLowerCase' => $modelNameLowerCase,
            'modelNamePluralLowerCase' => $this->tableName,
            'pathName' => $pathName,
            'namespacePath' => $namespacePath
        ];

        $this->controller();
        $this->resources();
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
        $pathName = $this->templateVars['pathName'];
        $customPath =  ($pathName) ? $pathName . '/' : '';
        $singularName = $this->templateVars['modelName'];
        $path = app_path("Http/Controllers/Api/{$customPath}");

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


        $pathName = $this->templateVars['pathName'];
        $modelName = $this->templateVars['modelName'];
        $modelNamePlural = $this->templateVars['modelNamePlural'];
        $customPath =  ($pathName) ? $pathName . '/' : '';

        $resources = [
            'ModelIdentifierResource' => $modelName.'IdentifierResource',
            'ModelRelationshipResource' => $modelName.'RelationshipResource',
            'ModelResource' => $modelName.'Resource',
            'ModelsResource' => $modelNamePlural.'Resource'
        ];

        $path = app_path("Http/Resources/Api/{$customPath}");

        if (!file_exists($path))
            mkdir($path, 0755, true);

        foreach ($resources AS $key => $resource){
            $template = $this->makeTemplate("Http/Resources/{$key}.php");
            $this->writeToFile($path.$resource.".php", $template);
        }
    }


    /**
     * Create the routes
     * @param string $name
     */
    protected function routes(string $name): void
    {
        $pathName = ($this->route) ? ucfirst($this->route) : '';
        $namespacePath =  ($pathName) ? $pathName . '\\' : '';
        $routesFile = File::get(base_path('routes/web.php'));
        $singular = $namespacePath . Str::singular($name);
        $singularLowerCase = strtolower(Str::singular($name));
        $route = $this->route . "/" . $this->tableName;

        $routes = [
            "Route::pattern('{$singularLowerCase}', '[0-9]+');",
            "Route::resource('" . $route . "', '{$singular}Controller');",
        ];

        foreach ($routes as $route) {
            if (!stristr($routesFile, $route)) {
                File::append(base_path('routes/web.php'), $route . PHP_EOL);
            }
        }
    }
}
