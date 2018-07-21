<pre>
<?php

function render_strings(array $words, $count)
{
    $result = [];
    for($i = 0; $i < $count; $i++)
    {
        shuffle($words);
        $result[] = implode(' ', $words);
    }
    return $result;
}

function insert_docs($dbh, $strings)
{
    /** @var PDOStatement $stmt */
    $stmt = $dbh->prepare('INSERT IGNORE INTO `docs` (`hash`,`text`)VALUES(?,?);');
    $dbh->beginTransaction();
    foreach($strings as $s)
    {
        $hash = md5($s);
        $stmt->execute([$hash, $s]);
    }
    $dbh->commit();
}

function is_line_exists($dbh, $line)
{
    /** @var PDOStatement $stmt */
    $stmt = $dbh->prepare('SELECT count(*) as cnt FROM docs WHERE `hash` = ?;');
    $cnt = 0;
    if ($stmt->execute([md5($line)]))
    {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $cnt = (int)$row['cnt'];
    }
    return $cnt > 0;
}

$dsn = 'mysql:dbname=test_scheme;host=localhost';
$user = 'root';
$password = '123';
$words = ['red', 'green', 'blue', 'yellow', 'orange'];

/*
 CREATE TABLE `docs` (
  `hash` varchar(32) NOT NULL,
  `text` mediumtext,
  PRIMARY KEY (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 */

$strings = render_strings($words, 10*1000*10);

try
{
    /** @var PDO $dbh */
    $dbh = new PDO($dsn, $user, $password);
    $t = microtime(true);
    insert_docs($dbh, $strings);
    $time = (microtime(true) - $t);
    echo "Time: ".$time."\n";
    $test_line = 'red green blue yellow orange';
    echo "Line '$test_line' is ".(is_line_exists($dbh, $test_line) ? '' : 'not').' exists.';

}
catch (PDOException $e)
{
    echo 'Connection failed: ' . $e->getMessage();
}