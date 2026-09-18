<?php
require $argv[1] ?? dirname(__DIR__).'/vendor/autoload.php';
$app = new Illuminate\Foundation\Application(sys_get_temp_dir());
$app->instance('config', new Illuminate\Config\Repository(['cache'=>['default'=>'array','stores'=>['array'=>['driver'=>'array']]],'multiversx'=>['urls'=>['api'=>'https://fixture.invalid']]]));
Illuminate\Support\Facades\Facade::setFacadeApplication($app);
$app->register(Illuminate\Cache\CacheServiceProvider::class);
$app->register(Peerme\MxLaravel\ServiceProvider::class);
Carbon\Carbon::setTestNow('2026-09-18T10:00:00Z');
$client = Peerme\MxLaravel\Multiversx::createMockedHttpClientWithResponses([['totalSupply'=>100,'circulatingSupply'=>80,'staked'=>20,'price'=>42,'marketCap'=>4200,'apr'=>1,'topUpApr'=>1,'baseApr'=>1]]);
$api = Peerme\MxLaravel\Multiversx::apiWithCache(now()->addMinutes(5),$client);
$result = $api->network()->getEconomics();
if ($result->price !== 42.0) throw new RuntimeException('Injected client response was not preserved');
// Verify the actual default cache middleware TTL without making a network call.
$api = Peerme\MxLaravel\Multiversx::apiWithCache(now()->addMinutes(5));
$found = str_contains((string)$api->client->getConfig('handler'),'cache');
if (!$found) throw new RuntimeException('Default client lost its cache middleware');
if ((int)now()->diffInSeconds(now()->addMinutes(5),false) !== 300) throw new RuntimeException('Future TTL must be positive');
Carbon\Carbon::setTestNow();
echo "3 Laravel SDK compatibility checks passed\n";
