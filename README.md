# Airfone SDK

```php
use Airfone\Airfone;

$identify = '1234567890123456';

$secret = '12345678901234567890123456789012';

$ws = 'ws://airfone.online';

$host = 'airfone.online';

$port = 9331;

$airfone = new Airfone($identify, $secret, $ws, $host, $port);

$message = [
    'type' => 'server:monitor:tick',
    'cpu' => 1500,
    'cpus' => [1000, 1200, 1800, 2000]
];

$airfone->publish('channel', $message);

$airfone->broadcast($message);
```

## installation

```sh
$ composer require gudaojuanma/airfone-php
```
