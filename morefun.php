<?php
/**
 6个面顺序参考： F:/develope/python/game/mofang_rubikcube/rubiksCube_AlphaZero 的enums.py中的图案,
     (git@github.com:uipps/AI_AlphaZero.git 的 rubiksCube分支)

The names of the facelet positions of the cube (letters stand for Up, Left, Front, Right, Back, and Down):
             |************|
             |*U1**U2**U3*|
             |************|
             |*U4**U5**U6*|
             |************|
             |*U7**U8**U9*|
             |************|
|************|************|************|************|
|*L1**L2**L3*|*F1**F2**F3*|*R1**R2**R3*|*B1**B2**B3*|
|************|************|************|************|
|*L4**L5**L6*|*F4**F5**F6*|*R4**R5**R6*|*B4**B5**B6*|
|************|************|************|************|
|*L7**L8**L9*|*F7**F8**F9*|*R7**R8**R9*|*B7**B8**B9*|
|************|************|************|************|
             |************|
             |*D1**D2**D3*|
             |************|
             |*D4**D5**D6*|
             |************|
             |*D7**D8**D9*|
             |************|


 */

// 定义6个面的结果字符串“面”顺序；默认完好的顺序是 上、右、前、下、左、后： UUUUUUUUU RRRRRRRRR FFFFFFFFF DDDDDDDDD LLLLLLLLL BBBBBBBBB
$rlt_str_face_order = ['u', 'r', 'f', 'd', 'l', 'b'];  // 6个面字符串顺序, 就用小写
$rlt_str_face_order = ['d', 'u', 'l', 'f', 'r', 'b'];


// 定义6个面的颜色，通常是：上黄下白，前蓝后绿，左橙右红。用于拼装初始魔方状态
$face_color = [];   // 默认就用U R F D L B 表示颜色好了。
//  上黄-yellow 下白-white 前蓝-blue 后绿-green 左橙-orange 右红-red
//$face_color = ['u' => 'y', 'd' => 'w', 'f' => 'b', 'b' => 'g', 'l' => 'o', 'r' => 'r'];


$play_action9 = array_merge($rlt_str_face_order, []);  // 'm','s','e' 除了6个面，还有前后转动的中层M、左右转动的中层S、水平转动的中层E

// 魔方对象，共6面，每面9块，共有54块，存储魔方各面颜色状态。初始就用
$mofun = init_morefun($rlt_str_face_order, $face_color);    // 共26个元素，程序生成的跟上面一样，只是bl组合的顺序不一样。

// 初始魔方状态, 三维数组
function init_morefun($face6_arr, $face_color = []) {
    $l_arr = [];
    // 6个面
    foreach ($face6_arr as $letter_1) {
        $l_color = [];
        $one_c = isset($face_color[$letter_1]) ? $face_color[$letter_1] : strtoupper($letter_1);
        $l_color[] = [$one_c, $one_c, $one_c];  // 第1行3个元素
        $l_color[] = [$one_c, $one_c, $one_c];  // 第2行3个元素
        $l_color[] = [$one_c, $one_c, $one_c];  // 第3行3个元素
        $l_arr[$letter_1] = $l_color; // 每个面的3X3=9个元素的颜色数组
    }
    return $l_arr;
}

/**************************** 需要用到的方法 ******************************/
// 定义排序函数，用于字符串排序
function compareStr($a, $b) {
    $a = trim($a);
    $b = trim($b);
    $face_sort = $GLOBALS['rlt_str_face_order']; // 字母必须在这些指定的字母中
    $face_flip = array_flip($face_sort);         // 键值互换
    if (!in_array($a, $face_sort) || !in_array($b, $face_sort)) {
        exit($a . ' or ' . $b . ' not in face_sort!');
    }
    // if ($face_flip[$a] == $face_flip[$b]) return 0; 此行可以去掉
    return ($face_flip[$a] > $face_flip[$b]) ? 1 : -1;  // 升序:由小到大
    //return ($face_flip[$a] > $face_flip[$b]) ? -1 : 1;  // 这是降序:由大到小
}

// 对字符串排序，例如角块Key的字母顺序。可应用于二维数组
function getSort($str) {
    $arr = str_split($str); // 字符串映射为一维数组
    uasort($arr, 'compareStr'); // 排序后
    $str = implode('', $arr);   // 转成字符串
    return $str;
}


/**************************** 魔方基本动作 ******************************/
// 代码参考 F:\develope\javascript\game_\mofang_rubikcube\Just-a-Cube_Renovamen\cube\js\lbl.js 中的"模拟魔方按解法转动后的状态变化"部分

