<?php

require('config.php');

/**
* abstract class EmailVerification
* 用于注册/忘记密码/改变邮箱时的邮箱验证
* 使用组合模式设计
* **************************************************************************************
* 注册时的消息示例
* $to = $this->email;
* $subject = "感谢注册NCUECP";
* $regUrl="method=reg";
* $message = "<a href=\"".$reg_url."\">欢迎注册NCUECP,请点击这里进行激活帐号</a>";
* $from = "someonelse@example.com";
* $headers = "From: $from";
*/

class EmailVerification{
	protected $email;

	function __construct($email){$this->email=$email;}

	function getEmail(){return $this->email;}

	function send(emailContentMgr $ecm){mail($ecm->getTo(),$ecm->getSubject(),$ecm->getMessage(),$ecm->getHeaders());}
}

class emailContentMgr{
	protected $subject;
	protected $to;
	protected $from;
	protected $message;
	protected $headers;

	function __construct(
		$subject,$to,$url,$from,$message,$headers){
		$this->subject=$subject;
		$this->to=$to;
		$this->from=$from;
		$this->message=$message;
		$this->headers=$headers;
	}

	function getSubject(){return $this->subject;}

	function getTo(){return $this->to;}

	function getFrom(){return $this->from;}

	function getMessage(){return $this->message;}

	function getHeaders(){return $this->headers;}
}

?>