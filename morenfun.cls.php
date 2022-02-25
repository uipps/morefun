<?php

// 魔方对象，存储魔方的状态。一共6+12=8=26个
$m = [
    'd'=>   'w',   'u'=>   'y',   'l'=>   'o',   'f'=>   'b',   'r'=>   'r',   'b'=>   'g', // 6个中心块， 下白上黄，左橙右红，前蓝后绿;名称及其对应颜色；
    'dl'=>  'wo',  'df'=>  'wb',  'dr'=>  'wr',  'db'=>  'wg',  // 12个中间棱块之下层交界处，4个；名称及其对应颜色；
    'ul'=>  'yo',  'uf'=>  'yb',  'ur'=>  'yr',  'ub'=>  'yg',  // 12个中间棱块之上层交界处，4个；
    'lf'=>  'ob',  'fr'=>  'br',  'rb'=>  'rg',  'bl'=>  'go',  // 12个中间棱块之中层交界处，4个；
    'dlf'=> 'wob', 'dfr'=> 'wbr', 'drb'=> 'wrg', 'dbl'=> 'wgo', // 8个角块之下层，4个；名称及其对应颜色；
    'ulf'=> 'yob', 'ufr'=> 'ybr', 'urb'=> 'yrg', 'ubl'=> 'ygo'  // 8个角块之上层，4个；
];

//魔方颜色
$c = ['r'=>'red','g'=>'green','y'=>'yellow','o'=>'orange','b'=>'blue','w'=>'white'];

$next = ['l'=>'f','f'=>'r','r'=>'b','b'=>'l'];      // left --> front --> right --> back --> left
$prev = ['l'=>'b','b'=>'r','r'=>'f','f'=>'l'];      // 上面的反向链表
$next_c = ['o'=>'b','b'=>'r','r'=>'g','g'=>'o'];    // next链表的颜色表示。如 left(orange) --> front(blue) --> right(red) --> back(green) --> left

/**************************** 魔方基本动作 (小写字母是顺时针，大写字母是逆时针) ******************************/
//back后面顺时针旋转90°
function mov_b() {
    global $m;
    $tmp = $m['ub'];
    $m['ub'] = $m['rb'];
    $m['rb'] = $m['db'];
    $m['db'] = $m['bl']; $m['db'] = $m['db'][1] + $m['db'][0];
    $m['bl'] = $tmp;  $m['bl'] = $m['bl'][1] + $m['bl'][0];
    $tmp = $m['ubl'];
    $m['ubl'] = $m['urb']; $m['ubl'] = $m['ubl'][1] + $m['ubl'][2] + $m['ubl'][0];
    $m['urb'] = $m['drb']; $m['urb'] = $m['urb'][1] + $m['urb'][0] + $m['urb'][2];
    $m['drb'] = $m['dbl']; $m['drb'] = $m['drb'][2] + $m['drb'][0] + $m['drb'][1];
    $m['dbl'] = $tmp;   $m['dbl'] = $m['dbl'][2] + $m['dbl'][1] + $m['dbl'][0];
}


//右面顺时针旋转90°
function mov_r() {
    global $m;
    $tmp = $m['ur'];
    $m['ur'] = $m['fr'];
    $m['fr'] = $m['dr'];
    $m['dr'] = $m['rb']; $m['dr'] = $m['dr'][1] + $m['dr'][0];
    $m['rb'] = $tmp;  $m['rb'] = $m['rb'][1] + $m['rb'][0];
    $tmp = $m['urb'];
    $m['urb'] = $m['ufr']; $m['urb'] = $m['urb'][1] + $m['urb'][2] + $m['urb'][0];
    $m['ufr'] = $m['dfr']; $m['ufr'] = $m['ufr'][1] + $m['ufr'][0] + $m['ufr'][2];
    $m['dfr'] = $m['drb']; $m['dfr'] = $m['dfr'][2] + $m['dfr'][0] + $m['dfr'][1];
    $m['drb'] = $tmp;   $m['drb'] = $m['drb'][2] + $m['drb'][1] + $m['drb'][0];
}

