<?php

/* debug code start. Don't forget to remove */
// if there already is a variable you use as parameter for schemaValidate() use that instead of defining a new one.
$path = '\NF_V3\NF_V3\servicos\reinf\schemas\2_01_01\R-4010-evt4010PagtoBeneficiarioPF-v2_01_01-A.xsd';
foreach (array('file_exists', 'is_readable', 'is_writable') as $fn) {
    echo $fn, ': ', $fn($path) ? 'true' : 'false', "<br />\n";
}
$foo = stat($path);
echo 'mode: ', $foo['mode'], "<br />\n";
echo 'uid: ', $foo['uid'], "<br />\n";
echo 'gid: ', $foo['gid'], "<br />\n";
if (function_exists('getmyuid')) {
    echo 'myuid: ', getmyuid(), "<br />\n";
}
if (function_exists('getmygid')) {
    echo 'myuid: ', getmygid(), "<br />\n";
}

$foo = fopen($path, 'rb');
if ($foo) {
    echo 'fopen succeeded';
    fclose($foo);
} else {
    echo 'fopen failed';
}
/*  debug code end */