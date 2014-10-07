<?php

require('config.php');
require('functions.php');
require('tableObj.php');

/**
* class pdoOperation
* 用于封装查询语句时的必须查询过程
* @submitQuery()使用pdo的预处理语句进行查询,比较安全
* @fetchClassQuery()返回对应数据库的class
* @fetchOdd()返回单个值
*/
class pdoOperation{

	public $emailCheck="UPDATE `profile` SET `isValid`=`1` WHERE `uid`=?";
	public $forgotPassword="";
	public $loadEmail="SELECT * FROM `profile` WHERE `email`=?";
	public $changeEmail="UPDATE `profile` SET `email`=`?` WHERE `uid`=?";
	public $addNewUser="INSERT INTO `profile`(`userName`, `password`, `email`, `isValid`, `lastLoginTime`, `ip`, `face`, `sex`, `university`, `college`, `flat`, `returnVisit`, `contact`) 
	VALUES (?,SHA1(?),?,'0',?,?,'user/default.jpg','boy','南昌大学','南昌大学新闻与传播学院','000000','0','暂无')";
	public $userEnter="SELECT * FROM `profile` WHERE `email`=? and `password`=SHA1(?)";
	public $updateLoginInfo="UPDATE `profile` SET `lastLoginTime`=?,`ip`=concat(`ip`,?) WHERE `email`=?";
	public $updateProfile="";

	protected static $pdo;
	
	function __construct($pdo){
		self::$pdo=$pdo;
		self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //禁用prepared statements的仿真效果
	}

	function submitQuery($sql,$arr){
		if(!(is_array($arr))){
			return false;
		}
		$stmt=self::$pdo->prepare($sql);
		$result=$stmt->execute($arr);
		return $result;
	}

	function fetchClassQuery($sql,$arr,$className){
		if(!(is_array($arr))){
			return false;
		}
		$stmt=self::$pdo->prepare($sql);
		$res=$stmt->execute($arr);

		$stmt->setFetchMode(PDO::FETCH_CLASS,$className);

		if ($res) {
			if($draft=$stmt->fetch()) {
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
		$stmt=self::$pdo->prepare($sql);

		if($stmt){
			$stmt->execute($arr);
			$row=$stmt->fetch();
			return $row;
		}else{return false;}
	}
}

/**
* Character verification
* 用于检测用户输入的邮箱/密码/用户名等是否合法并且过滤掉一些HTML/SQL注入字符
*/
class characterVerification extends pdoOperation{

	function test($data){
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}

	function emailIsExist($email){
		return $this->fetchOdd($this->loadEmail,array($email));}

	function pwIsValid($pw){
		return strlen($pw)<=16;}

	function emailIsValid($email){
		$eregp="^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$";
		return eregi($eregp,$email);
	}
}

/**
* class EmailVerification
* 用于注册/忘记密码/改变邮箱时的邮箱验证
* 使用组合模式设计
* @verifyStart()验证注册
* @verifyChange()改变邮箱
* **************************************************************************************
* 注册时的消息示例
* $to = $this->email;
* $subject = "感谢注册NCUECP";
* $regUrl="method=reg";
* $message = "<a href=\"".$reg_url."\">欢迎注册NCUECP,请点击这里进行激活帐号</a>";
* $from = "someonelse@example.com";
* $headers = "From: $from";
* **************************************************************************************
* EmailVerification类使用示例
* $em=new emailContentMgr("感谢注册NCUECP","url","597055914@qq.com","message","from:xxxxx");
* $ev=new EmailVerification();
* $ev->send($em);
*/

class EmailVerification{
	protected $uid;

	function __construct($uid){$this->uid=$uid;}

	function send(emailContentMgr $ecm){
		mail($ecm->getTo(),$ecm->getSubject(),$ecm->getMessage(),$ecm->getHeaders());}

	function verifyStart(pdoOperation $pdoo){
		return $pdoo->submitQuery($pdoo->emailCheck,array($this->uid));}

	function verifyPassword(){

	}

	function verifyChange(pdoOperation $pdoo,$newEmail){
		return $pdoo->submitQuery($pdoo->changeEmail,array($newEmail,$this->uid));}
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

/**
* 注册用户类
* @reg()中数组参数$regInfo元素的顺序为:姓名,密码,邮箱
* @reg()返回-1表示邮箱重复,返回-2表示邮箱不合法,返回-3表示密码不合法
*/
class register extends pdoOperation{
	
	function reg($regInfo){
		if(!is_array($regInfo)){
			throw new Exception("\$regInfo must be an array.", 1);
		}
		if(count($regInfo)!=3){
			throw new Exception("array \$regInfo must contain three elements.", 1);
		}

		$cv=new characterVerification(self::$pdo);
		$fix=$cv->test($regInfo[2]);
		if($cv->emailIsValid($fix)){
			if(!($cv->emailIsExist($fix))){
				if($cv->pwIsValid($regInfo[1])){
					date_default_timezone_set("Etc/GMT+8");
					$nowTime=date('Y-m-d H:i:s',time());
					$uip=getIp();
					$regInfo[]=$nowTime;
					$regInfo[]=$uip;
					return $this->submitQuery($this->addNewUser,$regInfo);
				}else{return -3;}
			}else{return -1;}
		}else{return -2;}
	}

}

/**
* 用户登录类 
* @enter()在验证成功后返回包含用户信息的类
* @enter()返回-1表示邮箱重复,返回-2表示邮箱不合法,返回-3表示密码不合法
* @update()用来更新用户登录后的lastLoginTime和ip数据
* *************************************************************************
* $log=new login($pdo);
* if($a=$log->enter("123@qq.com","123456")){
*	echo $a->getUserName();
* }
*/
class login extends pdoOperation{
	
	function enter($email,$password){
		$cv=new characterVerification(self::$pdo);
		$fix=$cv->test($email);
		if($cv->emailIsValid($fix)){
			if($cv->pwIsValid($password)){
				$re=$this->fetchClassQuery($this->userEnter,array($email,$password),'user');
				$up=$this->update($email);
				if($re && $up){
					return $re;
				}
			}else{return -3;}
		}else{return -2;}
	}

	private function update($email){
		date_default_timezone_set("Etc/GMT+8");
		$nowTime=date('Y-m-d H:i:s',time());
		$uip="|".getIp();
		return $this->submitQuery($this->updateLoginInfo,array($nowTime,$uip,$email));}
}

try {
	$pdo=new PDO("mysql:dbname=$dbname;host=$host",$user,$password);
} catch (PDOException $e) {
	echo $e->getMessage();
}

//$regin=new register($pdo);
//echo $regin->reg(array("233","123456","123@qq.com"));

//$cv=new characterVerification($pdo);
//print_r($cv->emailIsExist($cv->test("123@qq.com")));

/*$log=new login($pdo);
if($a=$log->enter("123@qq.com","123456")){
	echo $a->getUserName();
}*/

?>