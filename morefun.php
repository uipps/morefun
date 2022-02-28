<?php
/*
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


-- 补充:
  顺时针、逆时针如何判断？本程序遵从右手习惯，关于M/S/E各网站均不统一。
      -- 除了L层是按照左手的顺逆，其他所有层都按照右手的顺逆（E右手手心向上，顺逆同D）

  Middle(M):   The layer between L and R | M 左右之间的中间层，右手旋转，同右面顺逆，因从右往左看判断顺时针、逆时针，类似右
  Standing(S): The layer between F and B | S 前后之间的中间层，右手旋转，同前面顺逆，因从前往后看判断顺时针、逆时针，类似前
  Equator(E):  The layer between U and D | E 上下之间的中间层，右手旋转，同下面顺逆，因从下往上看判断顺时针、逆时针，类似下，（水平方向）


 */

// 定义6个面的结果字符串“面”顺序；默认完好的顺序是 上、右、前、下、左、后： UUUUUUUUU RRRRRRRRR FFFFFFFFF DDDDDDDDD LLLLLLLLL BBBBBBBBB
$rlt_str_face_order = ['u', 'r', 'f', 'd', 'l', 'b'];  // 6个面字符串顺序, 就用小写
$rlt_str_face_order = ['d', 'u', 'l', 'f', 'r', 'b'];


// 定义6个面的颜色，通常是：上黄下白，前蓝后绿，左橙右红。用于拼装初始魔方状态
$face_color = [];   // 默认就用U R F D L B 表示颜色好了。
//  上黄-yellow 下白-white 前蓝-blue 后绿-green 左橙-orange 右红-red
//$face_color = ['u' => 'y', 'd' => 'w', 'f' => 'b', 'b' => 'g', 'l' => 'o', 'r' => 'r'];


$play_action9 = array_merge($rlt_str_face_order, ['m','s','e']);  //  除了6个面，还有夹在左右之间的M层M、前后之间的S层、上下之间的水平层E

// 定义逆操作字符, 当前就支持3个:i和'，如：Fi F' F`
$inverse_str = ['i', "'", '`'];

$GLOBALS['debug'] = 0;

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
    // 1. 所转面，中心块没有动；其他8个都有变化。
    $tmp1 = $mofun['f'][0][0];              // 原来 F1 位置
    $mofun['f'][0][0] = $mofun['f'][2][0];  // F1 被 F7 替代
    $mofun['f'][2][0] = $mofun['f'][2][2];  // F7 被 F9 替代
    $mofun['f'][2][2] = $mofun['f'][0][2];  // F9 被 F3 替代
    $mofun['f'][0][2] = $tmp1;              // F3 被 F1 替代

    $tmp2 = $mofun['f'][0][1];              // 原来 F2 位置
    $mofun['f'][0][1] = $mofun['f'][1][0];  // F2 被 F4 替代
    $mofun['f'][1][0] = $mofun['f'][2][1];  // F4 被 F8 替代
    $mofun['f'][2][1] = $mofun['f'][1][2];  // F8 被 F6 替代
    $mofun['f'][1][2] = $tmp2;              // F6 被 F2 替代

    // 2. 其他4个面的变化, 每个面3个小块发生变动
    $tmp2 = $mofun['u'][2][0];              // 原来 U7 位置
    $mofun['u'][2][0] = $mofun['l'][2][2];  // U7 被 L9 替代
    $mofun['l'][2][2] = $mofun['d'][0][2];  // L9 被 D3 替代
    $mofun['d'][0][2] = $mofun['r'][0][0];  // D3 被 R1 替代
    $mofun['r'][0][0] = $tmp2;              // R1 被 U7 替代

    $tmp1 = $mofun['u'][2][1];              // 原来 U8 位置
    $mofun['u'][2][1] = $mofun['l'][1][2];  // U8 被 L6 替代
    $mofun['l'][1][2] = $mofun['d'][0][1];  // L6 被 D2 替代
    $mofun['d'][0][1] = $mofun['r'][1][0];  // D2 被 R4 替代
    $mofun['r'][1][0] = $tmp1;              // R4 被 U8 替代

    $tmp3 = $mofun['u'][2][2];              // 原来 U9 位置
    $mofun['u'][2][2] = $mofun['l'][0][2];  // U9 被 L3 替代
    $mofun['l'][0][2] = $mofun['d'][0][0];  // L3 被 D1 替代
    $mofun['d'][0][0] = $mofun['r'][2][0];  // D1 被 R7 替代
    $mofun['r'][2][0] = $tmp3;              // R7 被 U9 替代
}

