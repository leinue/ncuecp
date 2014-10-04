<?php

require('config.php');

/**
* abstract class EmailVerification
* 用于注册/忘记密码/改变邮箱时的邮箱验证
* 使用组合模式设计
*/
abstract class EmailVerification{
	protected $email;

	function __construct($email){$this->email=$email;}

	function getEmail(){return $this->email;}

	abstract function send();
}

class emailContentMgr{
	
	function __construct(){

	}
}

class regEmailVer extends EmailVerification{
	function send(){
		$to = $this->email;
		$subject = "感谢注册NCUECP";
		$reg_url="method=reg";
		$message = "<a href=\"".$reg_url."\">欢迎注册NCUECP,请点击这里进行激活帐号</a>";
		$from = "someonelse@example.com";
		$headers = "From: $from";
		mail($to,$subject,$message,$headers);
	}
}

class forgotPasswordEmailVer extends EmailVerification{
	function send(){

	}
}

class changeEmailVer extends EmailVerification{
	function send(){

	}
}

?>