<?php
/*
 顺时针、逆时针如何判断？本程序遵从右手习惯，关于M/S/E各网站均不统一。
    -- 除了L层是按照左手的顺逆，其他所有层都按照右手的顺逆（E右手手心向上，顺逆同D）

 输入所要得到的效果（初始状态为6面全好），限定多少步骤完成，用程序跑出所做的操作-多少种都列表出，(小写字母是顺时针，大写字母是逆时针)
 php mofang_solve.php -t "" -n 6


 通过图形获取字符串
 php rubikcube.php -r ""

 */

include_once 'morenfun.cls.php';

$o = getopt('a:');
main($o);

function main($o) {
    return 1;
}

