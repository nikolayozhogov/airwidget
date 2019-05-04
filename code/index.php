<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

</head>
<body>

    <textarea onclick="this.select()" id="slackwidget_code"
              style="width:100%; height:50px; font-size:14px; border:none;"></textarea>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var url = new URL(window.location.href);
            var id = url.searchParams.get("id");
            document.getElementById("slackwidget_code").value = '<iframe src="https://event.airwidget.app/widget/?id=' + id + '" frameborder="0" scrolling="no" horizontalscrolling="no" verticalscrolling="no" width="100%" height="540px" async></iframe>';
        })
    </script>

</body>
</html>