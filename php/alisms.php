<?php
// AppKey：23492367 
// AppSecret：b424103e3b546e850f3c80f74c45f4d6
$app_key = "23492367 ";
$app_secret = "b424103e3b546e850f3c80f74c45f4d6";
$request_paras = array(
	'ParamString' => '{"msg":"12342"}',
	'RecNum' => '13764166229',
	'SignName' => '爱车师傅',
	'TemplateCode' => 'SMS_22425014',
);

$request_host = "http://sms.market.alicloudapi.com";
$request_uri = "/singleSendSms";
$request_method = "GET";
$info = "";
$content = do_get($app_key, $app_secret, $request_host, $request_uri, $request_method, $request_paras, $info);
echo '<pre>';
print_r($content); // API返回值
print_r($info); // 系统请求返回信息

function do_get($app_key, $app_secret, $request_host, $request_uri, $request_method, $request_paras, &$info) {
	ksort($request_paras);
	$request_header_accept = "application/json;charset=utf-8";
	$content_type = "";
	$headers = array(
		"Content-Type" => "application/json",
		"X-Ca-Version" => "1",
		"gateway_channel" => "http",
		"X-Ca-Request-Mode" => "debug",
		"X-Ca-Stage" => "RELEASE",
		'X-Ca-Key' => $app_key,
		'Accept' => $request_header_accept,
		'X-Ca-Timestamp' => time() * 1000,
	);
	ksort($headers);
	$header_str = "";
	$header_ignore_list = array('X-CA-SIGNATURE', 'X-CA-SIGNATURE-HEADERS', 'ACCEPT', 'CONTENT-MD5', 'CONTENT-TYPE', 'DATE');
	$sig_header = array();
	foreach ($headers as $k => $v) {
		if (in_array(strtoupper($k), $header_ignore_list)) {
			continue;
		}
		$header_str .= $k . ':' . $v . "\n";
		array_push($sig_header, $k);
	}
	$url_str = $request_uri;
	$para_array = array();
	foreach ($request_paras as $k => $v) {
		array_push($para_array, $k . '=' . $v);
	}
	if (!empty($para_array)) {
		$url_str .= '?' . join('&', $para_array);
	}
	$content_md5 = "";
	$date = "";
	$sign_str = "";
	$sign_str .= $request_method . "\n";
	$sign_str .= $request_header_accept . "\n";
	$sign_str .= $content_md5 . "\n";
	$sign_str .= "\n";
	$sign_str .= $date . "\n";
	$sign_str .= $header_str;
	$sign_str .= $url_str;

	$sign = base64_encode(hash_hmac('sha256', $sign_str, $app_secret, true));
	$headers['X-Ca-Signature'] = $sign;
	$headers['X-Ca-Signature-Headers'] = join(',', $sig_header);
	$request_header = array();
	foreach ($headers as $k => $v) {
		array_push($request_header, $k . ': ' . $v);
	}

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $request_host . $url_str);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLINFO_HEADER_OUT, true);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $request_header);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$ret = curl_exec($ch);
	$info = curl_getinfo($ch);
	$info['error'] = curl_error($ch);
	$info['error_no'] = curl_errno($ch);
	curl_close($ch);
	return $ret;
}