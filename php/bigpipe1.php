<?php
header("Transfer-Encoding","chunked");
ini_set("output_buffering",0);
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>php实现BigPipe</title>
<style type="text/css">
<!--
body {
    font-size:12px;
    text-align:left;
}
#head {
    width:100%;
    height:40px;
    background:#8CAADE;
    font-weight:bold;
    color:#FFFFFF;
    line-height:40px;
    text-align:center;
    font-size:16px;
}
#body {
    margin:5px auto 5px auto;
    width:800px;
    border:solid 1px #8CAADE;
    padding:10px;
    line-height:30px;
}
#left {
    float:left;
    width:50%;
    border:0px;
    line-height:23px;
}
#right {
    float:right;
    width:50%;
    border:0px;
    line-height:23px;
}
#result {
    width:800px;
    height:30px;
    text-align:center;
}
#bottom {
    margin-top:5px;
    width:800px;
    height:30px;
    text-align:center;
    display:none;
}
#bottom a {
    color:red;
    text-decoration:none;
}
-->
</style>
<script>
function update(id, content) {
    document.getElementById(id).innerHTML = content;
}
</script>
</head>
<?php
echo str_repeat('  ',1024000);
ob_flush();
flush();
?>
<body>
<div id="head">php实现BigPipe</div>
<div id="body">
<div>
本demo旨在演示php的并发处理在bigpipe中的应用。因此，在一些细节上实现的比较简单。但是，demo完全体现了bigpipe的并行数据处理和并行加载。<br/>
慢动作体现的是后端php串行处理时的效果。
</div>
<div id="cost_time"></div>
<?php
/*
* @purpose: 使用curl并行处理url
* @return: array 每个url获取的数据
* @param: $urls array url列表
* @param: $callback string 需要进行内容处理的回调函数。示例：func(array)
*/
function curl($urls = array(), $callback = '')
{
    $response = array();
    if (empty($urls)) {
        return $response;
    }
    $chs = curl_multi_init();
    $map = array();
    foreach($urls as $url){
        $cookie_file = "/temp/".md5($url).".txt";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_multi_add_handle($chs, $ch);
        $map[strval($ch)] = $url;
    }
    do{
        if (($status = curl_multi_exec($chs, $active)) != CURLM_CALL_MULTI_PERFORM) {
            if ($status != CURLM_OK) { break; } //如果没有准备就绪，就再次调用curl_multi_exec
            while ($done = curl_multi_info_read($chs)) {
                $info = curl_getinfo($done["handle"]);
                $error = curl_error($done["handle"]);
                $result = curl_multi_getcontent($done["handle"]);
                $url = $map[strval($done["handle"])];
                $rtn = compact('info', 'error', 'result', 'url');
                if (trim($callback)) {
                    $callback($rtn);
                }
                $response[$url] = $rtn;
                curl_multi_remove_handle($chs, $done['handle']);
                curl_close($done['handle']);
                //如果仍然有未处理完毕的句柄，那么就select
                if ($active > 0) {
                    curl_multi_select($chs, 0.5); //此处会导致阻塞大概0.5秒。
                }
            }
        }
    }
    while($active); //还有句柄处理还在进行中
    curl_multi_close($chs);
    return $response;
}

//使用方法
function deal($data){
    $url_info = parse_url($data["url"]);
    $content_id = "content_".md5($data["url"]);
    if ($data["error"] == "") {
        $content = $url_info["host"]."&nbsp;&nbsp;能正常打开";
        echo "<script>update('$content_id', '$content')</script>";
    } else {
        $content = $url_info["host"]."&nbsp;&nbsp;访问失败。具体错误原因：".$data["error"];
        echo "<script>update('$content_id', '$content')</script>";
    }
    //因为浏览器有缓冲，即获取数据后，不会马上渲染。当获取数据大于一个值时开始渲染。
    echo str_repeat('',1024);
    ob_flush();
    flush();
    if ($_GET["m"] == "slow") {
        sleep(1);
    }
}

$keyword = "一淘网";
$google_urls = array(
'http://www.google.com.br',//巴西
'http://www.google.ch',//瑞士
'http://www.google.nl',//荷兰
'http://www.google.com.au',//澳大利亚
'http://www.google.co.in',//印度
'http://www.google.ro',//罗马尼亚
'http://www.google.co.th',//泰国
'http://www.google.com.sa',//沙特阿拉伯
'http://www.google.co.jp',//日本
'http://www.google.com.my',//马来西亚
'http://www.google.ca',//加拿大
'http://www.google.com.tw',//中国台湾
);

$google_num = 10;//从google网址中取几个
$keys = array_rand($google_urls,$google_num);
$urls[] = "http://www.baidu.com/s?wd=".urlencode($keyword);
$urls[] = "http://www.etao.com";
for ( $i = 0; $i < $google_num; $i++) {
    $urls[] = $google_urls[$keys[$i]]."/search?sclient=psy-ab&hl=en&site=&source=hp&q=".urlencode($keyword);
}

//生成占位div
foreach($urls as $url){
    echo "<div class='item' id='content_".md5($url)."'>{$url}&nbsp;&nbsp;检测中</div>";
}
//除了浏览器web服务器可能也会缓冲，如nginx的fastcgi_buffers。
echo str_repeat('  ',1024000);
// ob_end_flush();
ob_flush();
flush();

//开始获取搜索数据
$start = microtime(true);
curl($urls, "deal");
$cost_time = microtime(true) - $start;
echo "<script>update('cost_time', '耗时{$cost_time}秒&nbsp;&nbsp;<a href=\'?m=slow\'>查看慢动作</a>')</script>";
?>
</div>
</body>
</html>