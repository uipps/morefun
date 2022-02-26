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
$rlt_str_face_order = ['u','r','f','d','l','b'];  // 6个面字符串顺序, 就用小写
$rlt_str_face_order = ['d','u','l','f','r','b'];


// 定义6个面的颜色，通常是：上黄下白，前蓝后绿，左橙右红。用于拼装初始魔方状态
//  上黄-yellow 下白-white 前蓝-blue 后绿-green 左橙-orange 右红-red
$face_color = ['u' => 'y', 'd' => 'w', 'f' => 'b', 'b' => 'g', 'l' => 'o', 'r' => 'r'];

// 魔方颜色，单字母同颜色映射表, TODO 这里暂时用不到，在html页面展示可能会用到
//$colors = ['r'=>'red','g'=>'green','y'=>'yellow','o'=>'orange','b'=>'blue','w'=>'white'];


// 定义6个面，顺序：下、上、左、前、右、后。通常人们都是先完成下层，再完成上层，所以下层放在前面 [就用上面即可，废弃下面的定义]
//$face6_sort = ['d','u','l','f','r','b'];  // 6个面
//$face6_flip = array_flip($face6_sort);    // 反转用于排序

$play_action9 = array_merge($rlt_str_face_order, []);  // 'm','s','e' 除了6个面，还有前后转动的中层M、左右转动的中层S、水平转动的中层E

// 魔方对象，共26块 = 6(个中心快) + 12(棱块) + 8(角块) ，存储魔方的状态。
//$m = [
//    'd'=>   'w',   'u'=>   'y',   'l'=>   'o',   'f'=>   'b',   'r'=>   'r',   'b'=>   'g', // 6个中心块， 下白上黄，前蓝后绿，左橙右红;名称及其对应颜色；
//    'dl'=>  'wo',  'df'=>  'wb',  'dr'=>  'wr',  'db'=>  'wg',  // 12个中间棱块之下层交界处，4个；名称及其对应颜色；
//    'ul'=>  'yo',  'uf'=>  'yb',  'ur'=>  'yr',  'ub'=>  'yg',  // 12个中间棱块之上层交界处，4个；
//    'lf'=>  'ob',  'fr'=>  'br',  'rb'=>  'rg',  'lb'=>  'go',  // 12个中间棱块之中层交界处，4个；
//    'dlf'=> 'wob', 'dfr'=> 'wbr', 'drb'=> 'wrg', 'dlb'=> 'wgo', // 8个角块之下层，4个；名称及其对应颜色；
//    'ulf'=> 'yob', 'ufr'=> 'ybr', 'urb'=> 'yrg', 'ulb'=> 'ygo'  // 8个角块之上层，4个；
//];
$m = init_morefun($rlt_str_face_order, $face_color);    // 共26个元素，程序生成的跟上面一样，只是bl组合的顺序不一样。


