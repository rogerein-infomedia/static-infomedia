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
            <div id="sidebar">
                <ul class="sideNav">
                    <li><a href="<?=BASEURL?>/users">List Users</a></li>
                    <li><a href="<?=BASEURL?>/createRemoteUser" class="active">Create User</a></li>
                </ul>
                <!-- // .sideNav -->
            </div>

            <!-- h2 stays for breadcrumbs -->
            <h2><a href="<?=BASEURL?>/users">Users</a> &raquo; <a class="active"><?=(!isset($_id) ? 'Create' : 'Edit')?></a></h2>

            <div id="main" style="margin-top: 15px;">
                <form action="<?=$actionURL?>" class="jNice" method="post" enctype="multipart/form-data">
                    <?php if(isset($message)):?>
                        <h3><?=$message?></h3>
                    <?php endif;?>
                    <fieldset>
                        <input name="public_key" type="hidden" value="<?=(isset($_id) ? $_id : '')?>" />
                        <p><label>Name:</label><input <?=(isset($errors['name']) ? 'style="border:1px solid red"' : '')?> name="name" type="text" class="text-long" value="<?=(isset($name) ? $name : '')?>" /></p>
                        <p><label>Username:</label><input <?=(isset($errors['username']) ? 'style="border:1px solid red"' : '')?> name="username" type="text" class="text-long" value="<?=(isset($username) ? $username : '')?>" /></p>
                        <p><label>Owner (slug):</label><input <?=(isset($errors['owner']) ? 'style="border:1px solid red"' : '')?> name="owner" type="text" class="text-long" value="<?=(isset($owner) ? $owner : '')?>" /></p>
                        <p><label>Password:</label><input <?=(isset($errors['password']) ? 'style="border:1px solid red"' : '')?> name="password" type="password" class="text-long" value="<?=(isset($password) ? $password : '')?>" /></p>
                        <p><label>Confirm Password:</label><input <?=(isset($errors['confirmPassword']) ? 'style="border:1px solid red"' : '')?> name="confirm-password" type="password" class="text-long" value="<?=(isset($confirmPassword) ? $confirmPassword : '')?>" /></p>
                        <p><label>Default: </label><input <?=(isset($errors['defaultImage']) ? 'style="border:1px solid red"' : '')?> type="file" name="defaultImage"/></p>
                        <p><label>Public Key:</label><span class="text-long"><?=(isset($_id) ? $_id : '----- NOT GENERATED YET -----')?></span></p>
                        <input type="submit" value="save" name="save" />

                        <?php if(isset($defaultImage)) :?>
                            <p>
                                <label>Default: </label><br />
                                <img src="<?=$defaultImage?>" alt="" />
                            </p>
                        <?php endif;?>
                    </fieldset>
                </form>
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
