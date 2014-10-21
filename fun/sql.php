<?php

require('config.php');

function createTable(){

	$sql=array(
		"profile"=>"CREATE TABLE `profile`( 
			`uid` int not null auto_increment, 
			`userName` char(30) not null, 
			`password` char(40) not null, 
			`email` text not null, 
			`regTime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
			`isValid` int(1) not null, 
			`lastLoginTime` TIMESTAMP not null, 
			`ip` text not null, 
			`face` text not null, 
			`sex` text not null, 
			`university` text not null, 
			`college` text not null, 
			`flat` text not null, 
			`returnVisit` text not null, 
			`contact` text not null, 
			primary key(`uid`) )default charset=utf8;",
		"order"=>"CREATE TABLE `order`( 
			`oid` int not null auto_increment, 
			`uid` int not null, 
			`gid` int not null, 
			`orderTime` timestamp default current_timestamp, 
			`isPaid` int not null, 
			`cid` int not null, 
			`expressFee` int not null, 
			primary key(`oid`) )default charset=utf8;",
		"goods"=>"CREATE TABLE `goods`( 
			`gid` int not null auto_increment, 
			`sid` int not null, 
			`price` float not null, 
			`remarks` text not null, 
			primary key(`gid`) )default charset=utf8;",
		"supplier"=>"CREATE TABLE `supplier`( 
			`sid` int not null auto_increment, 
			`name` text not null, 
			`location` text not null, 
			`master` text not null, 
			`contact` text not null, 
			primary key(`sid`) )default charset=utf8;",
		"oldBooks"=>"CREATE TABLE `oldBooks`( 
			`bid` int not null auto_increment, 
			`name` text not null, 
			`isbn` text not null, 
			`editor` text not null, 
			`publicTime` timestamp not null, 
			`remarks` text not null, 
			`pic` text not null, 
			`price` int not null, 
			primary key(`bid`) )default charset=utf8;",
		"orderBooks"=>"CREATE TABLE `booksOrder`( 
			`boid` int not null auto_increment, 
			`bid` int not null, 
			`purchaser` int not null, 
			`orderTime` timestamp default current_timestamp, 
			`guestbook` text, `isPaid` int not null, 
			`method` enum('online','offline'), 
			primary key(`boid`) )default charset=utf8;",
		"orderLog"=>"CREATE TABLE `orderLog`( 
			`lid` int not null auto_increment, 
			`uid` int not null, 
			`oid` int not null, 
			`otime` timestamp default current_timestamp, 
			primary key(`lid`) )default charset=utf8;",
		"orderForm"=>"CREATE TABLE `orderForm`( 
			`fid` int not null auto_increment, 
			`lid` int not null, 
			`uidLaunch` int not null, 
			`uidAccept` int not null, 
			`createTime` timestamp default current_timestamp, 
			primary key(`fid`) )default charset=utf8",
		"msg"=>"CREATE TABLE msg( 
			`mid` int NOT NULL AUTO_INCREMENT, 
			`_from` int NOT NULL, 
			`_to` int NOT NULL, 
			`content` TEXT NOT NULL, 
			`datetime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
			`isread` enum('y','n') NOT NULL, 
			PRIMARY KEY (`mid`) )DEFAULT CHARSET=utf8;",
		"msgMonitor"=>"CREATE TABLE msgMonitor( 
			`mmid` int not null auto_increment, 
			`mid` int not null, 
			primary key(`mmid`) )default charset=utf8;");

	$pdo=new PDO("mysql:dbname=$dbname;host=$host",$user,$password);

	foreach ($sql as $key => $sqlStatement) {
		$res=$pdo->exec($sqlStatement);
		if(!$res){
			print_r($pdo->errorInfo());
		}
	}

}

?>