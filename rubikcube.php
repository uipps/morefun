<?php
/*
 输入转动步骤，得到字符串结果，(小写字母是顺时针，大写字母是逆时针)
 php rubikcube.php -a "f r u R U F"

 php rubikcube.php -o "urfdlb" -a "f r u R U F"  -- 指定排序，动作组合

 */
include_once 'morenfun.cls.php';

$option = getopt('a:b:o:');




main($option);

function main($o)
{
    // 1. 参数检查，
    //   1) action参数，转动动作字母是否在指定动作限定内，不认识的动作，直接删除掉
    $str = (isset($o['a']) && $o['a']) ? $o['a'] : '';
    if (!$str) {
        echo ' -a 必须提供操作动作，如：php rubikcube.php -a "f r u R U F"' . "\r\n";
        return '';
    }
    //   2) 结果顺序order参数, 主要是结果顺序
    $order_str = (isset($o['o']) && $o['o']) ? $o['o'] : 'urfdlb';
    //   3) 初始状态
    $begin_ob = (isset($o['b']) && $o['b']) ? $o['b'] : $GLOBALS['ob'];

    // 2. 参数过滤，如果出现了不被识别的动作，过滤掉，并不给出提示。TODO 第一期只允许单字母动作,U2,U3-这种旋转180°，270°(逆时针)放到下期
    $str = str_replace(' ', '', $str);  // 去掉空白字符
    $str = strtolower($str);            // 全部转成小写字母，大小写暂不区分
    $action_arr = str_split($str);
    $action_arr = array_filter($action_arr, 'filter_action');   // 限定动作

    // 3. 初始状态：通常默认是完好的 $begin_ob，也可以参数指定。

    print_r($action_arr);
    // 4. 逐个动作执行
    foreach ($action_arr as $l_act_val) {
        //
    }

    print_r($GLOBALS['m']);


    // 字符串排序
    // $str2 = getSort($str2);


    return '';
}

//print_r($m);
// 按照 $face_sort 中的顺序，并且字母只能在这6个字母
//echo 'before: '. $str . "\r\n";
//$str = str_replace(' ', '', $str);
//$str = strtolower($str);
//echo 'strtolower: '. $str . "\r\n";
//$str = getSort($str);
//echo 'after: '. $str . "\r\n";

// 结果字符串，默认完好的顺序是 上、右、前、下、左、后： UUUUUUUUU RRRRRRRRR FFFFFFFFF DDDDDDDDD LLLLLLLLL BBBBBBBBB
$rlt_str_order_arr = getResultStringFaceOrder();
//$rlt_str_face_order = ['u','r','f','d','l','b'];  // 6个面字符串顺序，也可以改顺序
