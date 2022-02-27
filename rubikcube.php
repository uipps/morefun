<?php
/*
 输入转动步骤，得到字符串结果，
    主要规则，通常单字母(U或U1)是顺时针，U2表示旋转180°，U3(或U')是逆时针；一个面的转动也就是这三种情况。

 php rubikcube.php -d "F R U R' U' F'"
 php rubikcube.php -d "R"

 php rubikcube.php -o "urfdlb" -d "F R U R' U' F'"  -- 指定排序，动作组合

 */
//define('clockwise', 'clockwise');           // '顺时针'
//define('anti-clockwise', 'anti-clockwise'); // '逆时针'

include_once 'morefun.php';

$option = getopt('d:b:o:a:k:');
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
    //   3) 初始状态
    $begin_ob = (isset($o['b']) && $o['b']) ? $o['b'] : $GLOBALS['mofun'];
    //   4) 动作别名
    $alias_arr = (isset($o['a']) && $o['a']) ? $o['a'] : [];
    //   5) 是否要空格
    $kongge = (isset($o['k']) && $o['k']) ? $o['k'] : 0;

    // 2. 参数过滤，如果出现了不被识别的动作，过滤掉，并不给出提示。
    $action_arr = get_action_by_str($str, $alias_arr);

    // 3. 初始状态：通常默认是完好的 $begin_ob，也可以参数指定


    // 4. 逐个动作执行，也可以是空动作，不进行twist旋转操作
    if ($action_arr) {
        // print_r($action_arr);
        twist_multi($action_arr);
    }

    // 5. 按照order参数指定的顺序，输出各个面各块的颜色。
    $l_str = getRltStr($GLOBALS['mofun'], $order_str, $kongge);
    echo $l_str . "\r\n";

    return '';
}
