<?php
namespace app\Core;

use Predis\Client;

class Redis
{
    public function initConnection(): Client
    {
        return new Client([
            'scheme' => $_ENV['REDIS_SCHEME'],
            'host' => $_ENV['REDIS_HOST'],
            'port' => $_ENV['REDIS_PORT'],
        ]);
    }
}