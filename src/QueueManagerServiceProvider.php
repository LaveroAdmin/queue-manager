<?php

namespace LaveroAdmin\Admin\QueueManager;

use Illuminate\Support\ServiceProvider;

class QueueManagerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'lavero-admin-queue-manager');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'lavero-admin-queue-manager');

        QueueManager::boot();
    }
}
