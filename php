<?php
// мы будем делать нашу собственную обработку ошибок
error_reporting(0);

// пользовательская функция для обработки ошибок
function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars)
{
    // временная метка возникновения ошибки
    $dt = date("Y-m-d H:i:s (T)");

    // определим ассоциативный массив соответствия всех
    // констант уровней ошибок с их названиями, хотя
    // в действительности мы будем рассматривать только
    // следующие типы: E_WARNING, E_NOTICE, E_USER_ERROR,
    // E_USER_WARNING и E_USER_NOTICE
    $errortype = array (
                E_ERROR              => 'Ошибка',
                E_WARNING            => 'Предупреждение',
                E_PARSE              => 'Ошибка разбора исходного кода',
                E_NOTICE             => 'Уведомление',
                E_CORE_ERROR         => 'Ошибка ядра',
                E_CORE_WARNING       => 'Предупреждение ядра',
                E_COMPILE_ERROR      => 'Ошибка на этапе компиляции',
                E_COMPILE_WARNING    => 'Предупреждение на этапе компиляции',
                E_USER_ERROR         => 'Пользовательская ошибка',
                E_USER_WARNING       => 'Пользовательское предупреждение',
                E_USER_NOTICE        => 'Пользовательское уведомление',
                E_STRICT             => 'Уведомление времени выполнения',
                E_RECOVERABLE_ERROR  => 'Отлавливаемая фатальная ошибка'
                );
    // определим набор типов ошибок, для которых будет сохранён стек переменных
    $user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);

    $err = "<errorentry>\n";
    $err .= "\t<datetime>" . $dt . "</datetime>\n";
    $err .= "\t<errornum>" . $errno . "</errornum>\n";
    $err .= "\t<errortype>" . $errortype[$errno] . "</errortype>\n";
    $err .= "\t<errormsg>" . $errmsg . "</errormsg>\n";
    $err .= "\t<scriptname>" . $filename . "</scriptname>\n";
    $err .= "\t<scriptlinenum>" . $linenum . "</scriptlinenum>\n";

    if (in_array($errno, $user_errors)) {
        $err .= "\t<vartrace>" . wddx_serialize_value($vars, "Переменные") . "</vartrace>\n";
    }
    $err .= "</errorentry>\n\n";

    // для тестирования
    // echo $err;

    // сохраняем в журнал ошибок, а если произошла пользовательская критическая ошибка, то отправляем письмо
    error_log($err, 3, "/usr/local/php4/error.log");
    if ($errno == E_USER_ERROR) {
        mail("phpdev@example.com", "Пользовательская критическая ошибка", $err);
    }
}


function distance($vect1, $vect2)
{
    if (!is_array($vect1) || !is_array($vect2)) {
        trigger_error("Некорректные параметры функции, ожидаются массивы в качестве параметров", E_USER_ERROR);
        return NULL;
    }

    if (count($vect1) != count($vect2)) {
        trigger_error("Векторы должны быть одинаковой размерности", E_USER_ERROR);
        return NULL;
    }

    for ($i=0; $i<count($vect1); $i++) {
        $c1 = $vect1[$i]; $c2 = $vect2[$i];
        $d = 0.0;
        if (!is_numeric($c1)) {
            trigger_error("Координата $i в векторе 1 не является числом, будет использовать ноль",
                            E_USER_WARNING);
            $c1 = 0.0;
        }
        if (!is_numeric($c2)) {
            trigger_error("Координата $i в векторе 2 не является числом, будет использовать ноль",
                            E_USER_WARNING);
            $c2 = 0.0;
        }
        $d += $c2*$c2 - $c1*$c1;
    }
    return sqrt($d);
}

$old_error_handler = set_error_handler("userErrorHandler");

// использование неопределённой константы, будет генерироваться предупреждение
$t = I_AM_NOT_DEFINED;

// определим несколько "векторов"
$a = array(2, 3, "foo");
$b = array(5.5, 4.3, -1.6);
$c = array(1, -3);

// генерируем пользовательскую ошибку
$t1 = distance($c, $b) . "\n";

// генерируем ещё одну пользовательскую ошибку
$t2 = distance($b, "я не массив") . "\n";

// генерируем пользовательское предупреждение
$t3 = distance($a, $b) . "\n";

?>
