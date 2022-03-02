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

 -- 获取操作的反操作  F R U R' U' F' 得到 F U R u r f
 php rubikcube.php -d "F R U R' U' F'" -i 1



 -- 获取解魔方步骤，以下是经过一次R操作后的状态
 php rubikcube.php -e "UUFUUFUUFRRRRRRRRRFFDFFDFFDDDBDDBDDBLLLLLLLLLUBBUBBUBB" -n 3 --e_order "urfdlb"

 -- 提供-d数据表示校验改旋转步骤是否正确
 php rubikcube.php -e "UUFUUFUUFRRRRRRRRRFFDFFDFFDDDBDDBDDBLLLLLLLLLUBBUBBUBB" --e_order "urfdlb" -d R


 2. 其他组织方式输出:
 php rubikcube.php -d "" -p pglass -c "{\"u\":\"O\",\"l\":\"Y\",\"f\":\"W\",\"r\":\"G\",\"b\":\"B\",\"d\":\"R\"}"
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

 4. 支持python直接执行PHP命令，并获取执行结果
 (ailearn_py37) F:\develope\python\game\mofang_rubikcube\rubiksCube_AlphaZero>python main.py -d "B R B "
  python F:/develope/python/game/mofang_rubikcube/rubiksCube_AlphaZero/main.py -d " R B " (不建议在其他目录执行，重新生成这些文件很耗时)
  python F:/develope/python/game/mofang_rubikcube/rubiksCube_AlphaZero/main.py -d " M" (该python程序不支持三个中间层M S E的转动)

 D:/php8103ntsx64/php.exe F:/develope/javascript/game_/mofang_rubikcube/morefun_uipps/rubikcube.php -g 0 -d "RR"
 D:/php8103ntsx64/php.exe F:/develope/javascript/game_/mofang_rubikcube/morefun_uipps/rubikcube.php -g 1 -d "R F"


 5. -d "B R" 位置不对研究，-d "R B"却是正确的
   -- 程序执行后，得到的结果字符串是： RRFUUFUUF RDDRRRRDD FFDFFDFFLDDBDDBLLBULLULLULLUBBUBBRBB ；
        实际上右侧应该是： RRRRRRDDD ; 得到的右侧是： RDDRRRRDD

  php rubikcube.php -d "B R" -g 1 > br_err.txt



 3. 输入所要得到的效果（初始状态为6面全好），限定多少步骤完成，用程序跑出所做的操作-多少种都列表出，TODO 所有的解法，暂时都不使用MSE,以后有空再研究MSE
    (大写字母是顺时针，大写字母+'是逆时针,小写字母也是逆时针)，
    支持用x表示不关心的块位置，-t字符串的按照我们方便的U L F R B D (上 左 前 右 后 下) 6个面的顺序给出，由PHP转成python脚本习惯的 上 右 前 下 左 后

   1) 需求： 实现最后一步，将上层三个互换位置的的棱块归位，所有面均好，只有三个色块换了位子。如图所示
    UUU
    UUU
    UUU
LRL FLF RFR BBB
LLL FFF RRR BBB
LLL FFF RRR BBB
    DDD
    DDD
    DDD

  通过公式：F2 U' R' L F2 R L' U' F2 能将复原的魔方变成上图的样子
  通过公式：F2 U  R' L F2 R L' U  F2 能将上图复原，
  本程序的解法：(未提供-b开始状态，则默认为全部归位的状态)
    php rubikcube.php -e "UUUUUUUUU LRLLLLLLL FLFFFFFFF RFRRRRRRR BBBBBBBBB DDDDDDDDD" -n 5 --e_order "ulfrbd" --b_order ulfrbd
    php rubikcube.php -e "UUUUUUUUU LRLLLLLLL FLFFFFFFF RFRRRRRRR BBBBBBBBB DDDDDDDDD" -n 3 --e_order "ulfrbd" --b_order urfdlb
    php rubikcube.php -e "UUFUUFUUFRRRRRRRRRFFDFFDFFDDDBDDBDDBLLLLLLLLLUBBUBBUBB" -n 3 --e_order "urfdlb"


   2) 示例：第二层中间棱块公式，打小怪兽公式，将位于FU位置的FR棱块归位（在前）
  期望将：
    xxx
    xxX
    xxx
xxx xxx xxx xxx
LLL FFF RRR BBB
LLL FFF RRR BBB
    DDD
    DDD
    DDD

 要变成 ===> 如下的棱块归位

    xxx
    xxX
    xxx
