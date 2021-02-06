<?php

namespace Abcsoft\SQL\Language;

/** 
 * Класс En
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su
 * @copyright © 2015
 * @license http://www.wtfpl.net/ 
 */   
class Ru
{
    public static function set() 
    {
        if(!defined("ABC_SQL")){
         
            define("ABC_SQL", true);
            
            define("ABC_SQL_WRONG_CONNECTION",         
            " wrong data connection in the configuration file 
            <br />\n<span style=\"color:#f98a8a\">
            (неверные данные коннекта в конфигурационном файле)</span><br />\n");
            
            define("ABC_SQL_NO_SQL_DEBUGGER",          
            " SQL debugger is inactive. Set to true debug configuration. 
            <br />\n<span style=\"color:#f98a8a\">
            (SQL дебаггер не установлен. Установите настройку в конфигурационном файле)</span><br />\n"); 
            
            define("ABC_SQL_INVALID_MYSQLI_TYPE",      
            " Number of elements in type definition string doesn\"t match number of bind variables  
            <br />\n<span style=\"color:#f98a8a\">
            (количество элементов типа отличается от количества аргументов)</span><br />\n");
            
            define("ABC_SQL_NO_MYSQLI_TYPE",          
            " Unknown type of the parameter 
            <br />\n<span style=\"color:#f98a8a\">
            (неизвестный тип параметра)</span><br />\n");
            
            define("ABC_SQL_ERROR",                
            " Query build error  
            <br />\n<span style=\"color:#f98a8a\">
            (Ошибка построения запроса)</span><br />\n");
            
            define("ABC_SQL_NOT_ARGUMENTS",      
            " Too few arguments to method %s
            <br />\n<span style=\"color:#f98a8a\"> 
            (не все аргументы переданы в метод %s() )</span><br />");
            
            define("ABC_SQL_DISABLE",              
            " Blocked 
            <br />\n<span style=\"color:#f98a8a\">
            (Заблокировано)</span><br />\n"); 
            
            define("ABC_SQL_TRANSACTION_EXIST",        
            " There is already an active transaction 
            <br />\n<span style=\"color:#f98a8a\">
            (Уже есть активная транзакция)</span><br />\n");
            
            define("ABC_SQL_TRANSACTION_ERROR",       
            " Transaction error:  
            <br />\n<span style=\"color:#f98a8a\">
            Ошибка транзакции: </span><br />\n"); 
            
            define("ABC_SQL_NO_SUPPORT",               
            " This type of table is not supported by the debugger  
            <br />\n<span style=\"color:#f98a8a\">
            (Этот тип таблицы не поддерживается дебаггером)</span><br />\n");
            
            define("ABC_SQL_OTHER_OBJECT",             
            " An inappropriate object is used 
            <br />\n<span style=\"color:#f98a8a\">
            (Используется неподходящий объект)</span><br />\n"); 
            
            define("ABC_SQL_NO_METHOD",         
            " Method %s is not supported by the Query builder
            <br />\n<span style=\"color:#f98a8a\">
            (метод %s не поддерживается конструктором запросов)</span><br />\n");
            
            define("ABC_SQL_ERROR_BINDVALUES",        
            " The numbering of the array in the parameter of the <strong>bindValues()</strong> method must begin with 1
            <br />\n<span style=\"color:#f98a8a\">
            (Нумерация массива в параметре метода <strong>bindValues()</strong> должна начинаться с единицы)</span><br />\n");
            
            define("ABC_SQL_DBCOMAND_SERIALIZE",      
            " You can not serialize a query builder object
            <br />\n<span style=\"color:#f98a8a\">
            (Нельзя сериализовать объект конструктора запросов)</span><br />\n");
            
            define("ABC_SQL_INVALID_CONDITIONS",   
            " Conditions are set incorrectly
            <br />\n<span style=\"color:#f98a8a\">
            (Условия заданы некорректно)</span><br />\n");
            
            define("ABC_SQL_DUBLE",                
            " Methods usage error
            <br />\n<span style=\"color:#f98a8a\">
            (Ошибка использования методов)</span><br />\n"); 
            
            define("ABC_SQL_SEQUENCE",             
            " Method sequence error
            <br />\n<span style=\"color:#f98a8a\">
            (Ошибка последовательности методов)</span><br />\n");    
       /**
        * Active Record
        */ 
            define("ABC_SQL_FIND_SEQUENCE",            
            " Method <strong>%s</strong> can not be used in the context of <strong>::%s()</strong>.
            <br />\n<span style=\"color:#f98a8a\">
            (Метод <strong>%s</strong> не может быть использован в контексте метода <strong>::%s()</strong>.)</span><br />\n");
            
            define("ABC_SQL_INCORRECT_TYPE",          
            " Type <strong>%s</strong> is not correct.
            <br />\n<span style=\"color:#f98a8a\">
            (Тип <strong>%s</strong> некорректен.)</span><br />\n");
            
            define("ABC_SQL_INVALID_TYPE",            
            " Type <strong>%s</strong> is not supported Active Record. Use the type converter
            <br />\n<span style=\"color:#f98a8a\">
            (Тип <strong>%s</strong> не поддерживается системой. Воспользуйтесь преобразователем типов.)</span><br />\n");
            
            define("ABC_SQL_ATTRIBUTE_NOT_FOUND",      
            " The <strong>%s</strong> attribute is not in the model.
            <br />\n<span style=\"color:#f98a8a\">
            (Аттрибут <strong>%s</strong> отсутствует в модели.)</span><br />\n");
            
            define("ABC_SQL_JSON_ERROR",              
            " JSON decoding error 
            <br />\n<span style=\"color:#f98a8a\">
            (Ошибка декодирования JSON)</span><br />\n"); 
            
            define("ABC_SQL_NO_PRIMARY",              
            " No primary key 
            <br />\n<span style=\"color:#f98a8a\">
            (Нет первичного ключа)</span><br />\n");
            
            define("ABC_SQL_BANNED_ATTRIBUTE",       
            " Attribute <strong>%s</strong> is not allowed to bulk upload 
            <br />\n<span style=\"color:#f98a8a\">
            (Атрибут <strong>%s</strong>  не допущен к массовой загрузке.)</span><br />\n");
            
            define("ABC_SQL_MODEL_NO_SAVE",        
            " System failure. Updates are not accepted.
            <br />\n<span style=\"color:#f98a8a\">
            (Сбой системы. Обновления не приняты)</span><br />\n");          
        }
    }
    
}
    