//左面顺时针旋转90°
function twist_l() {
    global $mofun;
    // 1. 所转面，中心块没有动；其他8个都有变化。
    $tmp1 = $mofun['l'][0][0];              // 原来 L1 位置
    $mofun['l'][0][0] = $mofun['l'][2][0];  // L1 被 L7 替代
    $mofun['l'][2][0] = $mofun['l'][2][2];  // L7 被 L9 替代
    $mofun['l'][2][2] = $mofun['l'][0][2];  // L9 被 L3 替代
    $mofun['l'][0][0] = $tmp1;              // L3 被 L1 替代

    $tmp2 = $mofun['l'][0][1];              // 原来 L2 位置
    $mofun['l'][0][1] = $mofun['l'][2][0];  // L2 被 L4 替代
    $mofun['l'][2][0] = $mofun['l'][2][1];  // L4 被 L8 替代
    $mofun['l'][2][1] = $mofun['l'][1][2];  // L8 被 L6 替代
    $mofun['l'][1][2] = $tmp2;              // L6 被 L2 替代

    // 2. 其他4个面的变化, 每个面3个小块发生变动
    $tmp1 = $mofun['u'][0][0];              // 原来 U1 位置
    $mofun['u'][0][0] = $mofun['b'][2][2];  // U1 被 B9 替代
    $mofun['b'][2][2] = $mofun['d'][0][0];  // B9 被 D1 替代
    $mofun['d'][0][0] = $mofun['f'][0][0];  // D1 被 F1 替代
    $mofun['f'][0][0] = $tmp1;              // F1 被 U1 替代

    $tmp2 = $mofun['u'][1][0];              // 原来 U4 位置
    $mofun['u'][1][0] = $mofun['b'][1][2];  // U4 被 B6 替代
    $mofun['b'][1][2] = $mofun['d'][1][0];  // B6 被 D4 替代
    $mofun['d'][1][0] = $mofun['f'][1][0];  // D4 被 F4 替代
    $mofun['f'][1][0] = $tmp2;              // F4 被 U4 替代

    $tmp3 = $mofun['u'][2][0];              // 原来 U7 位置
    $mofun['u'][2][0] = $mofun['b'][0][2];  // U7 被 B3 替代
    $mofun['b'][0][2] = $mofun['d'][2][0];  // B3 被 D7 替代
    $mofun['d'][2][0] = $mofun['f'][2][0];  // D7 被 F7 替代
    $mofun['f'][2][0] = $tmp3;              // F7 被 U7 替代
}

