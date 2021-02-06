<?php

namespace Abcsoft\HTTP\Language;

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
        if(!defined("ABC_HTTP")){
         
            define("ABC_HTTP", true);
         
            define("ABC_HTTP_INVALID_STREAM",           
            "Invalid stream provided.
            <br />\n<span style=\"color:#f98a8a\">
            (Указан недопустимый поток)</span><br />\n");
            
            define("ABC_HTTP_INVALID_PROTOCOL",         
            "Invalid HTTP version.
            <br />\n<span style=\"color:#f98a8a\">
            (Невалидная версия протокола HTTP)</span><br />\n");
            
            define("ABC_HTTP_INVALID_TARGET",           
            "Invalid request target provided; cannot contain whitespace
            <br />\n<span style=\"color:#f98a8a\">
            (Некорректная цель запроса)</span><br />\n"); 
    
            define('ABC_HTTP_INVALID_BODY',             
            "Request body media type parser return value must be an array, an object, or null        
            <br />\n<span style=\"color:#f98a8a\">
            (Некорректная цель запроса)</span><br />\n"); 
            
            define("ABC_HTTP_NO_METHOD",                
            "Method %s not implemented in HTTP system 
            <br />\n<span style=\"color:#f98a8a\">
            (Метод %s не реализован в системе HTTP.)</span><br />\n");
            
            define("ABC_HTTP_NO_HEADER",                
            " - There is no such header. 
            <br />\n<span style=\"color:#f98a8a\">
            (Нет такого заголовка.)</span><br />\n");
            
            define("ABC_HTTP_VALUE_NO_STRING",          
            "Header must be a string or array of strings
            <br />\n<span style=\"color:#f98a8a\">
            (Заголовок должен быть строкой или массивом строк)</span><br />\n");    
            
            define("ABC_HTTP_INVALID_HEADER_NAME",      
            "Invalid header name. <br />\n<span style=\"color:#f98a8a\">
            (Невалидное имя заголовка.)</span><br />\n");
            
            define("ABC_HTTP_INVALID_HEADER_VALUE",     
            "Invalid header. 
            <br />\n<span style=\"color:#f98a8a\">
            (Невалидный заголовок.)</span><br />\n");
            
            define("ABC_HTTP_NO_RESOURCE",              
            " is not a resource. 
            <br />\n<span style=\"color:#f98a8a\">
            (Аргумент не является ресурсом.)</span><br />\n");
            
            define("ABC_HTTP_NO_REWIND",                
            "Could not rewind stream
            <br />\n<span style=\"color:#f98a8a\">
            (Не удалось сбросить курсор потока)</span><br />\n");
            
            define("ABC_HTTP_NO_POINTER",               
            "Could not get the position of the pointer in stream
            <br />\n<span style=\"color:#f98a8a\">
            (Не удалось получить позицию указателя в потоке)</span><br />\n"); 
            
            define("ABC_HTTP_NO_WRITE",                 
            "Could not write to stream
            <br />\n<span style=\"color:#f98a8a\">
            (Не удалось запиисать в поток)</span><br />\n"); 
            
            define("ABC_HTTP_NO_READ",                  
            "Could not read from stream
            <br />\n<span style=\"color:#f98a8a\">
            (Не удалось прочитать из потока)</span><br />\n");
            
            define("ABC_HTTP_NO_CONTENT",               
            "Could not get contents of stream
            <br />\n<span style=\"color:#f98a8a\">
            (Не удалось пролучить контент из потока)</span><br />\n");
            
            define("ABC_HTTP_PATH_NO_STRING",           
            "Path must be a string
            <br />\n<span style=\"color:#f98a8a\">
            (Path должен быть строкой)</span><br />\n");
            
            define("ABC_HTTP_URI_NO_STRING",            
            "Uri must be a string
            <br />\n<span style=\"color:#f98a8a\">
            (URI должен быть строкой)</span><br />\n");
            
            define("ABC_HTTP_INVALID_URI",              
            "The invalid Uri
            <br />\n<span style=\"color:#f98a8a\">
            (Невалидный Uri)</span><br />\n"); 
            
            define("ABC_HTTP_SCHEME_NO_STRING",         
            "Uri scheme must be a string
            <br />\n<span style=\"color:#f98a8a\">
            (URI схема должна быть строкой)</span><br />\n");   
            
            define("ABC_HTTP_INVALID_SCHEME",           
            "Uri scheme must be one of: \"\", \"https\", \"http\"
            <br />\n<span style=\"color:#f98a8a\">
            (URI схема должна быть одним из \"\", \"https\", \"http\")</span><br />\n");
            
            define("ABC_HTTP_EMPTY_ARGYMENTS",          
            "Uri fragment must be a string
            <br />\n<span style=\"color:#f98a8a\">
            (Фрагмент должен быть строкой)</span><br />\n");   
            
            define("ABC_HTTP_EMPTY_FILE_PATH",          
            "No path is specified for moving the file
            <br />\n<span style=\"color:#f98a8a\">
            (Не указан путь для перемещения файла)</span><br />\n"); 
            
            define("ABC_HTTP_CANNOT_MOVE_FILE",         
            "Cannot move file
            <br />\n<span style=\"color:#f98a8a\">
            (Не удалось переместить файл)</span><br />\n"); 
            
            define("ABC_HTTP_ERROR_MOVED",              
            "Cannot retrieve stream after it has already been moved 
            <br />\n<span style=\"color:#f98a8a\">
            (Не удалось получить поток после его перемещения)</span><br />\n");
            
            define("ABC_HTTP_ERROR_FILE",               
            "Error occur#f98a8a while moving uploaded file 
            <br />\n<span style=\"color:#f98a8a\">
            (Ошибка перемещения файла)</span><br />\n");
            
            define("ABC_HTTP_URI_IS_FRAGMENT",          
            "Query string must not include a URI fragment
            <br />\n<span style=\"color:#f98a8a\">
            (Строка запроса не должна содержать #фрагмент)</span><br />\n");
            
            define("ABC_HTTP_INVALID_STATUS",          
            "Invalid status code. Must be an integer between 100 and 599, inclusive
            <br />\n<span style=\"color:#f98a8a\">
            (Неверный статус-код. Код дожен быть в промежутке между 100 и 599)</span><br />\n"); 
        }
    }
    
}
    
