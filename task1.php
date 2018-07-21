<pre>
<?php
$memStart = memory_get_usage(true);
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

function get_uiniques(array $strings)
{
    return array_keys(array_flip($strings));
}

$words = ['red', 'green', 'blue', 'yellow', 'orange'];
$t = microtime(true);
$strings = render_strings($words, 10000000);
echo "T = ".(microtime(true) - $t)."\n";

$t = microtime(true);
$uniques = get_uiniques($strings);
echo "T = ".(microtime(true) - $t)."\n";
print_r($uniques);