<?php

namespace Nos\JsonApiGenerator\Console\Commands;

use Doctrine\DBAL\Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * PHP LEFT MARGIN
 */
define('PHP_MARGIN', '    ');

class GenerateJsonApi extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'json-api:generate {table : Table name from DB} {--route=v1} {--force=0}';

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
     * @var ?Collection
     */
    protected ?Collection $columns = null;


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
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        $this->tableName = Str::lower($this->argument('table'));
        $this->route = $this->option('route');
        $this->force = $this->option('force');

        $modelNamePlural = ucfirst(Str::camel($this->tableName));
        $modelNameSingularUnderlineLowerCase = Str::singular($this->tableName);
        $modelName = Str::singular($modelNamePlural);
        $modelNameLowerCase = lcfirst($modelName);
        $pathName = ($this->route) ? ucfirst($this->route) : '';
        $namespacePath = ($pathName) ? '\\' . $pathName : '';
        $customPath = ($pathName) ? '/' . $pathName : '';
        $modelsNamespace = config('jsonapigenerator.modelsNamespace');

        $this->templateVars = [
            'modelName' => $modelName,
            'modelNameSingularUnderlineLowerCase' => $modelNameSingularUnderlineLowerCase,
            'modelNamePlural' => $modelNamePlural,
            'modelsNamespace' => $modelsNamespace,
            'modelNameLowerCase' => $modelNameLowerCase,
            'modelNamePluralLowerCase' => $this->tableName,
            'pathName' => $pathName,
            'namespacePath' => $namespacePath,
            'customPath' => $customPath
        ];

        $this->info('Json Api Generator');
        $this->info('Creating model...');
        Artisan::call('make:model', ['name' => $modelName]);
        $this->info('Creating service...');
        $this->service();
        $this->info('Creating repository...');
        $this->repository();
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
        $this->info('Creating factory...');
        $this->factory();
        $this->info('Creating seed...');
        $this->seed();
        $this->info('Done');
    }

    /**
     * Create the service
     * @param string $name
     */
    protected function service(): void
    {
        $name = $this->templateVars['modelName'];
        if (!file_exists($path = app_path('/Services'))) {
            mkdir($path, 0755, true);
        }

        $template = $this->makeTemplate('Services/Service');
        $this->writeToFile(
            app_path("Services/{$name}Service.php"),
            $template
        );
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

        foreach ($this->templateVars as $key => $value) {
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
     * Read the template
     * @param string $type
     * @return string
     */
    protected function getStub(string $type): string
    {
        return File::get(__DIR__ . "/../../../resources/stubs/$type.stub");
    }

    /**
     * Write to file
     * @param string $path
     * @param string $content
     * @param bool $overwrite
     */
    protected function writeToFile(string $path, string $content, bool $overwrite = false): void
    {
        if ($this->force == 1 || !file_exists($path) || $overwrite) {
            File::put($path, $content);
        }
    }

    /**
     * Create the repository
     * @param string $name
     */
    protected function repository(): void
    {
        $name = $this->templateVars['modelName'];
        if (!file_exists($path = app_path('/Interfaces'))) {
            mkdir($path, 0755, true);
        }

        if (!file_exists($path = app_path('/Interfaces/Repositories'))) {
            mkdir($path, 0755, true);
        }

        if (!file_exists($path = app_path('/Repositories'))) {
            mkdir($path, 0755, true);
        }

        $template = $this->makeTemplate('Interfaces/RepositoryInterface');
        $this->writeToFile(
            app_path("Interfaces/Repositories/{$name}RepositoryInterface.php"),
            $template
        );

        $template = $this->makeTemplate('Repositories/Repository');
        $this->writeToFile(
            app_path("Repositories/{$name}Repository.php"),
            $template
        );
    }

    /**
     * Create the controller
     */
    protected function controller(): void
    {
        $customPath = $this->templateVars['customPath'];
        $singularName = $this->templateVars['modelName'];
        $path = app_path("Http/Controllers/Api{$customPath}/");

        $controllerTemplate = $this->makeTemplate('Http/Controllers/Controller.php');

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        $this->writeToFile("$path{$singularName}Controller.php", $controllerTemplate);
    }

    /**
     * Generate resources
     */
    protected function requests(): void
    {
        $modelName = $this->templateVars['modelName'];
        $customPath = $this->templateVars['customPath'];

        $requests = [
            'IndexRequest',
            'StoreRequest',
            'UpdateRequest'
        ];

        $path = app_path("Http/Requests/Api{$customPath}/{$modelName}/");

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        foreach ($requests as $request) {
            $rules = '[' . PHP_EOL;
            $rules .= PHP_MARGIN . PHP_MARGIN . PHP_MARGIN . '\'data\' => \'required|array\',' . PHP_EOL;
            $rules .= PHP_MARGIN . PHP_MARGIN . PHP_MARGIN . '\'data.type\' => [\'required\', Rule::in([\'' . $this->tableName . '\'])],' . PHP_EOL;
            if ($request !== 'StoreRequest') {
                $rules .= PHP_MARGIN . PHP_MARGIN . PHP_MARGIN . '\'data.id\' => \'integer\',' . PHP_EOL;
            }
            $rules .= PHP_MARGIN . PHP_MARGIN . PHP_MARGIN . '\'data.attributes\' => \'array\',' . PHP_EOL;

            foreach ($this->getColumns() as $column) {
                if (isset($column['rules'][$request])) {
                    $rules .= PHP_MARGIN . PHP_MARGIN . PHP_MARGIN . '\'data.attributes.' . $column['name'] . '\' => \'' . $column['rules'][$request] . '\',' . PHP_EOL;
                }
            }
            $rules .= '        ]';
            $this->templateVars['rules'] = $rules;

            $template = $this->makeTemplate("Http/Requests/{$request}.php");
            $this->writeToFile($path . $request . ".php", $template);
        }
    }

    /**
     * Get columns list from db
     *
     * @param string $tableName
     * @return Collection
     * @throws Exception
     */
    protected function getColumns(string $tableName = ""): Collection
    {
        if (!$this->columns) {
            $excludedColumns = ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'];
            if (!$tableName) {
                $tableName = $this->tableName;
            }
            $indexes = collect(Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes($tableName));
            $foreign = Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys($tableName);
            $columns = Schema::getColumnListing($tableName);
            $result = [];
            foreach ($columns as $column) {
                $unique = false;
                foreach ($indexes as $index) {
                    if (in_array($column, $index->getColumns()) && ($index->isUnique() && !$index->isPrimary())) {
                        $unique = true;
                    }
                }
                $forKey = '';
                foreach ($foreign as $fkey) {
                    if (in_array($column, $fkey->getLocalColumns())) {
                        $forKey = $fkey->getForeignTableName() . '.' . $fkey->getForeignColumns()[0];
                    }
                }
                $result[$column] = [
                    'name' => $column,
                    'type' => Schema::getColumnType($tableName, $column),
                    'required' => boolval(
                        Schema::getConnection()->getDoctrineColumn($tableName, $column)->getNotnull()
                    ),
                    'unique' => $unique,
                    'foreign' => $forKey,
                    'belongsTo' => null,
                    'rules' => null
                ];

                if (stristr($result[$column]['name'], "_id")) {
                    $result[$column]['belongsTo']['name'] = Str::camel(str_replace("_id", "", $column));
                    $result[$column]['belongsTo']['model'] = ucfirst(Str::camel($result[$column]['belongsTo']['name']));
                }

                if (!in_array($column, $excludedColumns)) {
                    $result[$column]['rules'] = [
                        'StoreRequest' => $this->generateStoreRules($result[$column]),
                        'UpdateRequest' => $this->generateUpdateRules($result[$column])
                    ];
                }
            }
            $this->columns = collect($result);
        }

        return $this->columns;
    }

    /**
     * Generate rules Store action
     *
     * @param $column array
     * @return string
     */
    protected function generateStoreRules(array $column): string
    {
        $result = [];
        if ($column['required']) {
            $result[] = 'required';
        } else {
            $result[] = 'nullable';
        }

        if ($column['name'] === 'email') {
            $result[] = 'email';
        } elseif ($column['name'] === 'password') {
            $result[] = 'min:7|confirmed|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9]).*$/';
        }

        if ($column['unique'] || $column['name'] == 'slug') {
            $result[] = 'unique:' . $this->tableName . ',' . $column['name'];
        }
        $result = $this->getTypeSpecificRules($column, $result);

        return implode('|', $result);
    }

    /**
     * Add column type specific rules
     *
     * @param array $column
     * @param array $rules
     * @return array
     */
    protected function getTypeSpecificRules(array $column, array $rules): array
    {
        $integerTypes = [
            'integer',
            'tinyint',
            'smallint',
            'mediumint',
            'bigint',
            'unsignedInteger',
            'unsignedTinyInteger',
            'unsignedSmallInteger',
            'unsignedMediumInteger',
            'unsignedBigInteger'
        ];
        $numericTypes = [
            'float',
            'decimal',
        ];
        $datetimeTypes = [
            'datetime',
            'date',
            'timestamp'
        ];
        $stringTypes = [
            'string',
            'varchar',
            'text'
        ];
        if (in_array($column['type'], $integerTypes)) {
            $rules[] = 'integer';
        } elseif (in_array($column['type'], $numericTypes)) {
            $rules[] = 'numeric';
        } elseif (in_array($column['type'], $datetimeTypes)) {
            $rules[] = 'date_format:Y-m-d H:i:s';
        } elseif (in_array($column['type'], $stringTypes)) {
            $rules[] = 'string';
        } elseif ($column['type'] === 'time') {
            $rules[] = 'date_format:H:i:s';
        } elseif ($column['type'] === 'boolean') {
            $rules[] = 'boolean';
        } else {
            $rules[] = 'string';
        }

        return $rules;
    }

    /**
     *  Generate rules Update action
     *
     * @param $column array
     * @return string
     */
    protected function generateUpdateRules(array $column): string
    {
        $result = [];
        if ($column['required']) {
            $result[] = 'sometimes';
        } else {
            $result[] = 'nullable';
        }
        if ($column['name'] == 'email') {
            $result[] = 'email';
        }
        if ($column['name'] == 'password') {
            $result[] = 'min:7|confirmed|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9]).*$/';
        }
        if ($column['unique'] || $column['name'] == 'slug') {
            $result[] = 'unique:' . $this->tableName . ',' . $column['name'] . ',\' . $this->id .\'';
        }
        $result = $this->getTypeSpecificRules($column, $result);

        return implode('|', $result);
    }

    /**
     * Generate resources
     * @throws Exception
     */
    protected function resources(): void
    {
        $modelName = $this->templateVars['modelName'];
        $modelNamePlural = $this->templateVars['modelNamePlural'];
        $customPath = $this->templateVars['customPath'];

        $resources = [
            'ModelIdentifierResource' => $modelName . 'IdentifierResource',
            'ModelRelationshipResource' => $modelName . 'RelationshipResource',
            'ModelResource' => $modelName . 'Resource',
            'ModelsResource' => $modelNamePlural . 'Resource'
        ];

        $path = app_path("Http/Resources/Api{$customPath}/{$modelName}/");

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $attributes = '';
        foreach ($this->getColumns() as $column) {
            if ($column['name'] !== 'id') {
                $attributes .= PHP_MARGIN . PHP_MARGIN . PHP_MARGIN . '\'' . $column['name'] . '\' => $this->' . $column['name'] . ',' . PHP_EOL;
            }
        }

        $this->templateVars['attributes'] = $attributes;

        foreach ($resources as $key => $resource) {
            $template = $this->makeTemplate("Http/Resources/{$key}.php");
            $this->writeToFile($path . $resource . ".php", $template);
        }
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
            "Route::resource('" . $route . "', \App\Http\Controllers\Api" . $namespacePath . "\\" . $modelName . "Controller::class, ['except'=> ['edit', 'create']]);",
        ];

        foreach ($routes as $route) {
            if (!stristr($routesFile, $route)) {
                File::append($routesPath, $route . PHP_EOL);
            }
        }
    }

    /**
     * Create the controller test
     */
    protected function test(): void
    {
        $customPath = $this->templateVars['customPath'];
        $singularName = $this->templateVars['modelName'];
        $path = base_path("tests/Feature/Api{$customPath}/");
        $this->templateVars['FactoryFields'] = $this->getFactoryFields();

        $testTemplate = $this->makeTemplate('Tests/Feature/Test.php');

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        $this->writeToFile("$path{$singularName}ControllerTest.php", $testTemplate);
    }

    /**
     * Get fields with faker
     *
     * @param string $tableName
     * @return string
     * @throws Exception
     */
    protected function getFactoryFields(string $tableName = ''): string
    {
        $return = '';
        foreach ($this->getColumns($tableName) as $column) {
            if ($column['rules'] && !$column['belongsTo']) {
                $return .= PHP_MARGIN . '\'' . $column['name'] . '\' => ';
                if (!$column['foreign'] && $column['name'] !== 'remember_token') {
                    $return .= '$this->faker->';
                }
                if ($column['unique']) {
                    $return .= 'unique()->';
                }

                if ($column['type'] === 'int') {
                    $return .= 'randomDigit';
                } elseif ($column['type'] === 'datetime') {
                    $return .= 'dateTime';
                } elseif ($column['type'] === 'text') {
                    $return .= 'word';
                } elseif ($column['type'] === 'boolean') {
                    $return .= 'boolean()';
                } elseif ($column['name'] === 'name') {
                    $return .= 'name';
                } elseif ($column['name'] === 'email') {
                    $return .= 'email';
                } elseif ($column['name'] === 'password') {
                    $return .= 'password';
                } elseif ($column['name'] === 'title') {
                    $return .= 'title';
                } elseif ($column['foreign']) {
                    $return .= ' \\App\\Models\\' . $column['belongsTo']['model'] . '::first()->id';
                } elseif ($column['name'] === 'remember_token') {
                    $return .= ' Str::random(10)';
                } else {
                    $return .= (substr($return, -2) === '->' ? 'word' : '');
                }

                $return .= ',' . PHP_EOL . '        ';
            }
        }

        return $return;
    }

    /**
     * Create the factory
     *
     * @param string $modelName
     * @param string $tableName
     * @throws Exception
     */
    protected function factory(string $modelName = '', string $tableName = ''): void
    {
        $model = ($modelName) ?: $this->templateVars['modelName'];
        $path = base_path("database/factories");
        $this->templateVars['FactoryFields'] = $this->getFactoryFields($tableName);
        $factoryTemplate = $this->makeTemplate('Database/Factory.php');
        $this->writeToFile("$path/{$model}Factory.php", $factoryTemplate);
    }

    /**
     * Create the seed
     *
     */
    protected function seed(): void
    {
        $callSeeder = '';

        foreach ($this->getColumns() as $column) {
            if ($column['foreign']) {
                $select = explode(".", $column['foreign']);
                $callSeeder .= '$this->call(' . ucfirst($select[0]) . 'TableSeeder::class);' . PHP_EOL . '        ';
                $this->factory(ucfirst($select[0]), $select[0]);
            }
        };

        $modelNamePlural = $this->templateVars['modelNamePlural'];
        $path = base_path("database/seeders");
        $this->templateVars['callSeeder'] = $callSeeder;
        $factoryTemplate = $this->makeTemplate('Database/Seed.php');
        $this->writeToFile("$path/{$modelNamePlural}TableSeeder.php", $factoryTemplate);
    }
}
