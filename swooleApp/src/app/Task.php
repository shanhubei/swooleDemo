<?php 
namespace Shanhubei\Swoole;

use swoole_server;

/**
* 任务调度
*/
class Task
{
    protected $serv;
    protected $host = '127.0.0.1';
    protected $port = 9506;
    // 进程名称
    protected $taskName = 'swooleTask';
    // PID路径
    protected $pidPath = '/run/swooletask.pid';
    // 设置运行时参数
    protected $options = [
        'worker_num' => 4, //worker进程数,一般设置为CPU数的1-4倍  
        'daemonize' => true, //启用守护进程
        'log_file' => '/data/log/swoole-task.log', //指定swoole错误日志文件
        'log_level' => 0, //日志级别 范围是0-5，0-DEBUG，1-TRACE，2-INFO，3-NOTICE，4-WARNING，5-ERROR
        'dispatch_mode' => 1, //数据包分发策略,1-轮询模式
        'task_worker_num' => 4, //task进程的数量
        'task_ipc_mode' => 3, //使用消息队列通信，并设置为争抢模式
    ];

    public function __construct($options = [])
    {
        date_default_timezone_set('PRC'); 
        // 构建Server对象，监听127.0.0.1:9506端口
        $this->serv = new swoole_server($this->host, $this->port);

        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        $this->serv->set($this->options);

        // 注册事件
        $this->serv->on('Start', [$this, 'onStart']);
        $this->serv->on('Connect', [$this, 'onConnect']);
        $this->serv->on('Receive', [$this, 'onReceive']);
        $this->serv->on('Task', [$this, 'onTask']);  
        $this->serv->on('Finish', [$this, 'onFinish']);
        $this->serv->on('Close', [$this, 'onClose']);
    }

    public function start()
    {
        // Run worker
        $this->serv->start();
    }

    public function onStart($serv)
    {
        // 设置进程名
        cli_set_process_title($this->taskName);
        //记录进程id,脚本实现自动重启
        $pid = "{$serv->master_pid}\n{$serv->manager_pid}";
        file_put_contents($this->pidPath, $pid);
    }

    //监听连接进入事件
    public function onConnect($serv, $fd, $from_id)
    {
        $serv->send( $fd, "Hello {$fd}!" );
    }

    // 监听数据接收事件
    public function onReceive(swoole_server $serv, $fd, $from_id, $data)
    {
        echo "Get Message From Client {$fd}:{$data}\n";
        //$this->writeLog('接收客户端参数：'.$fd .'-'.$data);
        $res['result'] = 'success';
        $serv->send($fd, json_encode($res)); // 同步返回消息给客户端
        $serv->task($data);  // 执行异步任务
    }

    /**
    * @param $serv swoole_server swoole_server对象
    * @param $task_id int 任务id
    * @param $from_id int 投递任务的worker_id
    * @param $data string 投递的数据
    */
    public function onTask(swoole_server $serv, $task_id, $from_id, $data)
    {
        swoole_timer_tick(30000, function($timer) use ($task_id) { // 启用定时器，每30秒执行一次
            $memPercent = $this->getMemoryUsage();
            echo date('Y-m-d H:i:s') . '当前内存使用率：'.$memPercent."\n";
        });
    }


    /**
    * @param $serv swoole_server swoole_server对象
    * @param $task_id int 任务id
    * @param $data string 任务返回的数据
    */
    public function onFinish(swoole_server $serv, $task_id, $data)
    {
        //
    }


    // 监听连接关闭事件
    public function onClose($serv, $fd, $from_id) {
        echo "Client {$fd} close connection\n";
    }

    public function stop()
    {
        $this->serv->stop();
    }

    private function getMemoryUsage()
    {
        // MEMORY
        if (false === ($str = @file("/proc/meminfo"))) return false;
        $str = implode("", $str);
        preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);
        //preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buffers);

        $memTotal = round($buf[1][0]/1024, 2);
        $memFree = round($buf[2][0]/1024, 2);
        $memUsed = $memTotal - $memFree;
        $memPercent = (floatval($memTotal)!=0) ? round($memUsed/$memTotal*100,2):0;

        return $memPercent;
    }
}