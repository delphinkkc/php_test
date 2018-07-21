<html>
<body>
<a href="task1.php" target="_blank">Task #1</a><br />
<a href="task2.php" target="_blank">Task #2</a><br />
<h3>User list:</h3>
<?php
require "predis/autoload.php";
require "UserExistsException.php";
Predis\Autoloader::register();

/**
 * @return \Predis\Client
 */
function initialize_redis()
{
    try
    {
        /** @var Predis\Client $redis */
        $redis = new Predis\Client();
        if (!$redis->auth('password'))
        {
            throw new Exception('auth error');
        }
        return $redis;
    }
    catch (Exception $e)
    {
        die($e->getMessage());
    }
}

/**
 * Creates new user
 *
 * ​
 * @param array $user_data
 * User data contains the following fields:
 *                                      - name
 *                                      - email
 *                                      - password_hash
 * @return string
 * Returns ID of created user
 *
 * ​@throws​ \UserExistsException     Throws exception if user with this email already exists
 *
 * @throws UserExistsException
 */
function create_user(array $user_data)
{
    /** @var Predis\Client $redis */
    $redis = initialize_redis();
    $id = $redis->incr('max_user_id');
    $key = $user_data['email'].':'.$user_data['password_hash'];
    if ($redis->exists($key) == 0)
    {
        $redis->hmset($key, array(
            'id' => $id,
            'name' => $user_data['name'],
            'email' => $user_data['email'],
            'password_hash' => $user_data['password_hash'],
        ));
        return ''.$id;
    }
    else
    {
        throw new \UserExistsException();
    }
}

/**
 * Finds user by combination of email and password hash
 *
 * ​@param​ string $email
 * ​@param​ string $password_hash
 *
 * ​@return​ string|null                   Returns ID of user or null if user not found
 */
function authorize_user ($email, $password_hash)
{
    /** @var Predis\Client $redis */
    $redis = initialize_redis();
    $user = $redis->hgetall($email.':'.$password_hash);
    return array_key_exists('id', $user) ? $user['id'] : null;
}

if (isset($_POST['createName']))
{
    try
    {
        create_user(array(
            'name'=>$_POST['createName'],
            'email'=>$_POST['createEmail'],
            'password_hash'=>md5($_POST['createPassword']),
        ));
    }
    catch (Exception $exception)
    {
        die($exception->getMessage());
    }
}

/** @var Predis\Client $redis */
$redis = initialize_redis();
$keys = $redis->keys('*');
unset($keys[array_search('max_user_id', $keys)]);
foreach ($keys as $key)
{
    $user = $redis->hgetall($key);
    echo 'ID: '.$user['id'].' Name: '.$user['name'].' Email: '.$user['email'].' Hash: '.$user['password_hash'].'<br />';
}
?>
<h3>Create user:</h3>
<form action="index.php" method="post">
    Name:<br />
    <input type="text" name="createName"><br />
    Email:<br />
    <input type="text" name="createEmail"><br />
    Password:<br />
    <input type="text" name="createPassword"><br /><br />
    <input type="submit" value="Create">
</form>
<h3>Sign in:</h3>
<form action="index.php" method="post">
    Email:<br />
    <input type="text" name="signinEmail"><br />
    Password:<br />
    <input type="text" name="signinPassword"><br /><br />
    <input type="submit" value="Sign in">
</form>
<h4>Signed as: <?php  if (isset($_POST['signinEmail']))
    {
        $id = authorize_user($_POST['signinEmail'], md5($_POST['signinPassword']));
        if ($id === null)
        {
            echo 'Incorrect user data';
        }
        else
        {
            echo $id;
        }
    }
    ?></h4>
</body>
</html>
