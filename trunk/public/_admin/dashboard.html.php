<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Static Management</title>

<!-- CSS -->
<link href="<?=BASEURL?>/style/css/transdmin.css" rel="stylesheet" type="text/css" media="screen" />
<!--[if IE 6]><link rel="stylesheet" type="text/css" media="screen" href="<?=BASEURL?>/style/css/ie6.css" /><![endif]-->
<!--[if IE 7]><link rel="stylesheet" type="text/css" media="screen" href="<?=BASEURL?>/style/css/ie7.css" /><![endif]-->

<!-- JavaScripts-->
<script type="text/javascript" src="<?=BASEURL?>/style/js/jquery.js"></script>
<script type="text/javascript" src="<?=BASEURL?>/style/js/jNice.js"></script>
</head>

<body>
	<div id="wrapper">
    	<!-- h1 tag stays for the logo, you can use the a tag for linking the index page -->
    	<h1><a href="#"><span>Infomedia</span></a></h1>
        
        <!-- You can name the links with lowercase, they will be transformed to uppercase by CSS, we prefered to name them with uppercase to have the same effect with disabled stylesheet -->
        <ul id="mainNav">
            <li><a href="<?=BASEURL?>/dashboard" class="active">DASHBOARD</a></li> <!-- Use the "active" class for the active menu item  -->
            <li><a href="<?=BASEURL?>/users">USERS</a></li>
        </ul>
        <!-- // #end mainNav -->
        
        <div id="containerHolder">
			<div id="container">
                <!-- h2 stays for breadcrumbs -->
                <h2><a href="#" class="active">Dashboard</a></h2>
                
                <div id="main" style="padding: 100px;">
                </div>
                <!-- // #main -->
                
                <div class="clear"></div>
            </div>
            <!-- // #container -->
        </div>	
        <!-- // #containerHolder -->
        
        <p id="footer">Feel free to use and customize it. <a href="http://www.perspectived.com">Credit is appreciated.</a></p>
    </div>
    <!-- // #wrapper -->
</body>
</html>
