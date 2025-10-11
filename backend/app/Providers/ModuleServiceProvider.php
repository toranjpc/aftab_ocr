<?php

namespace App\Providers;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

abstract class ModuleServiceProvider extends ServiceProvider
{
    abstract public function getNamespace();

    abstract public function getDir();

    function getModuleName()
    {
        return basename($this->getDir());
    }

    function loadMigrarions($dir)
    {
        $migrationsFolder = $dir . '/database/migrations';

        if (!file_exists($migrationsFolder))
            return;

        $migrationsSubFolders = array_filter(glob($migrationsFolder . '/*'), 'is_dir');

        foreach ($migrationsSubFolders as $folder) {
            $this->loadMigrationsFrom($folder);
        }

        $this->loadMigrationsFrom($migrationsFolder);
    }

    function register()
    {
        $dir = $this->getDir();

        $this->loadMigrarions($dir);

        $this->loadModels();

        if (file_exists($dir . '/routes.php'))
            Route::middleware(['api'])
                // ->namespace($this->getNamespace())
                ->prefix('api')
                ->group($dir . '/routes.php');
    }

    public function loadModels()
    {
        $mapModels = $this->getModelsWithNamespace($this->getModuleName());

        foreach ($mapModels as $key => $model) {
            $this->app->bind($key, $model);
        }
    }

    public static function getModelsWithNamespace(string $module): array
    {
        $modelsPath = base_path('modules/' . $module . '/Models');

        if (!file_exists($modelsPath))
            return [];

        $modelFiles = \File::files($modelsPath);

        $mapModels = [];

        foreach ($modelFiles as $file) {
            $filename = $file->getFilename();
            $className = substr($filename, 0, -4);
            $namespace = 'Modules\\' . $module . '\\Models\\' . $className;

            if (class_exists($namespace)) {
                $mapModels['Model.' . $className] = $namespace;
            }
        }

        return $mapModels;
    }


    protected function loadSeeders($seedList)
    {
        $this->callAfterResolving(DatabaseSeeder::class, function ($seeder) use ($seedList) {
            foreach ((array) $seedList as $path) {
                $seeder->call($seedList);
                // here goes the code that will print out in console that the migration was succesful
            }
        });
    }
}