xxx xxx xxx xxx
LLL FFF RRR BBB
LLL FFF RRR BBB
    DDD
    DDD
    DDD
 打小怪兽 1) 在前  U  R  U' R' U' F' U  F
         2) 在右  U' F' U  F  U  R  U' R'

  通过公式： U  R  U' R' U' F' U  F 能将上图变成想要的状态
  本程序的解法：(未提供-b开始状态，则默认为全部归位的状态)
    php rubikcube.php -e "UUUUUUUUU LRLLLLLLL FLFFFFFFF RFRRRRRRR BBBBBBBBB DDDDDDDDD" -n 10 --e_order "ulfrbd" --b_order urfdlb


 */
//define('clockwise', 'clockwise');           // '顺时针'
//define('anti-clockwise', 'anti-clockwise'); // '逆时针'

include_once 'morefun.php';


$GLOBALS['debug'] = 1;

$option = getopt('d:o:a:k:p:c:g:s:f:b:e:n:i:', ['e_order:', 'b_order:']);
main($option);


function main($o) {
    // 1. 参数检查，
    //   1) action参数，转动动作字母是否在指定动作限定内，不认识的动作，直接删除掉
    $str = (isset($o['d']) && $o['d']) ? $o['d'] : '';
    //if (!isset($o['d'])) {
        //echo '  用法如下： php rubikcube.php -d "R R\'"' . "\r\n";
    //}

    //   9) 获取操作的反操作
    if (isset($o['i']) && $o['i']) {
        $inverse_act = get_inverse_operation($str);
        echo date('Y-m-d H:i:s') . ' ' . $str . ' 的逆反操作为：' . $inverse_act . "\r\n"; // 也可以把小写字母替换成U'
        return '';
    }


    $end_order = (isset($o['e_order']) && $o['e_order']) ? $o['e_order'] : implode(human_habit_order);
    $begin_order = (isset($o['b_order']) && $o['b_order']) ? $o['b_order'] : implode(human_habit_order);

    //   2) 结果顺序order参数, 主要是结果顺序
    $order_str = (isset($o['o']) && $o['o']) ? $o['o'] : 'urfdlb';  //  // $order_str = 'ulfrbd'; 符合人们查看习惯的

    //
    $order_type = (isset($o['p']) && $o['p']) ? $o['p'] : '';
    $pglass_type = 0;
    if ('pglass' == $order_type)
        $pglass_type = 1;
    $pglass_color = (isset($o['c']) && $o['c']) ? $o['c'] : '{"u":"O","l":"Y","f":"W","r":"G","b":"B","d":"R"}';

    // 魔方对象，共6面，每面9块，共有54块，存储魔方各面颜色状态。初始就用
    $mofun_init = init_morefun($GLOBALS['kociemba_face_order']); // 共26个元素，程序生成的跟上面一样，只是bl组合的顺序不一样。

    $mofang_obj = $mofun_init;

    //   3) 初始状态，可以用x表示不关心的块位置
    $begin_str = (isset($o['b']) && $o['b']) ? $o['b'] : '';
    //   4) 动作别名
    $alias_arr = (isset($o['a']) && $o['a']) ? $o['a'] : [];
    //   5) 是否要空格
    $kongge = (isset($o['k']) && $o['k']) ? $o['k'] : 0;
    //   6) 是否debug
    $GLOBALS['debug'] = isset($o['g']) ? $o['g'] : 1;

    //   7) 解魔方最大步数
    $num_solve = (isset($o['n']) && $o['n']) ? $o['n'] : GOD_NUM;
    if ($num_solve < 1) {exit('  $num_solve is error!');};
    if ($num_solve > GOD_NUM) $num_solve = GOD_NUM;

    //   8) 终态，即目标字符串，支持用x表示不关心的块位置
    if (isset($o['e']) && $o['e']) {
        $end_str = $o['e'];
        $end_ob = fillMoFangWithString($end_str, $end_order, $pglass_type);

        // 判断end是否完好的魔方，后续处理方式会有不同
        $mofun_init_str = getRltStr($mofun_init, $end_order, 0);
        $end_is_init = 0;
        if ($mofun_init_str == str_replace(' ', '', $end_str))
            $end_is_init = 1;

        // 判断begin是否完好的魔方
        $begin_is_init = 0;
        if ($begin_str) {
            $begin_ob = fillMoFangWithString($begin_str, $begin_order, $pglass_type);
            $mofun_init_str = getRltStr($mofun_init, $begin_order, 0);
            if ($mofun_init_str == str_replace(' ', '', $begin_str))
                $begin_is_init = 1;
        } else {
            $begin_ob = $mofun_init;
            $begin_is_init = 1;
        }
        //print_r($begin_ob);exit;

        //if ($GLOBALS['debug']) echo "    对应的图形展示：\r\n" . getGraghOfMoFang($end_ob) . "\r\n";
        // 分为三种情况: 1.初态是完好的魔方 2.目标是完好的魔方 3.初态和目标都不是完好的魔方
        if ($begin_is_init) {
            return solve_mofang_beginOk($begin_ob, $end_ob, $num_solve, $str);
        } else if ($end_is_init) {
            echo ' please change to begin_is_init! 参考begin是完好魔方的情况，应该类似，只是倒过来了。' . "\n";
            return '';
        } else {
            return solve_mofang_part($begin_ob, $end_ob, $num_solve, $str);
        }
    }

    // 2. 参数过滤，如果出现了不被识别的动作，过滤掉，并不给出提示。全部变成 F,F2,f
    $action_arr = get_action_by_str($str, $alias_arr);

    // 3. 初始状态：通常默认是完好的 $begin_ob，也可以参数指定


    // 4. 逐个动作执行，也可以是空动作，不进行twist旋转操作
    if ($action_arr) {
        // print_r($action_arr);
        twist_multi($mofang_obj, $action_arr);
    }
    //if ($GLOBALS['debug']) echo ' after "' . $str .'" , $mofun: ' . "\r\n"; print_r($mofang_obj);

    // 5. 按照order参数指定的顺序，输出各个面各块的颜色。
    if ($pglass_type) {
        $l_str = getPglassRltStr($mofang_obj, $pglass_color);
    } else if (isset($o['f']) && $o['f']) {
        // -b "ddd" -f 1 -o ulfrbd
        // format 字符串为, 格式化输出
        if (isset($o['b']) && $o['b']) {
            $begin_ob = fillMoFangWithString($o['b'], $begin_order, $pglass_type);
            $l_str = getRltStr($begin_ob, $order_str, $kongge);
        }
        //else if (isset($o['e']) && $o['e'])
        //    $l_str = getRltStr($end_ob, $order_str, $kongge);
        else
            $l_str = getRltStr($mofang_obj, $order_str, $kongge);
    } else
        $l_str = getRltStr($mofang_obj, $order_str, $kongge);

    echo $l_str . "\r\n";
    if ($GLOBALS['debug']) echo "    对应的图形展示：\r\n" . getGraghOfMoFang($mofang_obj) . "\r\n";

    return '';
}

