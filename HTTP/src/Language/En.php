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
class En
{
    
    public static function set() 
    { 
        if(!defined("ABC_HTTP")){
         
            define("ABC_HTTP", true); 
            
            define('ABC_HTTP_INVALID_STREAM',           'Invalid stream provided.');
            define('ABC_HTTP_INVALID_PROTOCOL',         'Invalid HTTP version. ');
            define('ABC_HTTP_INVALID_TARGET',           'Invalid request target provided; cannot contain whitespace ');
            define('ABC_HTTP_INVALID_BODY',             'Request body media type parser return value must be an array, an object, or null');        
            define('ABC_HTTP_NO_HEADER',                ' - There is no such header. ');
            define('ABC_HTTP_VALUE_NO_STRING',          'Header must be a string or array of strings ');    
            define('ABC_HTTP_INVALID_HEADER_NAME',      'Invalid header name. ');
            define('ABC_HTTP_INVALID_HEADER_VALUE',     'Invalid header. ');
            define('ABC_HTTP_NO_RESOURCE',              ' is not a resource. ');
            define('ABC_HTTP_NO_REWIND',                'Could not rewind stream ');
            define('ABC_HTTP_NO_POINTER',               'Could not get the position of the pointer in stream '); 
            define('ABC_HTTP_NO_WRITE',                 'Could not write to stream '); 
            define('ABC_HTTP_NO_READ',                  'Could not read from stream ');
            define('ABC_HTTP_NO_CONTENT',               'Could not get contents of stream ');
            define('ABC_HTTP_PATH_NO_STRING',           'Path must be a string ');
            define('ABC_HTTP_URI_NO_STRING',            'Uri must be a string ');
            define('ABC_HTTP_INVALID_URI',              'The invalid Uri '); 
            define('ABC_HTTP_SCHEME_NO_STRING',         'Uri scheme must be a string ');   
            define('ABC_HTTP_INVALID_SCHEME',           'Uri scheme must be one of: "", "https", "http" '); 
            define('ABC_HTTP_EMPTY_ARGYMENTS',          'Uri fragment must be a string ');   
            define('ABC_HTTP_EMPTY_FILE_PATH',          'No path is specified for moving the file '); 
            define('ABC_HTTP_CANNOT_MOVE_FILE',         'Cannot move file '); 
            define('ABC_HTTP_ERROR_MOVED',              'Cannot retrieve stream after it has already been moved ');
            define('ABC_HTTP_ERROR_FILE',               'Error occurred while moving uploaded file ');
            define('ABC_HTTP_URI_IS_FRAGMENT',          'Query string must not include a URI fragment ');
            define('ABC_HTTP_INVALID_STATUS',          'Invalid status code. Must be an integer between 100 and 599, inclusive ');
         }
    }
}
















