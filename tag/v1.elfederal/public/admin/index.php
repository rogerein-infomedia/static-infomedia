<?php
// No modificar, así queda a modo de debug
define('IS_PRODUCTION', false);
define('BASEURL', '/admin');
define('PUBLIC_PATH', dirname(dirname(__FILE__)));

require_once '../../libraries/loader.php';

function configure()
{
    option('limonade_public_dir', dirname(__FILE__) . '/../libraries/routing/limonade/public');
    option('views_dir', './');
    option('env', ENV_DEVELOPMENT);
}

dispatch('/', 'dashboard');
dispatch('/dashboard', 'dashboard');

dispatch('/users', 'listUsers');

dispatch_post('/saveRemoteUser', 'registerUser');
dispatch_post('/saveRemoteUser/:id', 'registerUser');
dispatch('/saveRemoteUser/:id', 'registerUser');
dispatch('/saveRemoteUser', 'registerUser');
run();


function dashboard()
{
    echo render('dashboard.html.php', null, array());
}

function listUsers()
{
    $users = MongoDBWrapper::getMongoDBInstance()->remoteUser->find();
    echo render('users.html.php', null, array('users' => $users));
}

function registerUser($id = null)
{
    $data = array();
    $data['actionURL'] = BASEURL . '/saveRemoteUser';
    if(!is_null($id))
    {
        $user = MongoDBWrapper::getMongoDBInstance()->remoteUser->findOne(array('_id' => new MongoId($id)));
        if($user)
        {
            $data['_id'] = $user['_id']->{'$id'};
            $data['name'] = $user['name'];
            $data['username'] = $user['username'];
            $data['password'] = $user['password'];
            $data['confirmPassword'] = $user['password'];
            $data['owner'] = $user['ownerName'];
            $data['defaultImagePath'] = PUBLIC_PATH . '/assets/' . $data['owner'];
            $data['defaultImage'] = '/assets/' . $data['owner'] . '/default.jpg';
            $data['actionURL'] .= "/$id";
        }
        else
        {
            redirect('/dashboard');
        }
    }

    if(!empty($_POST))
    {
//        move_uploaded_file($_FILES['name'], ASSETS_PATH . '')
        if(isset($_POST['name']))
        {
            $data['name'] = $_POST['name'];
            if(isset($_POST['username']))
            {
                $data['username'] = $_POST['username'];
                if(isset($_POST['password']) && isset($_POST['confirm-password']) && $_POST['password'] == $_POST['confirm-password'])
                {
                    if(isset($_POST['owner']))
                    {
                        $data['owner'] = $_POST['owner'];
                        if(isset($_FILES['defaultImage']))
                        {
                            $defaultDestination = $data['defaultImagePath'];

                            if(!file_exists($defaultDestination))
                                mkdir($defaultDestination, 0777, true);

                            move_uploaded_file($_FILES['defaultImage']['tmp_name'], $defaultDestination . '/default.jpg');

                            JPGImageHandler::compressImage($defaultDestination . '/default.jpg');

                            if(!isset($_POST['public_key']))
                            {
                                $inserted = MongoDBWrapper::getMongoDBInstance()->remoteUser->insert($data);
                                $data['message'] = 'User Created';
                            }
                            else
                            {
                                MongoDBWrapper::getMongoDBInstance()->remoteUser->update(
                                    array('_id' => new MongoId($data['_id'])),
                                    array('$set' => $data)
                                );
                                $data['message'] = 'User Updated';
                            }
                        }
                        else
                        {
                            $data['errors']['defaultImage'] = 1;
                        }
                    }
                    else
                    {
                        $data['errors']['owner'] = 1;
                    }
                }
                else
                {
                    $data['errors']['password'] = 1;
                    $data['errors']['confirmPassword'] = 1;
                }
            }
            else
            {
                $data['errors']['username'] = 1;
            }
        }
        else
        {
            $data['errors']['name'] = 1;
        }
    }

    echo render('saveuser.html.php', null, $data);
}