//顶面顺时针旋转90°
function twist_u() {
    global $mofun;
    // 1. 所转面，中心块没有动；其他8个都有变化。
    $tmp1 = $mofun['u'][0][0];              // 原来 U1 位置
    $mofun['u'][0][0] = $mofun['u'][2][0];  // U1 被 U7 替代
    $mofun['u'][2][0] = $mofun['u'][2][2];  // U7 被 U9 替代
    $mofun['u'][2][2] = $mofun['u'][0][2];  // U9 被 U3 替代
    $mofun['u'][0][2] = $tmp1;              // U3 被 U1 替代

    $tmp2 = $mofun['u'][0][1];              // 原来 U2 位置
    $mofun['u'][0][1] = $mofun['u'][1][0];  // U2 被 U4 替代
    $mofun['u'][1][0] = $mofun['u'][2][1];  // U4 被 U8 替代
    $mofun['u'][2][1] = $mofun['u'][1][2];  // U8 被 U6 替代
    $mofun['u'][1][2] = $tmp2;              // U6 被 U2 替代

    // 2. 其他4个面的变化, 每个面3个小块发生变动
    $tmp2 = $mofun['f'][0][0];              // 原来 F1 位置
    $mofun['f'][0][0] = $mofun['r'][0][0];  // F1 被 R1 替代
    $mofun['r'][0][0] = $mofun['b'][0][0];  // R1 被 B1 替代
    $mofun['b'][0][0] = $mofun['l'][0][0];  // B1 被 L1 替代
    $mofun['l'][0][0] = $tmp2;              // L1 被 F1 替代

    $tmp1 = $mofun['f'][0][1];              // 原来 F2 位置
    $mofun['f'][0][1] = $mofun['r'][0][1];  // F2 被 R2 替代
    $mofun['r'][0][1] = $mofun['b'][0][1];  // R2 被 B2 替代
    $mofun['b'][0][1] = $mofun['l'][0][1];  // B2 被 L2 替代
    $mofun['l'][0][1] = $tmp1;              // L2 被 F2 替代

    $tmp3 = $mofun['f'][0][2];              // 原来 F3 位置
    $mofun['f'][0][2] = $mofun['r'][0][2];  // F3 被 R3 替代
    $mofun['r'][0][2] = $mofun['b'][0][2];  // R3 被 B3 替代
    $mofun['b'][0][2] = $mofun['l'][0][2];  // B3 被 L3 替代
    $mofun['l'][0][2] = $tmp3;              // L3 被 F3 替代
}

//底面顺时针旋转90°
function twist_d() {
    global $mofun;
    // 1. 所转面，中心块没有动；其他8个都有变化。
    $tmp1 = $mofun['d'][0][0];              // 原来 D1 位置
    $mofun['d'][0][0] = $mofun['d'][2][0];  // D1 被 D7 替代
    $mofun['d'][2][0] = $mofun['d'][2][2];  // D7 被 D9 替代
    $mofun['d'][2][2] = $mofun['d'][0][2];  // D9 被 D3 替代
    $mofun['d'][0][2] = $tmp1;              // D3 被 D1 替代

    $tmp2 = $mofun['d'][0][1];              // 原来 D2 位置
    $mofun['d'][0][1] = $mofun['d'][1][0];  // D2 被 D4 替代
    $mofun['d'][1][0] = $mofun['d'][2][1];  // D4 被 D8 替代
    $mofun['d'][2][1] = $mofun['d'][1][2];  // D8 被 D6 替代
    $mofun['d'][1][2] = $tmp2;              // D6 被 D2 替代

    // 2. 其他4个面的变化, 每个面3个小块发生变动
    $tmp2 = $mofun['f'][2][0];              // 原来 F7 位置
    $mofun['f'][2][0] = $mofun['l'][2][0];  // F7 被 L7 替代
    $mofun['l'][2][0] = $mofun['b'][2][0];  // L7 被 B7 替代
    $mofun['b'][2][0] = $mofun['r'][2][0];  // B7 被 R7 替代
    $mofun['r'][2][0] = $tmp2;              // R7 被 F7 替代

    $tmp1 = $mofun['f'][2][1];              // 原来 F8 位置
    $mofun['f'][2][1] = $mofun['l'][2][1];  // F8 被 L8 替代
    $mofun['l'][2][1] = $mofun['b'][2][1];  // L8 被 B8 替代
    $mofun['b'][2][1] = $mofun['r'][2][1];  // B8 被 R8 替代
    $mofun['r'][2][1] = $tmp1;              // R8 被 F8 替代

    $tmp3 = $mofun['f'][2][2];              // 原来 F9 位置
    $mofun['f'][2][2] = $mofun['l'][2][2];  // F9 被 L9 替代
    $mofun['l'][2][2] = $mofun['b'][2][2];  // L9 被 B9 替代
    $mofun['b'][2][2] = $mofun['r'][2][2];  // B9 被 R9 替代
    $mofun['r'][2][2] = $tmp3;              // R9 被 F9 替代
}


