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
        <li><a href="<?=BASEURL?>/dashboard">DASHBOARD</a></li> <!-- Use the "active" class for the active menu item  -->
        <li><a href="<?=BASEURL?>/users" class="active">USERS</a></li>
    </ul>
    <!-- // #end mainNav -->

    <div id="containerHolder">
        <div id="container">
            <div id="sidebar">
                <ul class="sideNav">
                    <li><a href="<?=BASEURL?>/users" class="active">List Users</a></li>
                    <li><a href="<?=BASEURL?>/createRemoteUser">Create User</a></li>
                </ul>
            </div>
            <!-- // #end mainNav -->


            <!-- h2 stays for breadcrumbs -->
            <h2><a href="<?=BASEURL?>/users">Users</a> &raquo; <a class="active">List</a></h2>

            <div id="main">
                <form class="jNice" action="" style="padding-top: 50px;padding-bottom: 50px;">
                    <table cellspacing="0" cellpadding="0">
                        <tbody>
                        <?php foreach($users as $i => $user) :?>
                            <tr<?=($i%2 != 0 ? ' class="odd"' : '')?>>
                                <td><?=$user['name']?></td>
                                <td class="action">
                                    <a class="edit" href="<?=BASEURL?>/saveRemoteUser/<?=$user['_id']?>">Edit</a>
                                    <a class="delete" href="<?=BASEURL?>/deleteRemoteUser/<?=$user['_id']?>">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach;?>
                        </tbody>
                    </table>
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
