<?php

namespace YunhanDev\Tests;

abstract class TestCaseBase extends \Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        // 清理注入的 testing 环境变量，使用当前 env 内的环境变量
        putenv('APP_ENV');
        return require __DIR__ . '/../../../../../bootstrap/app.php';
    }
}