//前后中间层顺时针旋转90°（yz轴，x-0）。M是夹在左右之间。// 从操作的便捷性来说，前后中间层可以用一下，而水平和左右中间层用的很少，后2个先不实现。
//  参考 https://ruwix.com/the-rubiks-cube/notation/ 或 https://github.com/Renovamen/Just-a-Cube
function twist_m() {    // TODO 待验证正确性
    global $mofun;
    // 左右两面均没有变化，只有yz轴12个色块调换位置
    $tmp1 = $mofun['u'][0][1];              // 原来 U2 位置
    $mofun['u'][0][1] = $mofun['f'][0][1];  // U2 被 F2 替代
    $mofun['f'][0][1] = $mofun['d'][0][1];  // F2 被 D2 替代
    $mofun['d'][0][1] = $mofun['b'][2][1];  // D2 被 B8 替代
    $mofun['b'][2][1] = $tmp1;              // B8 被 U2 替代

    $tmp2 = $mofun['u'][1][1];              // 原来 U5 位置
    $mofun['u'][1][1] = $mofun['f'][1][1];  // U5 被 F5 替代
    $mofun['f'][1][1] = $mofun['d'][1][1];  // F5 被 D5 替代
    $mofun['d'][1][1] = $mofun['b'][1][1];  // D5 被 B5 替代
    $mofun['b'][1][1] = $tmp2;              // B5 被 U5 替代

    $tmp3 = $mofun['u'][2][1];              // 原来 U8 位置
    $mofun['u'][2][1] = $mofun['f'][2][1];  // U8 被 F8 替代
    $mofun['f'][2][1] = $mofun['d'][2][1];  // F8 被 D8 替代
    $mofun['d'][2][1] = $mofun['b'][0][1];  // D8 被 B2 替代
    $mofun['b'][0][1] = $tmp3;              // B2 被 U8 替代
}

//左右中间层顺时针旋转90°（xy轴，z-0）S是夹在前后之间。
function twist_s() {    // TODO 待验证正确性
    global $mofun;
    // 前后两面均没有变化，只有xy轴12个色块调换位置
    $tmp1 = $mofun['u'][1][0];              // 原来 U4 位置
    $mofun['u'][1][0] = $mofun['l'][2][1];  // U4 被 L8 替代
    $mofun['l'][2][1] = $mofun['d'][1][2];  // L8 被 D6 替代
    $mofun['d'][1][2] = $mofun['r'][0][1];  // D6 被 R2 替代
    $mofun['r'][0][1] = $tmp1;              // R2 被 U4 替代

    $tmp2 = $mofun['u'][1][1];              // 原来 U5 位置
    $mofun['u'][1][1] = $mofun['l'][1][1];  // U5 被 L5 替代
    $mofun['l'][1][1] = $mofun['d'][1][1];  // L5 被 D5 替代
    $mofun['d'][1][1] = $mofun['r'][1][1];  // D5 被 R5 替代
    $mofun['r'][1][1] = $tmp2;              // R5 被 U5 替代

    $tmp3 = $mofun['u'][1][2];              // 原来 U6 位置
    $mofun['u'][1][2] = $mofun['l'][0][1];  // U6 被 L2 替代
    $mofun['l'][0][1] = $mofun['d'][1][0];  // L2 被 D4 替代
    $mofun['d'][1][0] = $mofun['r'][2][1];  // D4 被 R8 替代
    $mofun['r'][2][1] = $tmp3;              // R8 被 U6 替代
}