//除了3个中间层，其他6个层每层的旋转都是4个棱块和4个角块替换
//右面顺时针旋转90°，只需要三维数组的相关节点的替换表示出来即可。
function twist_r() {
    global $mofun;
    // 涉及到5个大面-20个小颜色面块的变化，除了对面(l左面没有变化)，每个面都需要进行一些变换。
    // 1. 4个面的变化
    $tmp1 = $mofun['u'][0][2];              // 就是原来U3位置，将其颜色先放到临时变量中
    $mofun['u'][0][2] = $mofun['f'][0][2];  // U3被F3替代
    $mofun['f'][0][2] = $mofun['d'][0][2];  // F3被D3替代
    $mofun['d'][0][2] = $mofun['b'][2][0];  // D3被B7替代
    $mofun['b'][2][0] = $tmp1;              // B7被U3替代

    $tmp2 = $mofun['u'][1][2];              // 就是原来U6位置
    $mofun['u'][1][2] = $mofun['f'][1][2];  // U6被F6替代
    $mofun['f'][1][2] = $mofun['d'][1][2];  // F6被D6替代
    $mofun['d'][1][2] = $mofun['b'][1][0];  // D6被B4替代
    $mofun['b'][1][0] = $tmp2;              // B4被U6替代

    $tmp3 = $mofun['u'][2][2];              // 就是原来U9位置
    $mofun['u'][2][2] = $mofun['f'][2][2];  // U9被F9替代
    $mofun['f'][2][2] = $mofun['d'][2][2];  // F9被D9替代
    $mofun['d'][2][2] = $mofun['b'][0][0];  // D6被B1替代
    $mofun['b'][0][0] = $tmp3;              // B1被U9替代


    // 2. 所在的侧（右），中心块没有动；其他8个都有变化。
    $tmp1 = $mofun['r'][0][0];              // 就是原来R1位置
    $mofun['r'][0][0] = $mofun['r'][2][0];  // R1被R7替代
    $mofun['r'][2][0] = $mofun['r'][2][2];  // R7被R9替代
    $mofun['r'][2][2] = $mofun['r'][0][2];  // R9被R3替代
    $mofun['r'][0][0] = $tmp1;              // R3被R1替代

    $tmp2 = $mofun['r'][0][1];              // 就是原来R2位置
    $mofun['r'][0][1] = $mofun['r'][2][0];  // R2被R4替代
    $mofun['r'][2][0] = $mofun['r'][2][1];  // R4被R8替代
    $mofun['r'][2][1] = $mofun['r'][1][2];  // R8被R6替代
    $mofun['r'][1][2] = $tmp2;              // R6被R2替代
}

//后面顺时针旋转90°,
function twist_b() {
    global $mofun;
    // 涉及到5个大面-20个小颜色面块的变化，除了对面(f前面没有变化)，每个面都需要进行一些变换。
    // 1. 所转面（后），中心块没有动；其他8个都有变化。
    $tmp1 = $mofun['b'][0][0];              // 原来 B1 位置
    $mofun['b'][0][0] = $mofun['b'][2][0];  // B1 被 B7 替代
    $mofun['b'][2][0] = $mofun['b'][2][2];  // B7 被 B9 替代
    $mofun['b'][2][2] = $mofun['b'][0][2];  // B9 被 B3 替代
    $mofun['b'][0][2] = $tmp1;              // B3 被 B1 替代

    $tmp2 = $mofun['b'][0][1];              // 原来 B2 位置
    $mofun['b'][0][1] = $mofun['b'][1][0];  // B2 被 B4 替代
    $mofun['b'][1][0] = $mofun['b'][2][1];  // B4 被 B8 替代
    $mofun['b'][2][1] = $mofun['b'][1][2];  // B8 被 B6 替代
    $mofun['b'][1][2] = $tmp2;              // B6 被 B2 替代

    // 2. 其他4个面的变化, 每个面3个小块发生变动
    $tmp2 = $mofun['u'][0][0];              // 原来 U1 位置
    $mofun['u'][0][0] = $mofun['r'][0][2];  // U1 被 R3 替代
    $mofun['r'][0][2] = $mofun['d'][2][2];  // R3 被 D9 替代
    $mofun['d'][2][2] = $mofun['l'][2][0];  // D9 被 L7 替代
    $mofun['l'][2][0] = $tmp2;              // L7 被 U1 替代

    $tmp3 = $mofun['u'][0][1];              // 原来 U2 位置
    $mofun['u'][0][1] = $mofun['r'][1][2];  // U2 被 R6 替代
    $mofun['r'][1][2] = $mofun['d'][2][1];  // R6 被 D8 替代
    $mofun['d'][2][1] = $mofun['l'][1][0];  // D8 被 L4 替代
    $mofun['l'][1][0] = $tmp3;              // L4 被 U2 替代

    $tmp1 = $mofun['u'][0][2];              // 原来 U3 位置
    $mofun['u'][0][2] = $mofun['r'][2][2];  // U3 被 R9 替代
    $mofun['r'][2][2] = $mofun['d'][2][0];  // R9 被 D7 替代
    $mofun['d'][2][0] = $mofun['l'][0][0];  // D7 被 L1 替代
    $mofun['l'][0][0] = $tmp1;              // L1 被 U3 替代
}

