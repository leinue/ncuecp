<?php

$sql=array(
	"profile"=>"CREATE TABLE `profile`( `uid` int not null auto_increment, `userName` char(30) not null, `password` char(40) not null, `email` text not null, `regTime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `isValid` int(1) not null, `lastLoginTime` TIMESTAMP not null, `ip` text not null, `face` text not null, `sex` text not null, `university` text not null, `college` text not null, `flat` text not null, `returnVisit` text not null, `contact` text not null, primary key(`uid`) )default charset=utf8;",
	"order"=>"CREATE TABLE `order`( `oid` int not null auto_increment, `uid` int not null, `gid` int not null, `orderTime` timestamp default current_timestamp, `isPaid` int not null, `cid` int not null, `expressFee` int not null, primary key(`oid`) )default charset=utf8;",
	"goods"=>"CREATE TABLE `goods`( `gid` int not null auto_increment, `sid` int not null, `price` float not null, `remarks` text not null, primary key(`gid`) )default charset=utf8;",
	"supplier"=>"CREATE TABLE `supplier`( `sid` int not null auto_increment, `name` text not null, `location` text not null, `master` text not null, `contact` text not null, primary key(`sid`) )default charset=utf8;",
	"oldBooks"=>"CREATE TABLE `oldBooks`( `bid` int not null auto_increment, `name` text not null, `isbn` text not null, `editor` text not null, `publicTime` timestamp not null, `remarks` text not null, `pic` text not null, `price` int not null, primary key(`bid`) )default charset=utf8;",
	"orderBooks"=>"CREATE TABLE `booksOrder`( `boid` int not null auto_increment, `bid` int not null, `purchaser` int not null, `orderTime` timestamp default current_timestamp, `guestbook` text, `isPaid` int not null, `method` enum('online','offline'), primary key(`boid`) )default charset=utf8;");


?>