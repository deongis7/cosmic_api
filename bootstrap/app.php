<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();
//date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));
date_default_timezone_set('Asia/Jakarta');


/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades();

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Config Files
|--------------------------------------------------------------------------
|
| Now we will register the "app" configuration file. If the file exists in
| your configuration directory it will be loaded; otherwise, we'll load
| the default version. You may register other files below as needed.
|
*/

$app->configure('app');
//untuk auth
$app->configure('auth');
//$app->configure('database');
$app->configure('redis');
$app->configure('swagger-lume');


/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

// $app->middleware([
//     App\Http\Middleware\ExampleMiddleware::class
// ]);

$app->routeMiddleware([
     'auth' => App\Http\Middleware\Authenticate::class,
	 'client' => \Laravel\Passport\Http\Middleware\CheckClientCredentials::class,
 ]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);
$app->register(Pearl\RequestValidate\RequestServiceProvider::class);
$app->register(Maatwebsite\Excel\ExcelServiceProvider::class);
$app->register(Laravel\Passport\PassportServiceProvider::class);
$app->register(Dusterio\LumenPassport\PassportServiceProvider::class);
$app->register(Intervention\Image\ImageServiceProvider::class);
$app->register(Illuminate\Redis\RedisServiceProvider::class);
$app->register(\SwaggerLume\ServiceProvider::class);

Dusterio\LumenPassport\LumenPassport::routes($app->router, ['prefix' => 'api/v1/oauth'] );
//Dusterio\LumenPassport\LumenPassport::routes($app->router, ['prefix' => 'apix/v1/oauth'] );
/*
|--------------------------------------------------------------------------
| Register Alias
|--------------------------------------------------------------------------
|
*/
// I register the db alias here because the Application::registerContainerAliases()
// doesn't register it by default like laravel does, it's probable because the db is not always
// register by default.

$app->alias('Excel', Maatwebsite\Excel\Facades\Excel::class);
$app->alias('Image', Intervention\Image\Facades\Image::class);

if (!class_exists('Redis')) {
    class_alias('Illuminate\Support\Facades\Redis', 'Redis');
}
/*
/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

//\Dusterio\LumenPassport\LumenPassport::routes($app, ['prefix' => 'v1/oauth']);
//\Dusterio\LumenPassport\LumenPassport::routes($this->app, ['prefix' => 'v1/oauth']);

return $app;
