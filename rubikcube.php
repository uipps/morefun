<?php
/*
 顺时针、逆时针如何判断？ 本程序遵从右手习惯，关于M/S/E各网站均不统一。
    -- 除了L层是按照左手的顺逆，其他所有层都按照右手的顺逆（E右手手心向上，顺逆同D）。

 1. 输入转动步骤，得到字符串结果，

    主要规则，通常单字母(U或U1)是顺时针，U2表示旋转180°，U3(Ui或U')是逆时针；一个面的转动也就是这三种情况。  (Ui, inverted:反向的，倒转的)
    https://ruwix.com/the-rubiks-cube/notation/ 有一些介绍，本程序的顺逆只有M不同，其他顺逆都一致。

 php rubikcube.php -d "F R U R' U' F'"
 php rubikcube.php -d "R"

 php rubikcube.php -o "urfdlb" -d "F R U R' U' F'"  -- 指定排序，动作组合



 2. 其他组织方式输出:
 php rubikcube.php -d "" -t pglass -c "{\"u\":\"O\",\"l\":\"Y\",\"f\":\"W\",\"r\":\"G\",\"b\":\"B\",\"d\":\"R\"}"
 -- 得到的就是:OOOOOOOOOYYYWWWGGGBBBYYYWWWGGGBBBYYYWWWGGGBBBRRRRRRRRR

-- type参数，表示输出的结果参数，参考 https://github.com/pglass/cube ; F:\develope\python\game\mofang_rubikcube\cube_pglass_github\
>>> c = Cube("OOOOOOOOOYYYWWWGGGBBBYYYWWWGGGBBBYYYWWWGGGBBBRRRRRRRRR")
>>> print(c)
    OOO
    OOO
    OOO
YYY WWW GGG BBB
YYY WWW GGG BBB
YYY WWW GGG BBB
    RRR
    RRR
    RRR


 3. TODO 输入所要得到的效果（初始状态为6面全好），限定多少步骤完成，用程序跑出所做的操作-多少种都列表出，(大写字母是顺时针，大写字母+'是逆时针)
 php mofang_solve.php -t "" -n 6



 */
//define('clockwise', 'clockwise');           // '顺时针'
//define('anti-clockwise', 'anti-clockwise'); // '逆时针'

include_once 'morefun.php';


$option = getopt('d:b:o:a:k:t:c:g:');
main($option);


function main($o) {
    // 1. 参数检查，
    //   1) action参数，转动动作字母是否在指定动作限定内，不认识的动作，直接删除掉
    $str = (isset($o['d']) && $o['d']) ? $o['d'] : '';
    if (!isset($o['d'])) {
        echo ' -d 必须提供操作动作，如：php rubikcube.php -d "R R\'"' . "\r\n";
        return '';
    }

    //   2) 结果顺序order参数, 主要是结果顺序
    $order_str = (isset($o['o']) && $o['o']) ? $o['o'] : 'urfdlb';

    //
    $order_type = (isset($o['t']) && $o['t']) ? $o['t'] : '';
    $pglass_type = 0;
    if ('pglass' == $order_type)
        $pglass_type = 1;
    $pglass_color = (isset($o['c']) && $o['c']) ? $o['c'] : '{"u":"O","l":"Y","f":"W","r":"G","b":"B","d":"R"}';


    //   3) 初始状态
    $begin_ob = (isset($o['b']) && $o['b']) ? $o['b'] : $GLOBALS['mofun'];
    //   4) 动作别名
    $alias_arr = (isset($o['a']) && $o['a']) ? $o['a'] : [];
    //   5) 是否要空格
    $kongge = (isset($o['k']) && $o['k']) ? $o['k'] : 0;
    //   6) 是否debug
    $GLOBALS['debug'] = (isset($o['g']) && $o['g']) ? $o['g'] : 0;

    // 2. 参数过滤，如果出现了不被识别的动作，过滤掉，并不给出提示。全部变成 F,F2,f
    $action_arr = get_action_by_str($str, $alias_arr);

    // 3. 初始状态：通常默认是完好的 $begin_ob，也可以参数指定


    // 4. 逐个动作执行，也可以是空动作，不进行twist旋转操作
    if ($action_arr) {
        // print_r($action_arr);
        twist_multi($action_arr);
    }

    // 5. 按照order参数指定的顺序，输出各个面各块的颜色。
    $l_str = getRltStr($GLOBALS['mofun'], $order_str, $kongge, $pglass_type, $pglass_color);
    echo $l_str . "\r\n";

    return '';
}
