<?php

require('config.php');

/**
* class pdoOperation
* 用于封装查询语句时的必须查询过程
* @submitQuery()使用pdo的预处理语句进行查询,比较安全
* @fetchClassQuery()返回对应数据库的class
* @fetchOdd()返回单个值
*
*/
class pdoOperation{

	public $emailCheck="UPDATE `profile` SET `isValid`=`1` WHERE `uid`=?";

	protected static $pdo;
	
	function __construct($pdo){$this->pdo=$pdo;}

	function submitQuery($sql,$arr){
		if(!(is_array($arr))){
			return false;
		}
		$stmt=$this->pdo->prepare($sql);
		$result=$stmt->execute($arr);
		return $result;
	}

	function fetchClassQuery($sql,$arr,$className){
		if(!(is_array($arr))){
			return false;
		}
		$stmt=$this->pdo->prepare($sql);
		$res=$stmt->execute($arr);

		$stmt->setFetchMode(PDO::FETCH_CLASS,$className);

		if ($res) {
			if($draft=$stmt->fetchAll()) {
				return $draft;
			}else{
				return false;}
		}else{
			return false;}
	}

	function fetchOdd($sql,$arr){
		if(!(is_array($arr))){
			return false;
		}
		$stmt=$this->pdo->prepare($sql);

		if($stmt){
			$stmt->execute($arr);
			$row=$stmt->fetch();
			if($row){
				return $row[0];
			}else{return false;}
		}else{return false;}
	}
}

/**
* class EmailVerification
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

	function __construct(){}

	function send(emailContentMgr $ecm){
		mail($ecm->getTo(),$ecm->getSubject(),$ecm->getMessage(),$ecm->getHeaders());}

	function verify(pdoOperation $pdoo,$uid){
		return $pdoo->submitQuery($pdoo->emailCheck,array($uid));
	}
}

class emailContentMgr{
	protected $subject;
	protected $to;
	protected $message;
	protected $headers;

	function __construct(
		$subject,$url,$to,$message,$headers){
		$this->subject=$subject;
		$this->message=$message;
		$this->headers=$headers;
		$this->to=$to;
	}

	function getSubject(){return $this->subject;}

	function getFrom(){return $this->from;}

	function getMessage(){return $this->message;}

	function getHeaders(){return $this->headers;}

	function getTo(){return $this->to;}
}

/*
EmailVerification类使用示例
$em=new emailContentMgr("感谢注册NCUECP","url","597055914@qq.com","message","from:xxxxx");
$ev=new EmailVerification();
$ev->send($em);
*/

?>