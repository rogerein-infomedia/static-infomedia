<html>
<body>
    <h2>Delete Images from:</h2>
    <ul>
        <?php foreach($users as $user) :?>
            <li><a href=""><?=$user['name']?></a></li>
        <?php endforeach;?>
    </ul>
</body>
</html>