<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['config']->set('logging.default', 'null');

        $testViewsDir = sys_get_temp_dir() . '/cargopharma_test_views';
        if (!is_dir($testViewsDir)) {
            @mkdir($testViewsDir, 0777, true);
        }
        $app['config']->set('view.compiled', $testViewsDir);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $testViewsDir = sys_get_temp_dir() . '/cargopharma_test_views';
        if (!is_dir($testViewsDir)) {
            @mkdir($testViewsDir, 0777, true);
        }

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'logging.default' => 'null',
            'view.compiled' => $testViewsDir,
        ]);
    }
}
