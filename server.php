<?php

use Workerman\Worker;

require_once './vendor/workerman/workerman/Autoloader.php';

(new PptSocketServer())->run();

class PptSocketServer
{
    const CONNECTION_TYPE_PPT = 'connection_ppt';
    const CONNECTION_TYPE_CONTROLLER = 'connection_controller';

    protected $worker;
    protected $globalUid = 0;
    protected $globalPptConnection;
    protected $globalControllerConnection;

    public function __construct(int $port = 2346)
    {
        $this->initWorker($port);
    }

    public function run()
    {
        Worker::runAll();
    }

    protected function initWorker(int $port)
    {
        $this->worker            = new Worker("websocket://0.0.0.0:" . $port);
        $this->worker->count     = 1;
        $this->worker->onConnect = [$this, 'handleConnection'];
        $this->worker->onMessage = [$this, 'handleMessage'];
        $this->worker->onClose   = [$this, 'handleClose'];

    }

    public function handleConnection($connection)
    {
        $connection->uid = ++$this->globalUid;
    }

    public function handleMessage($connection, $data)
    {

        if ($this->setPptConnectionIfNull($connection, $data)) {
            Log::info('ppt online');
            return;
        }

        if ($this->setControllerConnectionIfNull($connection, $data)) {
            Log::info('controller online');
            return;
        }

        if (is_null($this->globalPptConnection)) {
            Log::info('ppt offline; cant control');
            return;
        }

        // 目前只允许一个控制器发送指令。
        if (!is_null($this->globalControllerConnection)
            && $connection->uid !== $this->globalControllerConnection->uid
        ) {
            Log::info('sorry, you are not correct controller ' . $connection->uid);
            return;
        }

        $this->globalPptConnection->send($data);
    }

    public function handleClose($connection)
    {
        $this->destructConnection($connection);

        Log::info($connection->uid . ' offline by close websocket');
    }

    protected function destructConnection($connection)
    {
        if (isset($connection->type) && $connection->type === self::CONNECTION_TYPE_PPT) {
            $this->globalPptConnection = null;
            Log::info('ppt offline');
            return true;
        }

        if (isset($connection->type) && $connection->type === self::CONNECTION_TYPE_CONTROLLER) {
            $this->globalControllerConnection = null;
            Log::info('controller offline');
            return true;
        }

        return true;
    }

    /**
     * 根据命令判断和设置连接类型
     *
     * @param $connection
     * @param $data
     * @return bool
     */
    protected function setPptConnectionIfNull($connection, $data)
    {
        if (!is_null($this->globalPptConnection)) return false;
        if (!$this->requestConnectionIsPpt($data)) return false;

        $connection->type          = self::CONNECTION_TYPE_PPT;
        $this->globalPptConnection = $connection;
        return true;
    }

    /**
     * 根据命令判断和设置连接类型
     *
     * @param $connection
     * @param $data
     * @return bool
     */
    protected function setControllerConnectionIfNull($connection, $data)
    {
        if (!is_null($this->globalControllerConnection)) return false;
        if (!$this->requestConnectionIsController($data)) return false;

        $connection->type                 = self::CONNECTION_TYPE_CONTROLLER;
        $this->globalControllerConnection = $connection;
        return true;
    }

    public function requestConnectionIsPpt($data)
    {
        return $data === 'i am ppt';
    }

    public function requestConnectionIsController($data)
    {
        return $data === 'i am controller';
    }

}

class Log
{
    public static function info(...$logString)
    {
        $infos = func_get_args();
        foreach ($infos as $key=> $info) {
            file_put_contents(
                "./debug.log",
                "[ " . date('Y-m-d H:i:s') . " ] " . var_export($info, true) . PHP_EOL,
                FILE_APPEND
            );
        }
    }
}