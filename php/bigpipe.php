<?php
header("Transfer-Encoding","chunked");
ini_set("output_buffering",0);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <title> Big Pipe </title>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  <style type="text/css">
  body{margin:0px; background:#CCCCCC;}
  p{text-align:center; margin:10px;}
  img{width:450px;}
  </style>
 </head>
 <body>  
  <p><img src="http://image.tianjimedia.com/uploadImages/2013/240/5CPOE4UZ2T40.jpg"></p>
  <?php cache_flush(); ?>
  <p><img src="http://image.tianjimedia.com/uploadImages/2013/240/6893CY9XEQD1.jpg"></p>
  <?php cache_flush(2); ?>
  <p><img src="http://image.tianjimedia.com/uploadImages/2013/240/83H52SG02V32.jpg"></p>
 </body>
</html>
<?php
function cache_flush($sec=2){
    echo str_repeat('  ',1024);
    ob_flush();
    flush();
    // ob_flush();
    // flush();
    sleep($sec);
}
?>