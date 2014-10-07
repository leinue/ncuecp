<?php

require('config.php');
require('functions.php');
require('tableObj.php');

/**
* 上传图片类
*/
class uploadPic{

	function upload(
		$max_file_size=2000000,$destination_folder,$formname){
 		 //上传文件类型列表
		$uptypes=array(
   		'image/jpg',
    	'image/jpeg',
   		'image/png',
    	'image/pjpeg',
    	'image/gif',
    	'image/bmp',
    	'image/x-png'
		);

		if ($_SERVER['REQUEST_METHOD'] == 'POST'){
   			if (!is_uploaded_file($_FILES[$formname]["tmp_name"])){
         		//图片不存在
         		return -1;
         		exit;
    		}

    		$file = $_FILES[$formname];
   			if($max_file_size < $file["size"]){
        		//文件太大!
        		return -2;
        		exit;
    		}

    		if(!in_array($file["type"], $uptypes)){
        		//文件类型不符!".$file["type"]
        		return -3;
        		exit;
    		}

    		if(!file_exists($destination_folder)){
        		mkdir($destination_folder);
    		}

    		$filename=$file["tmp_name"];
    		$image_size = getimagesize($filename);
    		$pinfo=pathinfo($file["name"]);
    		$ftype=$pinfo['extension'];
    		$destination = $destination_folder.time().".".$ftype;//文件名
    		if (file_exists($destination) && $overwrite != true){
        		//同名文件已经存在了
        		return -4;
        		exit;
    		}

    		if(!move_uploaded_file ($filename, $destination)){
        		//移动文件出错
        		return -5;
        		exit;
    		}

    		$pinfo=pathinfo($destination);
    		$fname=$pinfo["basename"];

    		/*$final_data=array(
      		"dest" => $destination_folder.$fname,
      		"width" => $image_size[0],
      		"height" => $image_size[1],
      		);*/

    		return $destination_folder.$fname;
    		//echo " <font color=red>已经成功上传</font><br>文件名:  <font color=blue>".$destination_folder.$fname."</font><br>";
    		//echo " 宽度:".$image_size[0];
    		//echo " 长度:".$image_size[1];
    		//echo "<br> 大小:".$file["size"]." bytes";
		}

	}
}

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
	public $changePicture="UPDATE `profile` SET `face`=? WHERE `email`=?";
	public $changePassword="UPDATE `profile` SET `password`=? WHERE `email`=?";
	public $checkOldpw="SELECT `uid` FROM `profile` WHERE `email`=? AND `password`=SHA1(?)";
	public $editProfile="UPDATE `profile` SET `userName`=?,`sex`=?,`university`=?,`college`=?,`flat`=?,`contact`=? WHERE `email`=?";
	public $placeAnOrder="INSERT INTO `order`(`uid`, `gid`, `isPaid`, `cid`, `expressFee`) VALUES (?,?,?,?,?)";
	//gid=goodsID cid=courierID信使,陪送者
	public $undoPlaceOrder="DELETE FROM `order` WHERE `oid`=?";
	public $pushGoods="INSERT INTO `goods`(`sid`, `price`, `remarks`) VALUES (?,?,?)";//sid-supplierID
	public $popGoods="DELETE FROM `goods` WHERE `gid`=?";

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
			return;
		}
		if(count($regInfo)!=3){
			throw new Exception("array \$regInfo must contain three elements.", 1);
			return;
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

/**
* 个人资料管理类
* 构造函数较之前有点不同,在填写pdo参数的同时还要填写邮箱
* @changeHeas()用来修改头像,更换成功返回true,更换失败返回false
* @changePassword()用来修改密码,成功返回true,失败返回false,旧密码与数据库不吻合返回-1
* @checkOldpw()用来验证旧密码是否与数据库里的数据吻合,吻合返回true,不吻合返回false
* @editProfile()用来修改用户资料,数组参数$arr的元素顺序为userNmae,sex,university,college,flat,contact
*/
class profileMgr extends pdoOperation{

	protected $email;

	function __construct($email,$pdo){
		$this->email=$email;
		parent::$pdo=$pdo;
	}
	
	function changeHeas($pic){
		return $this->submitQuery($this->changePicture,array($pic,$this->email));}

	function changePassword($oldpw,$newpw){
		if($this->checkOldpw($oldpw)){
			return $this->submitQuery($this->changePassword,array($newpw,$this->email));
		}else{
			return -1;
		}
	}

	function checkOldpw($oldpw){
		return $this->fetchOdd($this->checkOldpw,array($this->email,$oldpw));}

	function editProfile($arr){
		if(!is_array($arr)){
			throw new Exception("parameter arr must be an array", 1);
			return;
		}
		if(count($arr)!=6){
			throw new Exception("parameter arr must contain 6 elements", 1);
			return;
		}
		return $this->submitQuery($this->editProfile,$arr);
	}

	function isExist(){
		return $this->fetchOdd($this->loadEmail,array($this->email));}

}

/**
* order类
* @place()函数执行下订单操作,数组参数$orderInfo元素顺序为uid,gid,isPaid,cid,expressFee
* @undo()函数将撤销一个订单,撤销成功返回true,撤销失败返回false
*/
class order extends pdoOperation{
	
	function place($orderInfo){
		if(!is_array($orderInfo)){
			throw new Exception("orderInfo must be an array", 1);
			return;
		}
		if(count($orderInfo!=5)){
			throw new Exception("orderInfo must contain 5 elements", 1);
			return;
		}
		$this->submitQuery($this->placeAnOrder,$orderInfo);
	}

	function undo($oid){//oid=orderID
		return $this->submitQuery($this->undoPlaceOrder,array($oid));}
}

/**
* goods类
* @push()将一个商品压入仓库,成功返回true,失败返回false,数组参数顺序为sid(supplierID),price,remarks
* @pop()删除一个商品,成功返回true,失败返回false
*/
class goods extends pdoOperation{
	
	function push($arr){
		if(count($arr)!=3){
			throw new Exception("arr must contain 3 elements.", 1);
			return;
		}
		$this->submitQuery($this->pushGoods,$arr);
	}

	function pop($gid){
		return $this->submitQuery($this->popGoods,array($gid));}
}

/**
* supplier类
*/
class supplier extends pdoOperation{
	
	function __construct(){
		# code...
	}
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

/*$pm=new profileMgr("123@qq.com",$pdo);
if($pm->checkOldpw("123456")){
	echo 'dsdssd';
}*/

?>