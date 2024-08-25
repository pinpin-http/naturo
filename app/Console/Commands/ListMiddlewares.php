<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Router;

class ListMiddlewares extends Command
{
    protected $signature = 'middleware:list';
    protected $description = 'Liste les middlewares disponibles dans l\'application';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(Router $router)
    {
        $middlewares = $router->getMiddleware();
        $routeMiddlewares = $router->getMiddlewareGroups();

        $this->info("Global Middlewares:");
        foreach ($middlewares as $name => $class) {
            $this->line("$name => $class");
        }

        $this->info("\nRoute Middlewares:");
        foreach ($routeMiddlewares as $group => $middlewares) {
            $this->line("$group => " . implode(', ', $middlewares));
        }
    }
}