//前面顺时针旋转90°
function mov_f() {
    global $m;
    $tmp = $m['uf'];
    $m['uf'] = $m['lf'];
    $m['lf'] = $m['df'];
    $m['df'] = $m['fr']; $m['df'] = $m['df'][1] + $m['df'][0];
    $m['fr'] = $tmp;  $m['fr'] = $m['fr'][1] + $m['fr'][0];
    $tmp = $m['ufr'];
    $m['ufr'] = $m['ulf']; $m['ufr'] = $m['ufr'][1] + $m['ufr'][2] + $m['ufr'][0];
    $m['ulf'] = $m['dlf']; $m['ulf'] = $m['ulf'][1] + $m['ulf'][0] + $m['ulf'][2];
    $m['dlf'] = $m['dfr']; $m['dlf'] = $m['dlf'][2] + $m['dlf'][0] + $m['dlf'][1];
    $m['dfr'] = $tmp;   $m['dfr'] = $m['dfr'][2] + $m['dfr'][1] + $m['dfr'][0];
}

//左面顺时针旋转90°
function mov_l() {
    global $m;
    $tmp = $m['ul'];
    $m['ul'] = $m['bl'];
    $m['bl'] = $m['dl'];
    $m['dl'] = $m['lf']; $m['dl'] = $m['dl'][1] + $m['dl'][0];
    $m['lf'] = $tmp;  $m['lf'] = $m['lf'][1] + $m['lf'][0];
    $tmp = $m['ulf'];
    $m['ulf'] = $m['ubl']; $m['ulf'] = $m['ulf'][1] + $m['ulf'][2] + $m['ulf'][0];
    $m['ubl'] = $m['dbl']; $m['ubl'] = $m['ubl'][1] + $m['ubl'][0] + $m['ubl'][2];
    $m['dbl'] = $m['dlf']; $m['dbl'] = $m['dbl'][2] + $m['dbl'][0] + $m['dbl'][1];
    $m['dlf'] = $tmp;   $m['dlf'] = $m['dlf'][2] + $m['dlf'][1] + $m['dlf'][0];
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

    /*************************** 层先法复原魔方 **************************/
    //查找块所在的位置及状态, TODO 暂不需要复原整个魔方
//    function pos($block) {
//        global $m;
//        $reg = '/[' . $block . ']{' . count($block) . '}/i';
//        foreach ($m as $k => $l_val) {
//            if (preg_match($reg, $m[$k])) {
//                return ['k' => $k, 'v' => $m[$k]];
//            }
//        }
//        echo 'something error! in pos';
//        return ['k' => '', 'v' =>''];
//    }

    //调整单个底棱块
//    function _step_1($position, $block) {
//        global $m,$next,$next_c;
//        $exp = '';
//        $exp_log = '';
//        $s = '';
//        for($i = 0; $i < 7; $i++){
//            $s = pos($block);
//            if (false !== strpos($s['k'], 'd')) {
//                if($s['v'][0] == $block[0]){
//                    if($s['k'] == $position){
//                        //console.log(exp_log);
//                        return $exp_log;		//最终返回指令
//                    }else{
//                        $exp = $s['k'][1] + $s['k'][1];
//                    }
//                }else{
//                    $exp = $s['k'][1];
//                }
//            }else if(false !== strpos($s['k'], 'u')){
//                if($s['k'][1] == $position[1]){
//                    if($s['v'][0] == $block[0]){
//                        $exp = $s['k'][1] + $s['k'][1];
//                    }else if($m[$position[0] . $next[$s['k'][1]]] != $m[$position[0]] . $m[$next[$s['k'][1]]]){
//                        $exp = 'U' . $next[$s['k'][1]].toUpperCase() . $s['k'][1];
//                    }else{
//                        $exp = 'U' . $next[$s['k'][1]].toUpperCase() . $s['k'][1] . $next[$s['k'][1]];
//                    }
//                }else{
//                    $exp = 'u';
//                }
//            }else{
//                if($s['v'][0] == $block[0]){
//                    if($s['k'][1] == $position[1]){
//                        $exp = strtoupper($s['k'][1]);
//                    }else if($m[$position[0] . $s['k'][1]] != $m[$position[0]] . $m[$s['k'][1]]){
//                        $exp = $s['k'][1];
//                    }else{
//                        $exp = $s['k'][1] . 'u' . strtoupper($s['k'][1]);
//                    }
//                }else{
//                    if($s['k'][0] == $position[1]){
//                        $exp = $s['k'][0];
//                    }else if($m[$position[0]+$s['k'][0]] != $m[$position[0]]+$m[$s['k'][0]]){
//                        $exp = strtoupper($s['k'][0]);
//                    }else{
//                        $exp = strtoupper($s['k'][0]) . 'u' . $s['k'][0];
//                    }
//                }
//            }
//            $exp_log .= $exp;
//            exe($exp);
//        }
//        //console.log('[1'+$exp_log+'1]');
//        return '[1' . $exp_log . '1]';
//    }

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


