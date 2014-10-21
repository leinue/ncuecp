<?php

/**
*  user
*/
class user{
	private $uid;
	private $userName;
	private $password;
	private $email;
	private $regTime;
	private $isValid;
	private $lastLoginTime;
	private $ip;
	private $face;
	private $sex;
	private $university;
	private $college;
	private $flat;
	private $returnVisit;
	private $contact;
	
	function getUid(){return $this->uid;}

	function getUserName(){return $this->userName;}

	function getPassword(){return $this->password;}

	function getEmail(){return $this->email;}

	function getRegTime(){return $this->regTime;}

	function getValid(){return $this->isValid;}

	function getLastLoginTime(){return $this->lastLoginTime;}

	function getIP(){return $this->ip;}

	function getFace(){return $this->face;}

	function getSex(){return $this->getSex;}

	function getUniversity(){return $this->university;}

	function getCollege(){return $this->college;}

	function getFlat(){return $this->flat;}

	function getReturnVisit(){return $this->returnVisit;}

	function getContact(){return $this->contact;}
}

/**
* 
*/
class msgObj{
	
	private $mid;
	private $_from;
	private $_to;
	private $content;
	private $datetime;
	private $isread;

	function getMid(){return $this->mid;}

	function getFrom(){return $this->_from;}

	function getTo(){return $this->to;}

	function getContent(){return $this->content;}

	function getTIme(){return $this->datetime;}

	function isRead(){return $this->isread;}


}

?>