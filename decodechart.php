<?php
header('Content-type: image/svg+xml');
header('Vary: Accept-Encoding');
$logfiles = "";
foreach (glob("logs/".date("m-d-Y")."*.log") as $filename)
  $logfiles .= file_get_contents($filename);

preg_match_all("/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}).*Control Channel Message Decode Rate: (.*)\/sec/", $logfiles, $decode);
unset($decode[0]);
foreach($decode[1] as $theentry => $thevalue)
  $decode2[$thevalue] = (float)$decode[2][$theentry];
//var_dump($decode2);
//include 'cms_chart.php';
$init['chart'] = 'line';
$init['fmt'] = 'xss';
$init['xSkip'] = 59;
$init['w'] = 960;
$init['h'] = 500;
$init['valShow'] = 0;
$init['xAngle'] = -90;
$init['title'] = 'Today\'s King County Metro Transit control channel decoding rate';
cms_chart($decode2, $init);

function cms_chart(&$data = null, $init = null) {
    $ceil = array('w', 'h', 'gapT', 'gapR', 'gapB', 'gapL', 'legendW', 'titleAlign', 'xTitleAlign', 'yTitleAlign', 'xSkip', 'xSkipMax', 'xAngle', 'ySum', 'pieArc', 'pieStripe', 'piePct', 'pieDonut', 'css', 'valAngle', 'valShow');
    $trim = array('chart', 'fmt', 'title', 'legend', 'style', 'sepCol', 'sepRow', 'xTitle', 'yTitle', 'yUnit', 'xFormat', 'yFormat', 'xMin', 'xMax', 'xKey', 'sort', 'xSum');
    foreach ($ceil as $k) $arr[$k] = isset($init[$k]) ? ceil($init[$k]) : 0;
    foreach ($trim as $k) $arr[$k] = isset($init[$k]) ? trim($init[$k]) : '';
    $data = cms_chart_data($data, $arr['fmt'], $arr['sort'], $arr['xKey'], $arr['xMin'], $arr['xMax'], $arr['sepRow'], $arr['sepCol'], $arr['ySum'], 'barS' === $arr['chart']);
    if (!$data) return;
    $color = cms_chart_color($init);
    $init = cms_chart_init($arr, $data, 'fix' === (isset($init['w']) ? $init['w'] : ''));
    extract($init);
    $init['color'] = $color;
    echo "\n" . '<svg viewBox="0 0 '. "$w $h" .'" class="chart"  version="1.0" xmlns="http://www.w3.org/2000/svg" >';
    
    echo "
<defs>
<style type=\"text/css\"><![CDATA[
svg.chart{display:block}
.chart-bg{fill:#eee;opacity:.5}
.chart-box{fill:#fff;opacity:1}
.chart-tick line{stroke:#ddd;stroke-width:1;stroke-dasharray:5,5}
.chart text{font-family:Helvetica,Arial,Verdana,sans-serif;font-size:12px;fill:#666}
.axisX text{text-anchor:middle}
.xAngle text{text-anchor:start}
.axisY text{text-anchor:end}
.chart circle{stroke-width:2px;stroke:#eee}
.chart .line{fill:none;stroke-width:3}
.chart .fill{stroke-width:0}
.valShow text,.pie text{fill:#fed;opacity:.8;font-size:10px}
.pie text{font-size:15px}
.chartInfo g{opacity:0}
.chartInfo g:hover{opacity:1}
.chartInfo .textBg{fill:#000;opacity:.9}
.chartInfo text{text-anchor:middle;fill:#887}
.chartInfo g rect:nth-child(even){fill:#eee;opacity:.8;stroke:#eed;stroke-width:1}
.pieBg{fill:#eee}
]]></style>
</defs>";    
    
    echo "\n" . '  <rect width="'. $w .'" height="'. $h .'" class="chart-bg"></rect>';
    echo "\n" . '  <rect x="'. $x1 .'" y="'. $y1 .'" width="'. $x2 .'" height="'. $y2 .'" class="chart-box"></rect>';
    $is_pie = ('pie' === $chart);
    if (!$is_pie) {
        $is_bar = ('bar' === substr($chart, 0, 3));
        $is_barV = ('barV' === $chart);
        cms_chart_axisX($data, $x1, $y1, $x2, $y2, $xVal, $xSkip, $xFormat, $is_bar, $xAngle, $yUnit, $yFormat, $color, $mouseInfo);
        cms_chart_axisY($data, $x1, $y1, $x2, $y2, $yVal, $yUnit, $yFormat, $is_barV);
        $zoom = $y2 / abs($yVal[0] - end($yVal));
        $init['xVal'] = $xVal;
        $init['yVal'] = $yVal;
    }
    cms_chart_title($y1, $y2, $w, $title, $titleAlign, $xTitle, $xTitleAlign, $yTitle, $yTitleAlign, $is_pie);

    echo "\n  <!-- the chart -->\n" . '  <g transform="translate('. "$x1,$y1" .')">';
    if ('line' == $chart) cms_chart_line($data, $color, $zoom, $xVal, $yVal[0], $valShow, $valAngle, $yFormat);
    echo "\n  </g>";
    echo "\n</svg>";
}

function cms_chart_data($data, $fmt, $sort, $xKey, $xMin, $xMax, $sepRow, $sepCol, $ySum, $is_barS) {
    $fmt3 = substr($fmt, 0, 3);
    if ('sql' === $fmt3) {
        $fmt = substr($fmt, 4);
        $data = cms_arr($data, ('sxx' === $fmt || 'xss' === $fmt ? 1 : 2));
    } elseif ('str' === $fmt3) {
        $fmt = substr($fmt, 4);
        $data = cms_chart_data_str($data, $fmt, $sepRow, $sepCol);
    } elseif ('jso' === $fmt3) {
        $data = json_decode($data, 1);
    }
    $num = count($data);
    if (!$num) return;
    if (!is_array(reset($data))) {
        $data = array($data); // only 2 cols
        $num = 1;
    } elseif ('xss' === $fmt || 'xsy' === $fmt) {
        foreach ($data as $x => $arr) {
            foreach ($arr as $s => $y) $sxy[$s][$x] = $y;
        }
        $data = $sxy;
    }
    $keys = array(); // x labels
    foreach ($data as $s => $arr) {
        foreach ($arr as $x => $y) {
            if (!in_array($x, $keys)) $keys[] = $x;
            if (is_array($y)) $data[$s][$x] = reset($y); // clean dirty data
        }
    }
    if (in_array($xKey, array('year','month','week','day','hour'))) {
        $keys = cms_chart_data_ymdH($xKey, $keys, $xMin, $xMax);
    } elseif ('x' === $sort) {
        sort($keys);
    }
    if ('y' === $sort && $num > 1) ksort($data);
    foreach ($data as $k => $arr) {
        foreach ($keys as $k2) $res[$k][$k2] = isset($arr[$k2]) ? $arr[$k2] + 0 : 0;
    }
    if ('y' === $sort && 1 == $num) { // if pie make sure only 1 array
        $pie = reset($res);
        arsort($pie);
        $res = array($pie);
    }
    if ($ySum) {
        foreach ($res as $k => $arr) {
            $j = $hold = 0;
            foreach ($arr as $k2 => $v) {
                if ($j++) $res[$k][$k2] += $hold;
                $hold += $v;
            }
        }
    }
    if ($is_barS) {
        foreach ($res as $k => $arr) $sortS[$k] = array_sum($arr);
        array_multisort($sortS, SORT_DESC, $res);
    }
    return $res;
}

function cms_chart_data_ymdH($xKey, $keys, $xMin, $xMax) {
    if (!$xMin) $xMin = min($keys);
    if (!$xMax) $xMax = max($keys);
    $y1 = substr($xMin, 0, 4); $y2 = substr($xMax, 0, 4);
    if ('year' === $xKey) return range($y1, $y2);
    $keys = array();
    if ('week' === $xKey) {
        $xMin = substr($xMin, 0, 6); $xMax = substr($xMax, 0, 6);
        for ($Y = $y1; $Y <= $y2; $Y++) { for ($W = 1; $W < 54; $W++) {
            $yw = $Y . str_pad($W, 2, 0, STR_PAD_LEFT);
            if ($yw >= $xMin && $yw <= $xMax) $keys[] = $yw;
        }} // year week
        return $keys;
    }
    $m1 = substr($xMin, 0, 7); $d1 = substr($xMin, 0, 10);
    $m2 = substr($xMax, 0, 7); $d2 = substr($xMax, 0, 10);
    for ($Y = $y1; $Y <= $y2; $Y++) { for ($M = 1; $M < 13; $M++) {
        $ym = $Y .'-'. str_pad($M, 2, 0, STR_PAD_LEFT);
        if ($ym >= $m1 && $ym <= $m2) {
            if ('month' === $xKey) $keys[] = $ym;
            else { for ($D = 1; $D < 32; $D++) {
                $ymd = $ym .'-'. str_pad($D, 2, 0, STR_PAD_LEFT);
                if (checkdate($M, $D, $Y) && $ymd >= $d1 && $ymd <= $d2) {
                    if ('day' === $xKey) $keys[] = $ymd;
                    else { for ($H = 0; $H < 24; $H++) {
                        $keys[] = $ymd .' '. str_pad($H, 2, 0, STR_PAD_LEFT);
                    }} // hour
                } // valid day
            }} // day
        } // valid m
    }} // y m
    return $keys;
}
function cms_chart_init($arr, $data, $wFix = 0) {
    $num = count($data);
    $xNum = count(reset($data));
    if (!$arr['xSkip'] && $arr['xSkipMax'] > 0) $arr['xSkip'] = floor($xNum / $arr['xSkipMax']);
    if (!$arr['chart'] || !in_array($arr['chart'], array('line','pie','barV','barS'))) $arr['chart'] = 'bar';
    $is_pie = ('pie' === $arr['chart']);
    $arr['gapL'] += 9; // default box margin 9px each side
    $arr['gapT'] += 9;
    $arr['gapR'] += 9;
    $arr['gapB'] += 9;
    if ($arr['title']) $arr['gapT'] += 15;
    if ($arr['xTitle']) $arr['gapB'] += 15;
    if ($arr['yTitle']) $arr['gapL'] += 15;
    if (!strlen($arr['legend'])) $arr['legend'] = ($num > 1 || $is_pie) ? 'R' : '0';
    if ($arr['legendW'] < 1) $arr['legendW'] = 80;
    if ($arr['legend']) { // 0 T B R
        if ('T' === $arr['legend']) $arr['gapT'] += 15;
        elseif ('B' === $arr['legend']) $arr['gapB'] += 15;
        elseif ('L' === $arr['legend']) $arr['gapL'] += $arr['legendW'];
        else $arr['gapR'] += $arr['legendW'];
    }
    if ($is_pie) {
        if ($arr['yTitle']) $arr['gapL'] += 3;
        if ('L' === $arr['legend']) $arr['gapL'] += 51;
        elseif ('R' === $arr['legend']) $arr['gapR'] += 51;
    } else {
        $arr['gapL'] += 51; // default yLabel
        $arr['gapB'] += 16; // default xLabel
    }
    $arr['x1'] = $arr['gapL'];
    $arr['y1'] = $arr['gapT'];
    if ($arr['x1'] < 0) $arr['x1'] = 0;
    if ($arr['y1'] < 0) $arr['y1'] = 0;

    if (!$is_pie && $wFix) {
        $arr['x2'] = 10 * $num * $xNum + $xNum + 1;
        $arr['w'] = $arr['x1'] + $arr['x2'] + $arr['gapR'];
    } else {
        if ($arr['w'] < 1) $arr['w'] = 480;
        $arr['x2'] = $arr['w'] - $arr['x1'] - $arr['gapR'];
        if ($arr['x2'] > $arr['w'] || $arr['x2'] < $arr['x1']) $arr['x2'] = $arr['w'];
    }
    if ($arr['h'] < 1) $arr['h'] = 250;
    if ($arr['h'] > $arr['w']) $arr['h'] = $arr['w'];
    if ($is_pie) {
        $arr['y2'] = $arr['x2'];
        $arr['h'] = $arr['y1'] + $arr['y2'] + $arr['gapB']; // pie h auto calculated
    } else {
        $arr['y2'] = $arr['h'] - $arr['y1'] - $arr['gapB'];
        if ($arr['y2'] > $arr['h'] || $arr['y2'] < $arr['y1']) $arr['y2'] = $arr['h'];
    }
    return $arr;
}
function cms_chart_color($init) {
    // the following are default 11 colors
    $defa = array('d9534f', 'f0ad4e', '5bc0de', '5cb85c', '337ab7', 'f26522', '754c24', 'd9ce00', '0e2e42', 'ce1797','672d8b');
    // add colors at front
    if (isset($init['color']) && !is_array($init['color'])) {
        $col = explode(',', $init['color']);
        if (is_array($col)) {
            foreach ($col as $c) {
                $c = trim(substr(trim($c), 0, 6));
                if (strlen($c) > 2) $color[] = $c;
            }
        }
    }
    // del colors
    if (isset($init['colorDel'])) {
        $col = explode(',', $init['colorDel']);
        if (is_array($col)) {
            foreach ($col as $c) {
                unset($defa[ceil($c)]);
            }
        }
    }
    foreach ($defa as $c) $color[] = $c;
    // add colors at end
    if (isset($init['colorAdd'])) {
        $col = explode(',', $init['colorAdd']);
        if (is_array($col)) {
            foreach ($col as $c) {
                $c = trim(substr(trim($c), 0, 6));
                if (strlen($c) > 2) $color[] = $c;
            }
        }
    }
    return $color;
}
function cms_chart_axisX($data, $x1, $y1, $x2, $y2, &$xVal, $xSkip, $xFormat, $is_bar, $xAngle, $yUnit, $yFormat, $color, &$mouseInfo) {
    $xNum = count(reset($data));
    if ($is_bar) {
        $xDiv = ($x2 - $xNum - 1) / $xNum;
        $xVal[0] = round($xDiv / 2 + 1, 5);
        $xDiv++;
        $xLeft = 0;
    } else {
        $xDiv = $x2 / ($xNum - 1);
        $xVal[0] = 0;
        $xLeft = $xDiv / 2;
    }
    $xLabel = array_keys(reset($data));
    if ($xAngle) {
        $angle1 = '<g transform="translate(5) rotate('. $xAngle .')">';// best for 45
        $angle2 = '</g>';
        $angleCSS = ' xAngle';
    } else {
        $angle1 = $angle2 = $angleCSS = '';
    }
    echo "\n" . '  <g class="chart-tick axisX'. $angleCSS .'" transform="translate('. $x1 .','. ($y1 + $y2) .')">';
    if ($yUnit) $yUnit = ' ' . $yUnit;
    $cNum = count($color);
    $j = 0;
    foreach ($data as $k => $arr) {
        $i = 0;
        foreach ($arr as $k2 => $v) {
            if ($i) $xVal[$i] = round($xVal[$i - 1] + $xDiv, 5);
            if (!isset($labelTxt[$i])) $labelTxt[$i] = '<tspan x="'. $xVal[$i] .'" dy="15">'. $k2 .'</tspan>';
            $labelTxt[$i] .= '<tspan x="'. $xVal[$i] .'" dy="15" fill="#'. $color[$j % $cNum] .'">'. ($k ? $k .' : ' : '') . cms_chart_axis_format($v, $yFormat) . $yUnit .'</tspan>';
            $i++;
        }
        $j++;
    }
    $mouseInfo = "\n" .'  <g class="chartInfo" transform="translate('. $x1 .','. $y1 .')">';
    $mouseInfoH= (count($data) + 1) * 15 + 7;
    for ($i = 0, $skip = 0; $i < $xNum; $i++) {
        $rect = '<rect x="'. round($i * $xDiv - $xLeft, 5) .'" width="'. round($xDiv, 5) .'" ';
        $mouseInfo .= "\n" .'    <g>'. $rect .'height="'. $y2 .'" fill="#000" opacity="0"/>'. $rect .'height="'. $mouseInfoH .'"/><text>'. $labelTxt[$i] .'</text></g>';
        if (!$xSkip || ($xSkip > 0 && $i == ($xSkip + 1) * $skip)
        || ($xSkip < 0 && !(substr($xLabel[$i], -2) % $xSkip))) {
            $skip++;
            echo "\n" . '    <g transform="translate('. $xVal[$i] .')"><line y2="-'. $y2 .'"></line>';
            echo $angle1 . '<text y="3" dy=".71em">'. cms_chart_axis_format($xLabel[$i], $xFormat) .'</text>' . $angle2;
            echo '</g>';
        }
    }
    $mouseInfo .= "\n  </g>";
    echo "\n" . '  </g>';
}
function cms_chart_axisY($data, $x1, $y1, $x2, $y2, &$yVal, $yUnit, $yFormat, $is_barV) {
    $max_min = reset($data);
    $max = $min = reset($max_min);
    if ($is_barV) {
        foreach ($data as $arr) {
            foreach ($arr as $k => $v) $TTL[0][$k] = $v + (isset($TTL[0][$k]) ? $TTL[0][$k] : 0);
        }
    } else $TTL = $data;
    foreach ($TTL as $arr) {
        $min = min(min($arr), $min);
        $max = max(max($arr), $max);
    }
    if ($max < 0 && $min < 0) $ttl = $min;
    elseif ($max < 0 || $min < 0) $ttl = $max - $min;
    else $ttl = $max;
    if ($ttl < 0) $ttl = abs($ttl);
    $zoom = pow(10, floor(log10($ttl)));
    $ttl /= $zoom;
    if ($ttl == 1) $step = 0.2;
    elseif ($ttl > 6) $step = 2;
    elseif ($ttl > 5) $step = 1.2;
    elseif ($ttl > 4) $step = 1;
    elseif ($ttl > 3) $step = 0.8;
    elseif ($ttl > 2) $step = 0.6;
    else $step = 0.4;
    if ($max <= 0 && $min < 0) {
        for ($i = 0; $i < 6; $i++) $yVal[] = 0 - $i * $zoom * $step;
    } else {
        for ($i = 5; $i >- 1; $i--) $yVal[] = $i * $zoom * $step;
        if ($max <= 0 || $min < 0) cms_chart_axisY_audit($max, $min, $step, $zoom, $yVal);
    }
    if ($max > 0 && $yVal[1] >= $max) array_shift($yVal);
    elseif ($min < 0 && $yVal[4] <= $min) array_pop($yVal);
    $step = count($yVal);
    if ($min < $yVal[$step-1]) $yVal[] = $yVal[$step-1] - abs($yVal[$step-2] - $yVal[$step-3]);
    $step = $y2 / (count($yVal) - 1);

    echo "\n" . '  <g class="chart-tick axisY" transform="translate('. "$x1,$y1" .')">';
    $yDiv = count($yVal);
    $last_not_zero = end($yVal);
    if ($yUnit) $yUnit = ' ' . $yUnit;
    for ($i = 0; $i < $yDiv; $i++) {
        echo "\n" . '    <g transform="translate(0,'. $i * $step .')"><line x2="'. $x2 .'"></line>';
        if ($yVal[$i] || $last_not_zero) {
            echo '<text x="-3" dy=".32em">'. cms_chart_axis_format($yVal[$i], $yFormat) . $yUnit . '</text>';
        }
        echo '</g>';
    }
    echo "\n" . '  </g>';
}
function cms_chart_axisY_audit($max, $min, $step, $zoom, &$yVal, $count = 0) {
    if ($count > 5) return;
    if ($yVal[1] > $max && $yVal[5] > $min) {
        $yVal[] = $yVal[5] - $zoom * $step;
        array_shift($yVal);
        cms_chart_axisY_audit($max, $min, $step, $zoom, $yVal, $count++);
    }
}
function cms_chart_axis_format($v, $format) {
    if (!$format) return $v;
    $format = explode('|', $format, 4); // eg substr|6 or format|0|.|, or data|M
    if ('format' == $format[0]) return number_format($v, isset($format[1]) ? ceil($format[1]) : 0, isset($format[2]) ? $format[2] : null, isset($format[3]) ? $format[3] : null);
    if ('substr' == $format[0]){
        if (isset($format[2])) return substr($v, ceil($format[1]), ceil($format[2]));
        return substr($v, isset($format[1]) ? ceil($format[1]) : 0);
    }
    if ('date' == $format[0]) return date($format[1], strtotime($v));
    return $v;
}
function cms_chart_title($y1, $y2, $w, $title, $titleAlign, $xTitle, $xTitleAlign, $yTitle, $yTitleAlign, $is_pie) {
    if ($title) {
        echo "\n" . '  <text y="15" x="';
        if (1 == $titleAlign) echo 5 . '"'; // left
        elseif (3 == $titleAlign) echo $w - 5 . '" text-anchor="end"'; // right
        else echo $w / 2 . '" text-anchor="middle"'; // default center
        echo '>'. $title .'</text>';
    }
    $y = $y1 + $y2;
    if ($xTitle) {
        echo "\n".'  <text y="'. ($y + ($is_pie ? 15 : 37)) .'" x="';
        if (1 == $xTitleAlign) echo 5 . '"'; // left
        elseif (3 == $xTitleAlign) echo $w - 5 . '" text-anchor="end"'; // right
        else echo $w / 2 . '" text-anchor="middle"'; // default center
        echo '>'. $xTitle .'</text>';
    }
    if ($yTitle) {
        echo "\n".'  <g transform="translate(15,'. $y .')"><text x="';
        if (1 == $yTitleAlign) echo 0 . '"'; // left
        elseif (3 == $yTitleAlign) echo $y2 . '" text-anchor="end"'; // right
        else echo $y2 / 2 . '" text-anchor="middle"'; // default center
        echo ' transform="rotate(-90)">'. $yTitle .'</text></g>';
    }
}

function cms_chart_val($valAngle, $x, $y, $xMove, $yMove, $v) {
    if (!$valAngle) return '<text x="'. $x .'" y="'. ($y + 10) .'" text-anchor="middle">'. $v .'</text>';
    return "\n" .'      <g transform="translate('. ($x + $xMove) .','. ($y + $yMove) .') rotate('. $valAngle .')"><text>'. $v .'</text></g>';
}
function cms_chart_line($data, $color, $zoom, $xVal, $yVal0, $valShow, $valAngle, $yFormat) {
    $i = 0;
    $cNum = count($color);
    foreach ($data as $arr) {
        $j = 0;
        $line = $dot = '';
        foreach ($arr as $v) {
            $x = round($xVal[$j++], 5);
            $y = round(($yVal0 - $v) * $zoom, 5);
            $line .= ' '. $x .','. $y;
            //$dot .= "\n" . '      <circle cx="'. $x .'" cy="'. $y .'" r="3"/>';//removed: <title>'. $v .'</title>
            if ($valShow) $dot .= cms_chart_val($valAngle, $x, $y - 15, 0, 0, cms_chart_axis_format($v, $yFormat));
        }
        echo "\n" . '    <g fill="#'. $color[$i % $cNum] .'">';
        echo "\n" . '      <path d="M'. substr($line, 1) .'" class="line" stroke="#'. $color[$i++ % $cNum] .'"/>';
        //echo $dot;
        echo "\n" . '    </g>';
    }
}
