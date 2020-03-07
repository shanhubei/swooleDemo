<?php 
namespace Shanhubei\Swoole;

use swoole_websocket_server;

class Uploader
{
    protected $ws;
    protected $host = '0.0.0.0';
    protected $port = 9505;
    // 进程名称
    protected $taskName = 'swooleUploader';
    // PID路径
    protected $pidFile = '/run/swooleUploader.pid';
    // 设置运行时参数
    protected $options = [
        'worker_num' => 4, //worker进程数,一般设置为CPU数的1-4倍  
        'daemonize' => true, //启用守护进程
        'log_file' => '/data/logs/uploadswoole.log', //指定swoole错误日志文件
        'log_level' => 3, //日志级别 范围是0-5，0-DEBUG，1-TRACE，2-INFO，3-NOTICE，4-WARNING，5-ERROR
        'dispatch_mode' => 1, //数据包分发策略,1-轮询模式
    ];
 

    public function __construct($options = [])
    {
        $this->ws = new swoole_websocket_server($this->host, $this->port);

        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        $this->ws->set($this->options);

        $this->ws->on("open", [$this, 'onOpen']);
        $this->ws->on("message", [$this, 'onMessage']);
        $this->ws->on("close", [$this, 'onClose']);
    }

    public function start()
    {
        // Run worker
        $this->ws->start();
    }

    public function onOpen(swoole_websocket_server $ws, $request)
    {
        // 设置进程名
        cli_set_process_title($this->taskName);
        //记录进程id,脚本实现自动重启
        $pid = "{$ws->master_pid}\n{$ws->manager_pid}";
        file_put_contents($this->pidFile, $pid);

        echo "server: handshake success with fd{$request->fd}\n";
        $msg = '{"msg": "connect ok"}';
        $ws->push($request->fd, $msg);
    }

    public function onMessage(swoole_websocket_server $ws, $frame)
    {
        $opcode = $frame->opcode;
        if ($opcode == 0x08) {
            echo "Close frame received: Code {$frame->code} Reason {$frame->reason}\n";
        } else if ($opcode == 0x1) {
            echo "Text string\n";
        } else if ($opcode == 0x2) {
            echo "Binary data\n"; //
        } else {
            echo "Message received: {$frame->data}\n";
        }
        $filename = './files/aaa.jpg';
        file_put_contents($filename, $frame->data);
        echo "file path : {$filename}\n";
        $ws->push($frame->fd, 'upload success');
    }
    public function onClose($ws, $fid)
    {
        echo "client {$fid} closed\n";
        foreach ($ws->connections as $fd) {
            $ws->push($fd, $fid. '已断开！');
        }
    }
}