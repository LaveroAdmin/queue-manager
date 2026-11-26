<?php

namespace LaveroAdmin\Admin\QueueManager;

use LaveroAdmin\Admin\Admin;
use LaveroAdmin\Admin\Extension;

class QueueManager extends Extension
{
    /**
     * Bootstrap this package.
     *
     * @return void
     */
    public static function boot()
    {
        static::registerRoutes();

        Admin::extend('queue-manager', __CLASS__);
    }

    /**
     * Register routes for LaveroAdmin.
     *
     * @return void
     */
    protected static function registerRoutes()
    {
        parent::routes(function ($router) {
            /* @var \Illuminate\Routing\Router $router */
            $router->get('queue-manager', 'LaveroAdmin\Admin\QueueManager\QueueController@index')->name('queue-manager-index');
            $router->get('queue-manager/job-detail', 'LaveroAdmin\Admin\QueueManager\QueueController@detail')->name('queue-manager-detail');
            $router->post('queue-manager/retry', 'LaveroAdmin\Admin\QueueManager\QueueController@retry')->name('queue-manager-retry');
            $router->post('queue-manager/retry-selected', 'LaveroAdmin\Admin\QueueManager\QueueController@retrySelected')->name('queue-manager-retry-selected');
            $router->post('queue-manager/retry-all', 'LaveroAdmin\Admin\QueueManager\QueueController@retryAll')->name('queue-manager-retry-all');
            $router->delete('queue-manager/forget', 'LaveroAdmin\Admin\QueueManager\QueueController@forget')->name('queue-manager-forget');
            $router->post('queue-manager/forget-selected', 'LaveroAdmin\Admin\QueueManager\QueueController@forgetSelected')->name('queue-manager-forget-selected');
            $router->post('queue-manager/flush', 'LaveroAdmin\Admin\QueueManager\QueueController@flush')->name('queue-manager-flush');
            $router->post('queue-manager/restart', 'LaveroAdmin\Admin\QueueManager\QueueController@restart')->name('queue-manager-restart');
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function import()
    {
        parent::createMenu('Queue Manager', 'queue-manager', 'icon-tasks');

        parent::createPermission('Queue Manager', 'ext.queue-manager', 'queue-manager*');
    }
}
