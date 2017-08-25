<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
	<title>ajax捕获错误异常demo</title>
	<meta charset="utf-8">
	<script type="text/javascript" src="http://www.simuwang.com/Public/Js/Simuwang/jquery-1.11.3.min.js"></script>
	<script type="text/javascript">
	  function show_success(msg){
	  	alert("success:"+msg);
	  }
	  function show_error(msg){
	  	alert("error:"+msg);
	  }
	</script>
</head>
<body>
<h2>捕获异常</h2>
</body>
<script type="text/javascript">
$(function(){
	$.ajax({
		url:"<?php echo U('Index/getError');?>",
		data:{a:2},
		type:'get',
		dataType:'json',
		success:function(rec){
			show_success(rec.readState);
		},
		error:function(rec){
			var obj=eval("("+rec.responseText+")");
			show_error(obj.message);
		}
	});
});
</script>
</html>