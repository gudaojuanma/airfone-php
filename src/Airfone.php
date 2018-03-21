<?php

namespace Airfone;

use Exception;

class Airfone
{
    const TYPE_AUTHENTICATE = 1;

    const TYPE_SUBSCRIBE = 2;

    const TYPE_UNSUBSCRIBE = 3;

    const TYPE_PUBLISH = 4;

    const TYPE_BROADCAST = 5;

    private $identify;

    private $secret;

    private $socket;

    private $ws;

    public function __construct($identify, $secret, $ws, $host, $port)
    {
        $this->identify = $identify;
        $this->secret = $secret;
        $this->ws = $ws;

        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($this->socket, $host, $port);
    }

    public function authentication()
    {
        return $this->generateAction(self::TYPE_AUTHENTICATE);
    }

    public function publish($channel, $message)
    {
        if (! isset($message['type'])) {
            throw new Exception('key [type] is required');
        }

        $data = [
            'channel' => $channel,
            'message' => json_encode($message),
        ];

        $action = $this->generateAction(self::TYPE_PUBLISH, $data);

        $buffer = json_encode($action);

        return socket_send($this->socket, $buffer, strlen($buffer), MSG_EOF);
    }

    public function broadcast($message)
    {
        if (! isset($message['type'])) {
            throw new Exception('key [type] is required');
        }

        $data = [
            'message' => json_encode($message),
        ];

        $action = $this->generateAction(self::TYPE_BROADCAST, $data);

        $buffer = json_encode($action);

        return socket_send($this->socket, $buffer, strlen($buffer), MSG_EOF);
    }

    protected function generateAction($type, $data = null)
    {
        $action = is_null($data) ? [] : $data;
        $action['type'] = $type;
        $action['identify'] = $this->identify;
        $action['nonce'] = str_random(16);
        $action['timestamp'] = time();
        $action['digest'] = self::generateDigest($action, $this->secret);
        return $action;
    }

    protected static function generateDigest($data, $secret)
    {
        $keys = array_keys($data);
        asort($keys);
        $segments = [];
        foreach ($keys as $key) {
            $segments[] = sprintf('%s=%s', $key, $data[$key]);
        }
        $segments[] = $secret;
        return hash('sha256', implode('&', $segments));
    }

    public function __destruct()
    {
        if ($this->socket) {
            socket_close($this->socket);
        }
    }
}