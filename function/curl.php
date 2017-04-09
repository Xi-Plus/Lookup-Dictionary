<?php
function cURL($url, $post=false, $cookie=false) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	if ($post !== false) {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
	}
	if ($cookie !== false) {
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$res = curl_exec($ch);
	curl_close($ch);
	if ($res === false) {
		return false;
	}
	return $res;
}