// 初始魔方状态
function init_morefun($face6_arr, $face_color) {
    $l_rlt = [];    // 参照$m
    // 6个中心块
    foreach ($face6_arr as $letter_1) {
        $l_rlt[$letter_1] = $face_color[$letter_1]; // 6个中心块
    }

    // 12个棱块，按照上中下层; key为两个字母的组合，总共12种组合C6^2还要排除3对对面颜色不可能组合在一起：ud、lr、fb，
    $num = count($face6_arr);
    for ($i = 0; $i < $num; $i++) {
        for ($j = $i+1; $j < $num; $j++) { // 排列组合，不分前后，所以可以$j = $i+1;
            // 需要排除掉上下、前后、左右这三种情况ud、lr、fb，也就是说，有u就不能有d；同理有l就不能有r；有f就不能有b;
            $key = $face6_arr[$i] . $face6_arr[$j]; // 棱块key名称需要固定一个顺序，例如ul和lu其实都是指左边和上面交界棱块。
            if (false !== strpos($key, 'u') && false !== strpos($key, 'd')) continue;
            if (false !== strpos($key, 'f') && false !== strpos($key, 'b')) continue;
            if (false !== strpos($key, 'l') && false !== strpos($key, 'r')) continue;

            $key = getSort($key);    // 返回稳定的排序，确保key唯一
            if (isset($l_rlt[$key]))
                continue;
            $l_rlt[$key] = $face_color[$key[0]] . $face_color[$key[1]]; // 获取对应颜色字符串
        }
    }

    // 8个角块，三个字母的组合，同时需要删除重复的
    for ($i = 0; $i < $num; $i++) {
        for ($j = $i+1; $j < $num; $j++) {
            for ($m = $j+1; $m < $num; $m++) {  // 排列组合，不分前后，三个面需要不同所以可以$m = $j+1;
                // 需要排除掉上下、前后、左右这三种ud、lr、fb字母同时出现的问题
                $key = $face6_arr[$i] . $face6_arr[$j] . $face6_arr[$m]; // 棱块key名称需要固定一个顺序
                if (false !== strpos($key, 'u') && false !== strpos($key, 'd')) continue;
                if (false !== strpos($key, 'f') && false !== strpos($key, 'b')) continue;
                if (false !== strpos($key, 'l') && false !== strpos($key, 'r')) continue;

                $key = getSort($key);    // 返回稳定的排序，确保key唯一
                if (isset($l_rlt[$key]))
                    continue;
                $l_rlt[$key] = $face_color[$key[0]] . $face_color[$key[1]] . $face_color[$key[2]]; // 获取对应颜色字符串
            }
        }
    }

    return $l_rlt;
}


// 初始状态, 6个面，颜色初始化, TODO 暂时用不上，页面展示可能会用上
//$ob = [
//    'u'=>[['y','y','y'],['y','y','y'],['y','y','y']],
//    'l'=>[['o','o','o'],['o','o','o'],['o','o','o']],
//    'f'=>[['b','b','b'],['b','b','b'],['b','b','b']],
//    'r'=>[['r','r','r'],['r','r','r'],['r','r','r']],
//    'b'=>[['g','g','g'],['g','g','g'],['g','g','g']],
//    'd'=>[['w','w','w'],['w','w','w'],['w','w','w']]
//];
//$ob = init_ob($rlt_str_face_order, $face_color);

// 6个面，颜色初始化
function init_ob($face6_arr, $face_color) {
    $l_arr = [];
    // 6个面
    foreach ($face6_arr as $letter_1) {
        $l_color = [];
        $one_c = $face_color[$letter_1];
        $l_color[] = [$one_c, $one_c, $one_c];  // 第1行3个元素
        $l_color[] = [$one_c, $one_c, $one_c];  // 第2行3个元素
        $l_color[] = [$one_c, $one_c, $one_c];  // 第3行3个元素
        $l_arr[$letter_1] = $l_color; // 每个面的3X3=9个元素的颜色数组
    }
    return $l_arr;
}


// TODO del
function getResultStringFaceOrder() {
    $l_order = $GLOBALS['rlt_str_face_order'];
    return $GLOBALS['rlt_str_face_order']; // 可以做一些检查，避免
}

