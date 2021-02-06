<?php

namespace Abcsoft\SQL\SqlDebug\Lang;

/** 
 * Класс Ru
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su
 * @copyright © 2015
 * @license http://www.wtfpl.net/ 
 */   
class Ru
{
    public static function setConstants()
    {
        defined('ABC_SQL_SEQUENCE')      or define('ABC_SQL_SEQUENCE',            ' Operator sequence error <br /><span style="color:red">(ошибка последовательности методов)</span><br />');
        defined('ABC_SQL_DUBLE')         or define('ABC_SQL_DUBLE',               ' Repeat of operator <strong>%s</strong><br /><span style="color:red">(повтор оператора <strong>%s</strong> )</span><br />');
        defined('ABC_SQL_NO_CONDITIONS') or define('ABC_SQL_NO_CONDITIONS',       ' No conditions are specified <br /><span style="color:red">(не задано имя поля или значение)</span><br />');
        defined('ABC_SQL_COUNT_VALUES')  or define('ABC_SQL_COUNT_VALUES',        ' Insufficient values for the operator <br /><span style="color:red">(недостаточно значений для оператора)</span><br />');
        defined('ABC_SQL_INVALID_CONDITIONS') or define('ABC_SQL_INVALID_CONDITIONS',  ' Error in setting conditions <br /><span style="color:red">(ошибка в задании условий)</span><br />');
        defined('ABC_SQL_INVALID_VALUES') or define('ABC_SQL_INVALID_VALUES',      ' Error in setting values <br /><span style="color:red">(ошибка в задании значений)</span><br />');
        defined('ABC_SQL_INVALID_OPERATOR') or define('ABC_SQL_INVALID_OPERATOR',    ' Operator not supported <br /><span style="color:red">(оператор не поддерживается)</span><br />'); 
        defined('ABC_SQL_DISABLE') or define('ABC_SQL_DISABLE',             ' The request is generated and executed. Changes are not allowed. <br /><span style="color:red">(Запрос сформирован и выполнен. Изменения не допустимы.)</span><br />');
        defined('ABC_SQL_NO_TABLE') or define('ABC_SQL_NO_TABLE',             ' Table <strong>%s</strong> not found in database <br /><span style="color:red">(Таблица <strong>%s</strong> не найдена в базе данных.)</span><br />');
    }


    protected static function errorReportings() 
    {
        return [
'Incorrect usage of (.+?) and (.+)' => 'Incorrect usage of <strong>$1</strong> and <strong>$2</strong>
<br /><span class="translate">(Неверное использование <strong>$1</strong> и <strong>$2</strong>)</span><br />',
'Incorrect datetime value: \'(.+?)\' for column \'(.+?)\' at row (\d+)' => 'Incorrect datetime value: \'$1\' for column \'$2\' at row $3
<br /><span class="translate">(Некорректное значение даты и времени: <strong>$1</strong> для столбца <strong>$2</strong> в строке <strong>$3</strong>)</span><br />',
'Base Table or view not found: (\d*?)(.+)' => ' $2 ',        
'Table(.+)doesn\'t exist' => 'Table$1doesn\'t exist<br /><span class="translate">(Таблица<strong>$1</strong>не существует)</span><br />',
'You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near(.+?)at line (.+)' => 'You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near$1at line <strong>$2</strong><br /><span class="translate">(Ошибка SQL синтаксиса. <br />Обратитесь к мануалу, соответствующему Вашей версии MySQL сервера, чтобы использовать верно строку <strong>$1</strong> на линии <strong>$2</strong>)</span><br />',
'Field \'(.+?)\' doesn\'t have a default value' => 'Field \'<strong>$1</strong>\' doesn\'t have a default value<br /><span class="translate">Поле <strong>\'<strong>$1</strong>\'</strong> не имеет значения по умолчанию</span><br />',
'Query was empty' => 'Query was empty<br /><span class="translate">Пустой запрос</span><br />',
'Column (.+?) in where clause is ambiguous' => 'Column <strong>$1</strong> in where clause is ambiguous<br /><span class="translate">Столбец <strong>$1</strong> неоднозначен</span><br />',
'Subquery returns more than (\d+) row' => 'Subquery returns more than $1 row<br /><span class="translate">Подзапрос вернул рядов больше, чем $1</span><br />',
'Not unique table/alias: (.+)' => 'Not unique table/alias: <strong>$1</strong><br /><span class="translate">Не уникальны таблица или алиас <strong>$1</strong></span><br />',
'Unknown column (.+?) in \'(.+?) clause\'' => 'Unknown column <strong>$1</strong> in <strong>$2</strong> clause<br /><span class="translate">Неизвестный столбец <strong>$1</strong> в клаузе <strong>$2</strong></span><br />',
'The used (.+?) statements have a different number of columns' => 'The used <strong>$1</strong> statements have a different number of columns<br /><span class="translate">Используемые операторы <strong>$1</strong> имеют различное количество столбцов</span><br />',
'Unknown column (.+?) in \'(.+)\'' => 'Unknown column <strong>$1</strong> in $2<br /><span class="translate">Неизвестный столбец <strong>$1</strong> </span><br />',

        ];
    }

    public static function translate($message) 
    {
        $reporting = self::errorReportings();
        $patterns = [];
     
        foreach ($reporting as $key => $value) {
            $patterns[] = '#'. $key .'#s';
        }
        return preg_replace($patterns, array_values($reporting), $message);
    }    
    
}