//水平中间层顺时针旋转90°（xz轴，y-0）E是夹在上下之间。
function twist_e() {    // TODO 待验证正确性
    global $mofun;
    // 上下两面均没有变化，只有xz轴12个色块调换位置
    $tmp1 = $mofun['f'][1][0];              // 原来 F4 位置
    $mofun['f'][1][0] = $mofun['l'][1][0];  // F4 被 L4 替代
    $mofun['l'][1][0] = $mofun['b'][1][0];  // L4 被 B4 替代
    $mofun['b'][1][2] = $mofun['r'][1][0];  // B4 被 R4 替代
    $mofun['r'][1][0] = $tmp1;              // R4 被 F4 替代

    $tmp2 = $mofun['f'][1][1];              // 原来 F5 位置
    $mofun['f'][1][1] = $mofun['l'][1][1];  // F5 被 L5 替代
    $mofun['l'][1][1] = $mofun['b'][1][1];  // L5 被 B5 替代
    $mofun['b'][1][1] = $mofun['r'][1][1];  // B5 被 R5 替代
    $mofun['r'][1][1] = $tmp2;              // R5 被 F5 替代

    $tmp3 = $mofun['f'][1][2];              // 原来 F6 位置
    $mofun['f'][1][2] = $mofun['l'][1][2];  // F6 被 L6 替代
    $mofun['l'][1][2] = $mofun['b'][1][2];  // L6 被 B6 替代
    $mofun['b'][1][2] = $mofun['r'][1][2];  // B6 被 R6 替代
    $mofun['r'][1][2] = $tmp3;              // R6 被 F6 替代
}



