<?php
require(__DIR__.'/config/config.php');
if (!in_array(PHP_SAPI, $C["allowsapi"])) {
	exit("No permission");
}

require(__DIR__.'/function/curl.php');
require(__DIR__.'/function/log.php');
require(__DIR__.'/function/sendmessage.php');

$sth = $G["db"]->prepare("SELECT * FROM `{$C['DBTBprefix']}input` ORDER BY `time` ASC");
$res = $sth->execute();
$row = $sth->fetchAll(PDO::FETCH_ASSOC);
foreach ($row as $data) {
	$sth = $G["db"]->prepare("DELETE FROM `{$C['DBTBprefix']}input` WHERE `hash` = :hash");
	$sth->bindValue(":hash", $data["hash"]);
	$res = $sth->execute();
}
function GetTmid() {
	global $C, $G;
	$res = cURL($C['FBAPI']."me/conversations?fields=participants,updated_time&access_token=".$C['FBpagetoken']);
	$updated_time = file_get_contents("updated_time.txt");
	$newesttime = $updated_time;
	while (true) {
		if ($res === false) {
			WriteLog("[follow][error][getuid]");
			break;
		}
		$res = json_decode($res, true);
		if (count($res["data"]) == 0) {
			break;
		}
		foreach ($res["data"] as $data) {
			if ($data["updated_time"] <= $updated_time) {
				break 2;
			}
			if ($data["updated_time"] > $newesttime) {
				$newesttime = $data["updated_time"];
			}
			foreach ($data["participants"]["data"] as $participants) {
				if ($participants["id"] != $C['FBpageid']) {
					$sth = $G["db"]->prepare("INSERT INTO `{$C['DBTBprefix']}user` (`uid`, `tmid`, `name`) VALUES (:uid, :tmid, :name)");
					$sth->bindValue(":uid", $participants["id"]);
					$sth->bindValue(":tmid", $data["id"]);
					$sth->bindValue(":name", $participants["name"]);
					$res = $sth->execute();
					break;
				}
			}
		}
		$res = cURL($res["paging"]["next"]);
	}
	file_put_contents("updated_time.txt", $newesttime);
}
function GetResult($res) {
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe52\.gif\".*?>/", "(一) ", $res);
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe53\.gif\".*?>/", "(二) ", $res);
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe54\.gif\".*?>/", "(三) ", $res);
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe55\.gif\".*?>/", "(四) ", $res);
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe56\.gif\".*?>/", "(五) ", $res);
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe57\.gif\".*?>/", "(六) ", $res);
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe58\.gif\".*?>/", "(七) ", $res);
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe59_?\.jpg\".*?>/", "(1) ", $res);
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe5a_?\.jpg\".*?>/", "(2) ", $res);
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe5b_?\.jpg\".*?>/", "(3) ", $res);
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe5c_?\.jpg\".*?>/", "(4) ", $res);
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe5d_?\.jpg\".*?>/", "(5) ", $res);
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe5e_?\.jpg\".*?>/", "(6) ", $res);
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe5f_?\.jpg\".*?>/", "(7) ", $res);
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe60_?\.jpg\".*?>/", "(8) ", $res);
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe61_?\.jpg\".*?>/", "(9) ", $res);
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe62_?\.jpg\".*?>/", "(10) ", $res);
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe63_?\.jpg\".*?>/", "(11) ", $res);
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe64_?\.jpg\".*?>/", "(12) ", $res);
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe65_?\.jpg\".*?>/", "(13) ", $res);
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe66_?\.jpg\".*?>/", "(14) ", $res);
	$res = preg_replace("/<img src=\"\/cbdic\/images\/words\/fe67_?\.jpg\".*?>/", "(15) ", $res);
	$response = "";
	if (preg_match("/字詞.*?<\/b><\/th><td class=\"std2\">(.*?)<\/td>/", $res, $m)) {
		$response .= "字詞：".html_entity_decode(strip_tags($m[1]))."\n";
	}
	if (preg_match("/注音.*?<\/b><\/th><td class=\"std2\">(.*?)<\/td>/", $res, $m)) {
		$response .= "注音：".strip_tags($m[1])."\n";
	}
	if (preg_match("/漢語拼音.*?<\/b><\/th><td class=\"std2\">(.*?)<\/td>/", $res, $m)) {
		$response .= "漢語拼音：".strip_tags($m[1])."\n";
	}
	if (preg_match("/相似詞.*?<\/b><\/th><td class=\"std2\">(.*?)<\/td>/", $res, $m)) {
		$response .= "相似詞：".strip_tags($m[1])."\n";
	}
	if (preg_match("/相反詞.*?<\/b><\/th><td class=\"std2\">(.*?)<\/td>/", $res, $m)) {
		$response .= "相反詞：".strip_tags($m[1])."\n";
	}
	if (preg_match("/釋義.*?<\/b><\/th><td class=\"std2\">(.*?)\n/", $res, $m)) {
		$m[1] = str_replace("\r", "", $m[1]);
		$m[1] = str_replace("</p>", "</p>\n", $m[1]);
		$m[1] = str_replace("</li>", "</li>\n", $m[1]);
		$m[1] = strip_tags($m[1]);
		$m[1] = str_replace("　", " ", $m[1]);
		$m[1] = preg_replace("/\s\s+$/", " ", $m[1]);
		$m[1] = preg_replace("/^ $/m", "", $m[1]);
		$m[1] = preg_replace("/\n+$/", "", $m[1]);
		$response .= "釋義：\n".$m[1]."\n";
	}
	if (preg_match("/本頁網址︰<\/span><input type=\"text\" value=\"(.+?)\" size/", $res, $m)) {
		$response .= "\n本頁網址：\n".$m[1]."\n";
	}
	return $response;
}
$W["success"] = true;
if ($W["success"]) {
	$res = cURL("http://dict.revised.moe.edu.tw/cbdic/search.htm", false, $C["cookiepath"]);
	if ($res === false) {
		$W["success"] = false;
		WriteLog("[res][error] fetch page 1");
	}
}
if ($W["success"]) {
	preg_match("/<a href=\"\/cgi-bin\/cbdic\/gsweb\.cgi\/\?&o=(.*?)&\" title/", $res, $m);
	$W["o"] = $m[1];
	$res = cURL("http://dict.revised.moe.edu.tw/cgi-bin/cbdic/gsweb.cgi/?&o={$W["o"]}&", false, $C["cookiepath"]);
	if ($res === false) {
		$W["success"] = false;
		WriteLog("[res][error] fetch page 2");
		exit;
	}
}
if ($W["success"]) {
	if (preg_match("/<a href=\"\/cgi-bin\/cbdic\/gsweb\.cgi\?ccd=(.*?)&o=(.*?)&sec=(.*?)&index=.*?\" title/", $res, $m)) {
		$W["ccd"] = $m[1];
		$W["o"] = $m[2];
		$W["sec"] = $m[3];
	} else {
		WriteLog("[res][error] fetch page token");
	}
}
foreach ($row as $data) {
	$input = json_decode($data["input"], true);
	foreach ($input['entry'] as $entry) {
		foreach ($entry['messaging'] as $messaging) {
			$mmid = "m_".$messaging['message']['mid'];
			$res = cURL($C['FBAPI'].$mmid."?fields=from&access_token=".$C['FBpagetoken']);
			$res = json_decode($res, true);
			if (!isset($res["from"]["id"])) {
				WriteLog("[rees][error][nouid] msg=".json_encode($res));
				continue;
			}
			$uid = $res["from"]["id"];

			$sth = $G["db"]->prepare("SELECT * FROM `{$C['DBTBprefix']}user` WHERE `uid` = :uid");
			$sth->bindValue(":uid", $uid);
			$sth->execute();
			$user = $sth->fetch(PDO::FETCH_ASSOC);
			if ($user === false) {
				GetTmid();
				$sth->execute();
				$user = $sth->fetch(PDO::FETCH_ASSOC);
				if ($user === false) {
					WriteLog("[rees][error][uid404] uid=".$uid);
					continue;
				} else {
					WriteLog("[res][info][newuser] uid=".$uid);
				}
			}
			$tmid = $user["tmid"];
			if (!isset($messaging['message']['text'])) {
				SendMessage($tmid, "請輸入欲搜尋文字");
				continue;
			}
			if (!$W["success"]) {
				SendMessage($tmid, "抓取資料失敗，請稍後再試\n".
					"您可以查看網頁是否正常\n".
					"http://dict.revised.moe.edu.tw/cbdic/search.htm");
				continue;
			}
			$msg = $messaging['message']['text'];
			$post = array(
				"o" => $W["o"],
				"ccd" => $W["ccd"],
				"sec" => $W["sec"],
				"selectmode" => "mode1",
				"qs0" => "^".$msg."$",
			);
			$res = cURL("http://dict.revised.moe.edu.tw/cgi-bin/cbdic/gsweb.cgi", $post, $C["cookiepath"]);
			if ($res === false) {
				WriteLog("[res][error] fetch page search 1");
				SendMessage($tmid, "抓取資料失敗，請稍後再試\n".
					"您可以查看網頁是否正常\n".
					"http://dict.revised.moe.edu.tw/cbdic/search.htm");
				continue;
			}
			$mulit = preg_match("/正文資料<font class=numfont>(\d+)<\/font>則/", $res, $m);
			if ($mulit) {
				$cnt = $m[1];
				if ($cnt == 0) {
					SendMessage($tmid, "在教育部重編國語辭典修訂本裡找不到「".$msg."」\n".
						"或許您想要自行搜尋 http://dict.revised.moe.edu.tw/cbdic/search.htm");
					continue;
				} else {
					if ($cnt != 1) {
						SendMessage($tmid, "找到".$cnt."則結果");
					}
					preg_match_all("/<td class=maintd.>[^<]*<a href=\"(.+?)\" class/", $res, $m);
					foreach ($m[1] as $key => $url) {
						$res = cURL("http://dict.revised.moe.edu.tw/".$url, false, $C["cookiepath"]);
						if ($res === false) {
							WriteLog("[res][error] fetch page search 2");
							SendMessage($tmid, "抓取資料失敗，請稍後再試\n".
								"您可以查看網頁是否正常\n".
								"http://dict.revised.moe.edu.tw/cbdic/search.htm");
							break;
						}
						$response = GetResult($res);
						if ($cnt != 1) {
							$response = "#".($key+1)."\n".$response;
						}
						SendMessage($tmid, $response);
					}
				}
			} else {
				$response = GetResult($res);
				SendMessage($tmid, $response);
			}
			$lastlicense = $user["lastlicense"];
			if (time() - strtotime($lastlicense) > $C['show_license_interval']) {
				SendMessage($tmid, "來源：\n".
					"中華民國教育部（Ministry of Education, R.O.C.）。《重編國語辭典修訂本》（版本編號：2015_20160523）網址：dict.revised.moe.edu.tw\n".
					"創用 CC－姓名標示－禁止改作 臺灣3.0 版授權條款");
				$sth = $G["db"]->prepare("UPDATE `{$C['DBTBprefix']}user` SET `lastlicense` = :lastlicense WHERE `tmid` = :tmid");
				$sth->bindValue(":lastlicense", date("Y-m-d H:i:s"));
				$sth->bindValue(":tmid", $tmid);
				$res = $sth->execute();
				if ($res === false) {
					WriteLog("[fetch][error][res][license] tmid=".$tmid." msg=".json_encode($sth->errorInfo()));
				}
			}
		}
	}
}