//前面顺时针旋转90°
function twist_f() {
    global $mofun;
}

//左面顺时针旋转90°
function twist_l() {
    global $mofun;

}

//顶面顺时针旋转90°
function twist_u() {
    global $mofun;
    //
}

//底面顺时针旋转90°
function twist_d() {
    global $mofun;
    //
}

//左右中间层顺时针旋转90°（xy轴，z-0）
function twist_s() {
    global $mofun;
    //
}

//水平中间层顺时针旋转90°（xz轴，y-0）
function twist_e() {
    global $mofun;
    //
}

//前后中间层顺时针旋转90°（yz轴，x-0）
function twist_m() {
    global $mofun;
    //
}



//魔方基本动作函数打包
function twist_one($str) {
    switch ($str) {
        case 'd':    //d - 底面顺时针旋转90°
            twist_d();
            break;
        case 'D':    //D - 底面逆时针旋转90°
            twist_d();
            twist_d();
            twist_d();
            break;
        case 'u':    //u - 顶面顺时针旋转90°
            twist_u();
            break;
        case 'U':    //U - 顶面逆时针旋转90°
            twist_u();
            twist_u();
            twist_u();
            break;
        case 'l':    //l - 左面顺时针旋转90°
            twist_l();
            break;
        case 'L':    //L - 左面逆时针旋转90°
            twist_l();
            twist_l();
            twist_l();
            break;
        case 'f':    //f - 前面顺时针旋转90°
            twist_f();
            break;
        case 'F':    //F - 前面逆时针旋转90°
            twist_f();
            twist_f();
            twist_f();
            break;
        case 'r':    //r - 右面顺时针旋转90°
            twist_r();
            break;
        case 'R':    //R - 右面逆时针旋转90°
            twist_r();
            twist_r();
            twist_r();
            break;
        case 'b':    //b - 后面顺时针旋转90°
            twist_b();
            break;
        case 'B':    //B - 后面逆时针旋转90°
            twist_b();
            twist_b();
            twist_b();
            break;
    }
}

//魔方组合动作
function twist_multi($com) {
    for ($i = 0; $i < count($com); $i++) {
        twist_one($com[$i]);
    }
}

//输出魔方状态
function out() {
    return $GLOBALS['mofun'];
}


// 获取结果颜色字符串，各面按照order_str指定顺序，默认完好的顺序是 上、右、前、下、左、后： UUUUUUUUU RRRRRRRRR FFFFFFFFF DDDDDDDDD LLLLLLLLL BBBBBBBBB
function getRltStr($mofun, $order_str, $kongge=1) {
    $order_arr = str_split($order_str);

    $color_face = [];                               // 一维数组
    foreach ($order_arr as $letter_face) {
        $letter_face = strtolower($letter_face);    // 小写; $mofun 索引都是小写字母
        $l_color = '';

        $l_color .= implode($mofun[$letter_face][0]);   // 第1行3个元素
        $l_color .= implode($mofun[$letter_face][1]);   // 第2行3个元素
        $l_color .= implode($mofun[$letter_face][2]);   // 第3行3个元素
        $color_face[$letter_face] = $l_color; // 每个面的3X3=9个元素的颜色数组
    }
    $l_str = implode(($kongge?' ':''), $color_face);
    return $l_str;
}


// 通过字符串，分离出动作数组，目前支持常见的单字母和'或数字1/2/3的组合方式。有无空格均可
function get_action_by_str($act_str, $alias_act=[], $type=0) {
    // F R U R' U' F' U3 U2 D1 等常见格式。
    $l_arr = [];

    if (!$act_str) return [];   // 不操作直接返回

    //   type=2:小写表示顺时针，大写字母表示逆时针;
    //   type=3:小写表示逆时针，大写字母表示顺时针;
    if (2 == $type) {
        // TODO
        // type=2:小写表示顺时针，大写字母表示逆时针;
        return $l_arr;
    }
    if (3 == $type) {
        // TODO
        // type=3:小写表示逆时针，大写字母表示顺时针;
        return $l_arr;
    }

    // 大部分地方魔方操作都没有采用大小写进行顺逆的表示。这里写一下大众情况
    // 1. 有空格的情况，简单一点
    if (false !== strpos($act_str, ' ')) {
        $tmp_arr = explode(' ', $act_str);  // 逐行读取到数组
        $l_arr = array_filter($tmp_arr);    // 过滤空元素
        // TODO 参数过滤，如果出现了不被识别的动作，过滤掉，并不给出提示。
        return $l_arr;
    }

    // 无空格分隔的情况下, TODO
    $tmp_arr = str_split($act_str); // 逐行读取到数组
    $l_arr = $tmp_arr;
    return $l_arr;
}
