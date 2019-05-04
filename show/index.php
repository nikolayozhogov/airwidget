<?php

if(empty($_REQUEST['id']))
    exit('parametr id not specified');

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slack Widget</title>
</head>
<body style="background: #fff">

<div style="text-align: center; margin-top: 150px;">
    <iframe style="width:900px; height:450px; border-radius: 10px; box-shadow: 0px 5px 30px 5px #d2d2d2" src="https://event.airwidget.app/widget/?id=<?=$_REQUEST['id']?>" frameborder="0" scrolling="no" horizontalscrolling="no" verticalscrolling="no" async></iframe>
</div>

</body>
</html>