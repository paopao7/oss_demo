<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title>Title</title>
    <style type="text/css">
        .file_item{
            line-height: 50px;
        }

        .submit_btn{
            color: #ffffff;
            margin-top: 30px;
            width: 80px;
            height: 35px;
            background-color: green;
            border: none;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <form action="<?php echo U('Home/Index/upload');?>" method="post" enctype="multipart/form-data">
        <div class="file_item">
            <div>图片</div>
            <input type="file" name="image">
        </div>
        <button type="submit" class="submit_btn">提交</button>
    </form>
</body>
</html>