// 定义排序函数，用于字符串排序
function compareStr($a, $b) {
    $a = trim($a);
    $b = trim($b);
    $face_sort = $GLOBALS['rlt_str_face_order']; // 字母必须在这些指定的字母中
    $face_flip = array_flip($face_sort);         // 键值互换
    if (!in_array($a, $face_sort) || !in_array($b, $face_sort)) {
        exit( $a . ' or '. $b . ' not in face_sort!');
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

function filter_action($str) {
    if (in_array(strtolower($str), $GLOBALS['play_action9']))
        return true;
    return false;
}


/**************************** 魔方基本动作 ******************************/
//$m = [
//    'd'=>   'w',   'u'=>   'y',   'l'=>   'o',   'f'=>   'b',   'r'=>   'r',   'b'=>   'g', // 6个中心块， 下白上黄，前蓝后绿，左橙右红;名称及其对应颜色；
//    'dl'=>  'wo',  'df'=>  'wb',  'dr'=>  'wr',  'db'=>  'wg',  // 12个中间棱块之下层交界处，4个；名称及其对应颜色；
//    'ul'=>  'yo',  'uf'=>  'yb',  'ur'=>  'yr',  'ub'=>  'yg',  // 12个中间棱块之上层交界处，4个；
//    'lf'=>  'ob',  'fr'=>  'br',  'rb'=>  'rg',  'lb'=>  'go',  // 12个中间棱块之中层交界处，4个；
//    'dlf'=> 'wob', 'dfr'=> 'wbr', 'drb'=> 'wrg', 'dlb'=> 'wgo', // 8个角块之下层，4个；名称及其对应颜色；
//    'ulf'=> 'yob', 'ufr'=> 'ybr', 'urb'=> 'yrg', 'ulb'=> 'ygo'  // 8个角块之上层，4个；
//];

//背面顺时针旋转90°
function mov_b() {
    global $m;
    // 背面中心块没有动；只有4个棱块和4个角块共8块位置发生变化。
    $tmp = $m['ub'];        // 选择一个块临时存放，
    $m['ub'] = $m['rb'];    // 上面背面被右背替换，
    $m['rb'] = $m['db'];
    $m['db'] = $m['bl'];
    $m['db'] = $m['db'][1] + $m['db'][0];
    $m['bl'] = $tmp;
    $m['bl'] = $m['bl'][1] + $m['bl'][0];
    $tmp = $m['ubl'];
    $m['ubl'] = $m['urb'];
    $m['ubl'] = $m['ubl'][1] + $m['ubl'][2] + $m['ubl'][0];
    $m['urb'] = $m['drb'];
    $m['urb'] = $m['urb'][1] + $m['urb'][0] + $m['urb'][2];
    $m['drb'] = $m['dbl'];
    $m['drb'] = $m['drb'][2] + $m['drb'][0] + $m['drb'][1];
    $m['dbl'] = $tmp;
    $m['dbl'] = $m['dbl'][2] + $m['dbl'][1] + $m['dbl'][0];
}


//右面顺时针旋转90°
function mov_r() {
    global $m;
    $tmp = $m['ur'];
    $m['ur'] = $m['fr'];
    $m['fr'] = $m['dr'];
    $m['dr'] = $m['rb'];
    $m['dr'] = $m['dr'][1] + $m['dr'][0];
    $m['rb'] = $tmp;
    $m['rb'] = $m['rb'][1] + $m['rb'][0];
    $tmp = $m['urb'];
    $m['urb'] = $m['ufr'];
    $m['urb'] = $m['urb'][1] + $m['urb'][2] + $m['urb'][0];
    $m['ufr'] = $m['dfr'];
    $m['ufr'] = $m['ufr'][1] + $m['ufr'][0] + $m['ufr'][2];
    $m['dfr'] = $m['drb'];
    $m['dfr'] = $m['dfr'][2] + $m['dfr'][0] + $m['dfr'][1];
    $m['drb'] = $tmp;
    $m['drb'] = $m['drb'][2] + $m['drb'][1] + $m['drb'][0];
}

//前面顺时针旋转90°
function mov_f() {
    global $m;
    $tmp = $m['uf'];
    $m['uf'] = $m['lf'];
    $m['lf'] = $m['df'];
    $m['df'] = $m['fr'];
    $m['df'] = $m['df'][1] + $m['df'][0];
    $m['fr'] = $tmp;
    $m['fr'] = $m['fr'][1] + $m['fr'][0];
    $tmp = $m['ufr'];
    $m['ufr'] = $m['ulf'];
    $m['ufr'] = $m['ufr'][1] + $m['ufr'][2] + $m['ufr'][0];
    $m['ulf'] = $m['dlf'];
    $m['ulf'] = $m['ulf'][1] + $m['ulf'][0] + $m['ulf'][2];
    $m['dlf'] = $m['dfr'];
    $m['dlf'] = $m['dlf'][2] + $m['dlf'][0] + $m['dlf'][1];
    $m['dfr'] = $tmp;
    $m['dfr'] = $m['dfr'][2] + $m['dfr'][1] + $m['dfr'][0];
}

//左面顺时针旋转90°
function mov_l() {
    global $m;
    $tmp = $m['ul'];
    $m['ul'] = $m['bl'];
    $m['bl'] = $m['dl'];
    $m['dl'] = $m['lf'];
    $m['dl'] = $m['dl'][1] + $m['dl'][0];
    $m['lf'] = $tmp;
    $m['lf'] = $m['lf'][1] + $m['lf'][0];
    $tmp = $m['ulf'];
    $m['ulf'] = $m['ubl'];
    $m['ulf'] = $m['ulf'][1] + $m['ulf'][2] + $m['ulf'][0];
    $m['ubl'] = $m['dbl'];
    $m['ubl'] = $m['ubl'][1] + $m['ubl'][0] + $m['ubl'][2];
    $m['dbl'] = $m['dlf'];
    $m['dbl'] = $m['dbl'][2] + $m['dbl'][0] + $m['dbl'][1];
    $m['dlf'] = $tmp;
    $m['dlf'] = $m['dlf'][2] + $m['dlf'][1] + $m['dlf'][0];
}

//顶面顺时针旋转90°
function mov_u() {
    global $m;
    //棱块转动
    $tmp = $m['ul'];
    $m['ul'] = $m['uf'];
    $m['uf'] = $m['ur'];
    $m['ur'] = $m['ub'];
    $m['ub'] = $tmp;
    //角块转动
    $tmp = $m['ulf'];
    $m['ulf'] = $m['ufr'];
    $m['ufr'] = $m['urb'];
    $m['urb'] = $m['ubl'];
    $m['ubl'] = $tmp;
}

//底面顺时针旋转90°
function mov_d() {
    global $m;
    //棱块转动
    $tmp = $m['dl'];
    $m['dl'] = $m['db'];
    $m['db'] = $m['dr'];
    $m['dr'] = $m['df'];
    $m['df'] = $tmp;
    //角块转动
    $tmp = $m['dlf'];
    $m['dlf'] = $m['dbl'];
    $m['dbl'] = $m['drb'];
    $m['drb'] = $m['dfr'];
    $m['dfr'] = $tmp;
}

    //魔方基本动作函数打包
    function mov($com) {
        switch($com){
            case 'd': 	//d - 底面顺时针旋转90°
                mov_d();
                break;
            case 'D': 	//D - 底面逆时针旋转90°
                mov_d();mov_d();mov_d();
                break;
            case 'u': 	//u - 顶面顺时针旋转90°
                mov_u();
                break;
            case 'U': 	//U - 顶面逆时针旋转90°
                mov_u();mov_u();mov_u();
                break;
            case 'l': 	//l - 左面顺时针旋转90°
                mov_l();
                break;
            case 'L': 	//L - 左面逆时针旋转90°
                mov_l();mov_l();mov_l();
                break;
            case 'f': 	//f - 前面顺时针旋转90°
                mov_f();
                break;
            case 'F': 	//F - 前面逆时针旋转90°
                mov_f();mov_f();mov_f();
                break;
            case 'r': 	//r - 右面顺时针旋转90°
                mov_r();
                break;
            case 'R': 	//R - 右面逆时针旋转90°
                mov_r();mov_r();mov_r();
                break;
            case 'b': 	//b - 后面顺时针旋转90°
                mov_b();
                break;
            case 'B': 	//B - 后面逆时针旋转90°
                mov_b();mov_b();mov_b();
                break;
        }
    }

    //魔方组合动作
    function exe($com) {
        for ($i = 0; $i < count($com); $i++) {
            mov($com[$i]);
        }
    }

    //压缩指令数,一正一反、旋转4圈等都相当于没有旋转；顺时针旋转3圈相当于逆时针旋转一圈
    function reduce($str) {
        $min = str_replace(['uU','Uu','dD','Dd','lL','Ll','fF','Ff','rR','Rr','bB','Bb','uuuu','dddd','llll','ffff','rrrr','bbbb'], '', $str);
        $min = str_replace('uuu', 'U', $min);
        $min = str_replace('ddd', 'D', $min);
        $min = str_replace('lll', 'L', $min);
        $min = str_replace('fff', 'F', $min);
        $min = str_replace('rrr', 'R', $min);
        $min = str_replace('bbb', 'B', $min);
		return $min;
	}

    /*************************** 输入输出操作 **************************/
    //根据魔方六个面的颜色数组获取魔方状态
    function scan_by_face($ob) {
        global $m;
        $m['d'] = $ob['d'][1][1];
        $m['u'] = $ob['u'][1][1];
        $m['l'] = $ob['l'][1][1];
        $m['f'] = $ob['f'][1][1];
        $m['r'] = $ob['r'][1][1];
        $m['b'] = $ob['b'][1][1];
        $m['dl'] = $ob['d'][1][0] + $ob['l'][2][1];
        $m['df'] = $ob['d'][0][1] + $ob['f'][2][1];
        $m['dr'] = $ob['d'][1][2] + $ob['r'][2][1];
        $m['db'] = $ob['d'][2][1] + $ob['b'][2][1];
        $m['ul'] = $ob['u'][1][0] + $ob['l'][0][1];
        $m['uf'] = $ob['u'][2][1] + $ob['f'][0][1];
        $m['ur'] = $ob['u'][1][2] + $ob['r'][0][1];
        $m['ub'] = $ob['u'][0][1] + $ob['b'][0][1];
        $m['lf'] = $ob['l'][1][2] + $ob['f'][1][0];
        $m['fr'] = $ob['f'][1][2] + $ob['r'][1][0];
        $m['rb'] = $ob['r'][1][2] + $ob['b'][1][0];
        $m['bl'] = $ob['b'][1][2] + $ob['l'][1][0];
        $m['dlf'] = $ob['d'][0][0] + $ob['l'][2][2] + $ob['f'][2][0];
        $m['dfr'] = $ob['d'][0][2] + $ob['f'][2][2] + $ob['r'][2][0];
        $m['drb'] = $ob['d'][2][2] + $ob['r'][2][2] + $ob['b'][2][0];
        $m['dbl'] = $ob['d'][2][0] + $ob['b'][2][2] + $ob['l'][2][0];
        $m['ulf'] = $ob['u'][2][0] + $ob['l'][0][2] + $ob['f'][0][0];
        $m['ufr'] = $ob['u'][2][2] + $ob['f'][0][2] + $ob['r'][0][0];
        $m['urb'] = $ob['u'][0][2] + $ob['r'][0][2] + $ob['b'][0][0];
        $m['ubl'] = $ob['u'][0][0] + $ob['b'][0][2] + $ob['l'][0][0];
        //return $m;
    }

    //根据魔方对象获取魔方状态
    function scan_by_obj($ob) {
        global $m;
        foreach ($ob as $k => $l_val){
            $m[$k] = $l_val;
        }
    }

    //输入魔方状态
    function scan($ob, $type) {
        if ($type == 1) {		// 通过对象方式获取魔方状态
            scan_by_obj($ob);
        } else {
            scan_by_face($ob);
        }
    }

    //输出魔方状态
    function out() {
        global $m;
        return $m;
    }

    /****************************** 其它 *******************************/
    //随机打乱魔方
    function mad($n) {
        $n = $n ? $n : 24;
        $n = $n > 240 ? 240 : $n;
        $arr = ['u','d','l','f','r','b','U','D','L','F','R','B'];
        $str = '';
        for($i = 0; $i < $n; $i++){
            $x =  mt_rand(0,11);
            $str .= $arr[$x];
        }
        exe($str);
        return $str;
    }