//魔方基本动作函数打包
function twist_one($str) {
    // 获取面

    switch ($str) {
        case 'D':    //d - 底面顺时针旋转90°
            twist_d();
            break;
        case 'd':    //D - 底面逆时针旋转90°
            twist_d();
            twist_d();
            twist_d();
            break;
        case 'U':    //u - 顶面顺时针旋转90°
            twist_u();
            break;
        case 'u':    //U - 顶面逆时针旋转90°
            twist_u();
            twist_u();
            twist_u();
            break;
        case 'L':    //l - 左面顺时针旋转90°
            twist_l();
            break;
        case 'l':    //L - 左面逆时针旋转90°
            twist_l();
            twist_l();
            twist_l();
            break;
        case 'F':    //f - 前面顺时针旋转90°
            twist_f();
            break;
        case 'f':    //F - 前面逆时针旋转90°
            twist_f();
            twist_f();
            twist_f();
            break;
        case 'R':    //r - 右面顺时针旋转90°
            twist_r();
            break;
        case 'r':    //R - 右面逆时针旋转90°
            twist_r();
            twist_r();
            twist_r();
            break;
        case 'B':    //b - 后面顺时针旋转90°
            twist_b();
            break;
        case 'b':    //B - 后面逆时针旋转90°
            twist_b();
            twist_b();
            twist_b();
            break;
        case 'M':    //m - 前后中间层顺时针旋转90°
            twist_m();
            break;
        case 'E':    //e - 水平中间层顺时针旋转90°
            twist_e();
            break;
        case 'S':    //s - 左右中间层顺时针旋转90°
            twist_s();
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
function getRltStr($mofun, $order_str, $kongge=1, $pglass_type=0, $pglass_color='') {
    if ($pglass_type) return getPglassRltStr($mofun, $pglass_color);
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

/*
    字符串顺序不一样
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
 */
function getPglassRltStr($mofun, $pglass_color='') {
    // 固定为上、左、前、右、后、下 。并且是线性从左到右、从上到下拼接到一起
    // $order_str = 'ulfrbd';
    $l_str = '';
    $l_str .= implode($mofun['u'][0]) . implode($mofun['u'][1]) . implode($mofun['u'][2]);  // 上层
    $l_str .= implode($mofun['l'][0]) . implode($mofun['f'][0]) . implode($mofun['r'][0]) . implode($mofun['b'][0]);
    $l_str .= implode($mofun['l'][1]) . implode($mofun['f'][1]) . implode($mofun['r'][1]) . implode($mofun['b'][1]);
    $l_str .= implode($mofun['l'][2]) . implode($mofun['f'][2]) . implode($mofun['r'][2]) . implode($mofun['b'][2]);
    $l_str .= implode($mofun['d'][0]) . implode($mofun['d'][1]) . implode($mofun['d'][2]);  // 下层

    if ($pglass_color) { // TODO {"r":"G","d":"R"}// 这样的必须先替换r，后替换d. 以后有时间了再折腾吧。
        $orig_pglass_color = $pglass_color;
        $big_pglass_color = strtoupper($orig_pglass_color);    // 键值都转成大写
        // 进行颜色字符串替换
        $pglass_color = json_decode($pglass_color, true);
        $big_pglass_color = json_decode($big_pglass_color, true);
        if ($pglass_color) {
            $l_str = str_replace(array_keys($pglass_color), array_values($pglass_color), $l_str);
            $l_str = str_replace(array_keys($big_pglass_color), array_values($big_pglass_color), $l_str);   // 大写key也替换一次
        }
    }
    return $l_str;
}

// 通过字符串，分离出动作数组，目前支持常见的单字母和'或数字1/2/3的组合方式。有无空格均可
//    顺时针1圈： 90°，本程序用单个大写字母表示顺时针(U,U1,u,u1)，小写字母表示逆时针；
//              有些网站，小写字母表示顺时针、大写字母表示逆时针；本程序用参数进行兼容
//              常见表示方法：U,U1(数字1通常省略)
//    顺或逆2圈：180°，顺时针或逆时针180°效果一样，本程序用数字下标2表示旋转180°，如U2，F2，也有网站写成2个字母 UU FF，本程序将同字母的多次操作进行合并，算作一次操作。
//              常见表示方法：RR,R2
//    顺时针3圈：270°，也就是逆时针1圈，U3(Ui或U')是逆时针；一个面的转动也就是这三种情况
//              常见表示方法：FFF,F3,Fi,F',F`,f

function get_action_by_str($act_str, $alias_act=[], $type=0) {
    // F F1 FF F2 FFF F3 Fi F' F` f 常见旋转动作格式，
    // 统一转换为: U,U2,u 这三种情况（分别对应：转1圈、转2圈、转3圈(逆时针1圈)，有时候需要把小写u转成U'都表示逆时针1圈）
    // 共9个面(6面+3中间)，每个面3种转动，共27种转动动作。
    $l_arr = [];

    if (!$act_str) return [];   // 不操作直接返回

    $orig_str = $act_str;   // 备份原字符串
    echo date('Y-m-d H:i:s') . ' 需要执行的动作：' . $orig_str . "\r\n";

    // 如果是type=2:小写表示顺时针，大写字母表示逆时针; 跟本程序恰好相反，只需要对大小写字母转换一下即可。
    if (2 == $type) {
        // type=2:小写表示顺时针，大写字母表示逆时针;
        $act_str = lowUpStrToggle($act_str);

        echo date('Y-m-d H:i:s') . '   小写表示顺时针，大写字母表示逆时针，因此需要转换一下。' . "\r\n";
        echo date('Y-m-d H:i:s') . '   转换后的动作：' . $act_str . "\r\n";
    }

    // $act_str无论有无空格，统一处理成没有空格的字符串
    $act_str = str_replace(' ', '', $act_str);

    // 数字和逆操作符号转换成多个前置字符或大小写互换
    $act_str = replace_num_inverse($act_str);   // 已经没有特殊字符和数字了，也没有不存在于9个操作中的动作了和其他符号了。

    // 去掉一些无用的操作：转4圈、一正一反等等这些；简化一些操作：转3圈等于逆时针一圈。
    $act_str = reduce_action($act_str);
    // 实在不放心，可以再执行一次；确保没有3个相同字母相连的情况出现
    $act_str = reduce_action($act_str); // php rubikcube.php -d "UUUuuUu" -g 1  所以还是需要再执行此步骤
    if ($GLOBALS['debug']) echo '  经过压缩后：' . $act_str . "\r\n";

    // 经过上面处理后，顶多2个相同字母大写字母相连,小写字母顶多2个字符

    // 统一转换为：U,U2,u 这三种情况吧
    $l_arr = statStrLength($act_str);

    return $l_arr;
}

//压缩指令数：1. 旋转4圈都相当于没有旋转； 2. 一正一反相当于没有旋转；3. 顺时针旋转3圈相当于逆时针旋转一圈;
//  $min已经处理成全部9个动作内的字符了，无数字和特殊字符。
function reduce_action($min) {
    $l_arr = [
        'uuuu','dddd','llll','ffff','rrrr','bbbb','mmmm','ssss','eeee',
        'UUUU','DDDD','LLLL','FFFF','RRRR','BBBB','MMMM','SSSS','EEEE'
    ];
    $min = str_replace($l_arr, '', $min); // 转4圈就回到原来状态，等于没操作

    // 顺时针3圈等于逆时针1圈；逆时针3圈等于顺时针1圈；
    $l_arr = [
        'uuu'   => 'U',
        'ddd'   => 'D',
        'lll'   => 'L',
        'fff'   => 'F',
        'rrr'   => 'R',
        'bbb'   => 'B',
        'mmm'   => 'M',
        'sss'   => 'S',
        'eee'   => 'E',
    ];
    $min = str_replace(array_keys($l_arr), array_values($l_arr), $min);
    // ff这种2个小写字母将被替换成2个大写字母，都表示旋转180°，效果一样。
    $l_arr = [
        'uu'   => 'UU',
        'dd'   => 'DD',
        'll'   => 'LL',
        'ff'   => 'FF',
        'rr'   => 'RR',
        'bb'   => 'BB',
        'mm'   => 'MM',
        'ss'   => 'SS',
        'ee'   => 'EE',
    ];
    $min = str_replace(array_keys($l_arr), array_values($l_arr), $min);

    $l_arr1 = [
        'UUU'   => 'u',
        'DDD'   => 'd',
        'LLL'   => 'l',
        'FFF'   => 'f',
        'RRR'   => 'r',
        'BBB'   => 'b',
        'MMM'   => 'm',
        'SSS'   => 's',
        'EEE'   => 'e',
    ];
    $min = str_replace(array_keys($l_arr1), array_values($l_arr1), $min);

    // 一正一反回到原来状态，等于没操作
    $min = str_replace(['uU','Uu','dD','Dd','lL','Ll','fF','Ff','rR','Rr','bB','Bb','Mm','mM','Ss','sS','Ee','eE'], '', $min);

    ////////// 经过上面的替换之后，已经不存在2个或超2个相同小写字母的片段，顶多1个小写字母的情况，大写字母最多只有2个相连的情况。

    return $min;
}

// 不同形式表示的动作进行统一为动作加数字，常见旋转动作格式: F F1 FF F2 FFF F3 Fi F' f f1 ff f2 fff f3 fi f'
function replace_num_inverse($str) {
    $orig_stt = $str;

    $l_act_str = implode('', $GLOBALS['play_action9']);

    // 将双字符操作 Fi F' F` F0 F1 F2 F3 这些有特殊操作符号的替换为多个前置字母，
    //   1. 先处理数字，后处理逆操作字符。将数字转换为多个前置字符，便于后面的字符串替换动作
    //      所有超过10的数字都需要转成4以内的数字，一个面不能一次转10+圈，没意义 // php rubikcube.php -d "U12 b2 F8 l20 l10"
    //preg_match_all('/([urfdlbmse](\d+))/i', $str, $matches);
    preg_match_all('/(['. $l_act_str . '](\d+))/i', $str, $matches);
    // 将超过4的数，全部替换为对4取模的数
    if ($matches[1]) {
        foreach ($matches[1] as $key => $l_str_num) {
            $l_num = $matches[2][$key]; // 数字
            //$l_mod = -1;
            if ($l_num >= 4) {
                $l_num = $l_num % 4;
            }
            if (0 == $l_num) {
                $str = str_replace($l_str_num, '', $str);   // 如果数字是0，说明不需要此操作
            } else if (1 == $l_num) {
                $str = str_replace($l_str_num, substr($l_str_num,0,1), $str);
            } else if (2 == $l_num) {
                $letter = substr($l_str_num,0,1);
                $str = str_replace($l_str_num, $letter . $letter, $str);
            } else if (3 == $l_num) {
                $letter = substr($l_str_num,0,1);
                $str = str_replace($l_str_num, $letter . $letter . $letter, $str);
            }
        }
    }
    if ($GLOBALS['debug']) echo '  去掉数字后：' . $str . "\r\n";

    //   2. 处理逆操作字符-双字符，单字符逆操作不用处理
    $l_inverse_str = implode('', $GLOBALS['inverse_str']);
    $l_inverse_str = str_replace("'", "\'", $l_inverse_str);
    preg_match_all('/(['. $l_act_str . '])(['. $l_inverse_str .'])/i', $str, $matches);
    if ($matches[1]) {
        foreach ($matches[1] as $key => $l_str) {
            // $str = str_replace($l_str_num, '', $str);   // 如果数字是0，说明不需要此操作
            if (ctype_upper($l_str)) {
                $str = str_replace($matches[0][$key], strtolower($l_str), $str);
            } else {
                $str = str_replace($matches[0][$key], strtoupper($l_str), $str);
            }
        }
    }
    if ($GLOBALS['debug']) echo '  去掉逆操作符号后：' . $str . "\r\n";

    // 上面替换特殊字符和数字后，就全部变成了单字母，因此去掉那些非正常的动作字符
    preg_match_all('/(['. $l_act_str . '])/i', $str, $matches);
    if ($matches[1]) {
        $l_str = '';
        foreach ($matches[1] as $l_letter) {
            $l_str .= $l_letter;
        }
        $str = $l_str;
    }
    if ($GLOBALS['debug']) echo '  经过去除数字和特殊字符处理后：' . $str . "\r\n";

    return $str;
}

// 大小写字母切换，不是全部替换为大写或小写，而是大写变小写，小写变大写
function lowUpStrToggle($str) {
    $l_arr = str_split($str);

    // $text = '2';var_dump(ctype_alpha($text));var_dump(ctype_lower($text));var_dump(ctype_upper($text)); 数字均是false
    // ctype_alpha($text) 等同于 (ctype_upper($text) || ctype_lower($text))

    // 逐个检查字符串，碰到字母类型的字符，将大写转小写，小写转大写
    $l_rlt = '';
    foreach ($l_arr as $text) {
        if (ctype_upper($text)) {
            $text = strtolower($text);
        } else if (ctype_lower($text)) {
            $text = strtoupper($text);
        }
        $l_rlt .= $text;
    }
    return $l_rlt;
}

// 逆时针表示法小写字母替换成大写字母加上'
function replaceLowerStrTO($min) {
    $l_arr = [
        'u'   => "U'",
        'd'   => "D'",
        'l'   => "L'",
        'f'   => "F'",
        'r'   => "R'",
        'b'   => "B'",
        'm'   => "M'",
        's'   => "S'",
        'e'   => "E'",
    ];
    $min = str_replace(array_keys($l_arr), array_values($l_arr), $min);
    return $min;
}

// 统计字符串中各个字母连续出现的次数，并记录到数组，如果出现小写字母超过2个连续，大写字母超过3个连续的需要报错并退出。
function statStrLength($str) {
    $l_arr = str_split($str);
    $l_rlt = [];

    $last_str = '';
    $last_num = 0;
    foreach ($l_arr as $key => $l_val) {
        if ($last_str != $l_val) {
            if ($key > 0) {
                // 记录上一个字母及其连续相连出现的个数
                if ($last_num > 1)
                    $l_rlt[] = $last_str . $last_num;
                else
                    $l_rlt[] = $last_str;
            }

            // 下一个新字母开始
            $last_str = $l_val;
            $last_num = 1;  // 重新开始计数
        } else {
            // 数量加1
            $last_num++;
        }
    }
    // 最后一个字母需要记录
    if ($last_str) {
        if ($last_num > 1)
            $l_rlt[] = $last_str . $last_num;
        else
            $l_rlt[] = $last_str;
    }

    return $l_rlt;
}