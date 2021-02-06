<?php

namespace Abcsoft\SQL\SqlDebug\Lang;


/** 
 * Класс En
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su
 * @copyright © 2015
 * @license http://www.wtfpl.net/ 
 */   
class En
{
    public static function setConstants()
    {
        define('ABC_COMMAND_SELECT',          ' Syntax error for the SELECT statement <br />');
        define('ABC_SQL_SEQUENCE',            ' Operator sequence error <br />');
        define('ABC_SQL_DUBLE',               ' Repeat of operator <strong>%s</strong> <br />');
        define('ABC_SQL_NO_CONDITIONS',       ' No conditions are specified <br />');
        define('ABC_SQL_COUNT_VALUES',        ' Insufficient values for the operator <br />');
        define('ABC_SQL_INVALID_CONDITIONS',  ' Error in setting conditions <br />');
        define('ABC_SQL_INVALID_VALUES',      ' Error in setting values <br />');
        define('ABC_SQL_INVALID_OPERATOR',    ' Operator not supported <br />');  
    }


    protected static function errorReportings() 
    {
        return [
                    'Base Table or view not found: (\d*?)(.+)' => ' $2 ',        
                    'Table(.+)doesn\'t exist' => 'Table$1doesn\'t exist<br />',
'You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near(.+)at line (.+)' => 'You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near$1at line $2<br />',

        ];
    }

    public static function translate($message) 
    {
        $reporting = self::errorReportings();
        $patterns = [];
     
        foreach ($reporting as $key => $value) {
            $patterns[] = '#'. $key .'#iu';
        }
        return preg_replace($patterns, array_values($reporting), $message);
    }    
    
}
