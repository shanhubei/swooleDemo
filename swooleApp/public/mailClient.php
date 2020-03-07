<?php 
class Client
{
    private $client;
    
    public function __construct() {
        $this->client = new swoole_client(SWOOLE_SOCK_TCP);
    }

    public function connect($type) {
        if( !$this->client->connect("127.0.0.1", 9502 , 1) ) {
            echo "Error: {$this->client->errMsg}[{$this->client->errCode}]\n";
        }
        // fwrite(STDOUT, "请输入消息 Please input msg：");
        // $msg = trim(fgets(STDIN));
        $action = 'sendMail';
		if($type == 'q'){
			$action = 'sendMailQueue';
		}
        $time = time();
        $key = 'MDDGnQE33ytd2jDFADS39DSEWsdD24sK';
        $token = md5($action.$time.$key);
        $data = [
            'action' => $action,
            'token' => $token,
            'timestamp' => $time
        ];
        $msg = json_encode($data);

        $this->client->send( $msg );
        $message = $this->client->recv();
        echo "Get Message From Server:{$message}\n";
    }
}

$type = 'q';  //区分批量和单个发送标志

if($type == 'q'){
	$redis = new \Redis();
	$redis->connect('127.0.0.1', 6379);

	//$password = '123456x';
	///$redis->auth($password);

	$arr = [];

	$arr[0] = [
		'subject' => '注册cloudfog-HA',
		'emailAddress' => '337899329@qq.com',
		'body' => '您好，您的CloudFog使用的用户名是：123, 密码是：123。<br/>请不要将此邮件泄漏给他人，并尽快登录CloudFog更换新密码。如有疑问请联系管理员。'
	];
	$arr[1] = [
		'subject' => '注册cloudfog2',
		'emailAddress' => '2823175272@qq.com',
		'body' => '<a href="https://www.shanhubei.com" target="_blank">网易邮箱</a>'
	];
		  
	foreach ($arr as $k=>$v) {
		$redis->rpush("mailerlist", json_encode($v, JSON_UNESCAPED_UNICODE));
	}	
}

$client = new Client();
$client->connect($type);
