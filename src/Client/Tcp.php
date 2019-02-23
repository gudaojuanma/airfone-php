<?php

namespace Airfone\Client;

use Exception;

class Tcp
{
    const TYPE_AUTHENTICATE = 1;

    const TYPE_SUBSCRIBE = 2;

    const TYPE_PUBLISH = 3;

    const TYPE_UNSUBSCRIBE = 4;

    private $key;

    private $secret;

    private $socket;

    public function __construct($key, $secret, $host, $port)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    }

    public function connect($host, $port)
    {
        return socket_connect($this->socket, $host, $port);
    }

    public function disconnect()
    {
        if ($this->socket) {
            socket_close($this->socket);
        }
    }

    public function authentication()
    {
        return $this->generateAction(self::TYPE_AUTHENTICATE);
    }

    public function authenticate()
    {
        $action = $this->generateAction(self::TYPE_AUTHENTICATE);

        return $this->sendAction($action);
    }

    public function subscribe($channel)
    {
        $data = [
            'channel' => $channel,
        ];

        $action = $this->generateAction(self::TYPE_SUBSCRIBE, $data);

        return $this->sendAction($action);
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

        return $this->sendAction($action);
    }

    public function unsubscribe($channel)
    {
        $data = [
            'channel' => $channel,
        ];

        $action = $this->generateAction(self::TYPE_UNSUBSCRIBE, $data);

        return $this->sendAction($action);
    }

    protected  function sendAction($action)
    {
        $buffer = json_encode($action);

        return socket_send($this->socket, $buffer, strlen($buffer), MSG_EOF);
    }

    protected function generateAction($type, $data = null)
    {
        $action = $data ?: [];
        $action['type'] = $type;
        $action['key'] = $this->key;
        $action['nonce'] = str_random(16);
        $action['timestamp'] = time();

        $payload = $action['key'] . $action['nonce'] . $action['timestamp'] . $this->secret;
        $action['digest'] = hash('sha256', $payload);

        return $action;
    }

    public function __destruct()
    {
        $this->disconnect();

        unset($this->socket);
    }
}