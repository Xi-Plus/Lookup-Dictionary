<?php

$C['FBpageid'] = 'page_id';
$C['FBpagetoken'] = 'page_token';
$C['FBWHtoken'] = 'Webhooks_token';
$C['FBAPI'] = 'https://graph.facebook.com/v2.8/';

$C["DBhost"] = 'localhost';
$C['DBname'] = 'dbname';
$C['DBuser'] = 'user';
$C['DBpass'] = 'pass';
$C['DBTBprefix'] = 'dictionary_';

$G["db"] = new PDO ('mysql:host='.$C["DBhost"].';dbname='.$C["DBname"].';charset=utf8', $C["DBuser"], $C["DBpass"]);

$M["nottext"] = "請輸入欲搜尋文字";
$M["fail"] = "抓取資料失敗，請稍後再試";
$M["license"] = "來源：\n".
	"中華民國教育部（Ministry of Education, R.O.C.）。《重編國語辭典修訂本》（版本編號：2015_20160523）網址：dict.revised.moe.edu.tw\n".
	"創用 CC－姓名標示－禁止改作 臺灣3.0 版授權條款";