// 解魔方, 初始状态完后的情形，需要指定旋转次数-不是最大次数(不是20以内的数字，上帝之数是20，因此通常不能超过20步)，指定多少步数就多少步数完成。
function solve_mofang_beginOk($begin_obj, $end_obj, $num_solve=20, $act_str='') {
    $orig_begin_ob = $begin_obj;
    // 输出初始状态图案和目标状态图案，
    echo date('Y-m-d H:i:s') . '  初始状态：' . "\r\n" . getGraghOfMoFang($begin_obj) . "\r\n";
    echo date('Y-m-d H:i:s') . '  目标状态：' . "\r\n" . getGraghOfMoFang($end_obj) . "\r\n";

    // 如果提供了转动步骤，表示验证一下转动步骤是否能成功！
    if ($act_str) {
        $is_right_act = is_succ_actions($begin_obj, $end_obj, $act_str);
        if ($is_right_act)
            echo date('Y-m-d H:i:s') . '      ' . $act_str . ' 方法正确！' . "\r\n";
        else
            echo date('Y-m-d H:i:s') . '      ' . $act_str . ' 方法不正确：' . "\r\n";
        return '';
    }

    // 未提供转动步骤，则寻找所有的转动方案，并且是在指定的步数，不能多不能少。9个面，每个面可以有三种转动方法，
    //   遍历所有的可能组合。上帝之数最大CJ(27,20) = 4522487307570679132924674048; (含MSE)
    //                               CJ(18,20) = 399030807609558105468750;

    $l_movies = [];
    // 从最少的步骤开始找，逐步增加到最多$num_solve步骤解决
    for ($i = 1; $i <= $num_solve; $i++) {
        solve_by_number_beginOK($l_movies, $begin_obj, $end_obj, $i);
    }

    if (!$l_movies)
        echo date('Y-m-d H:i:s') . '      在 ' . $num_solve . ' 步骤内，未找到解魔方方法！' . "\r\n";
    else
        echo date('Y-m-d H:i:s') . '      在 ' . $num_solve . ' 步骤内，解决方案有：' . "\r\n" . implode("\r\n", $l_movies) . "\r\n";

    return '';
}
