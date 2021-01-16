<?php

namespace admica\transFiles;

use Illuminate\Support\ServiceProvider;
use admica\transFiles\Commands\TranslateFilesCommand;

class transFilesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands([
            TranslateFilesCommand::class,
        ]);
    }
}
