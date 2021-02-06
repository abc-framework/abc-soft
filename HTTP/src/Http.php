<?php
 
namespace Abcsoft\HTTP;

/** 
 * Класс Http
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author irbis-team.ru
 * @copyright © 2017
 * @license http://www.wtfpl.net/ 
 */   
class Http
{ 
    protected $env;

    public function __construct(array $env = null, $lang = 'Ru')
    {
        $this->env = $env ?? Environment::get();
        $language = 'Abcsoft\HTTP\Language\\'. $lang;
        $language::set();
    }

    /**
    * Возвращает дефолтный Request.
    *
    * @return object.
    */
    public function createRequest()
    {
        return new Request();
    }
    
    /**
    * Возвращает дефолтный Response.
    *
    * @return object.
    */
    public function createResponse()
    {
        return new Response();
    }
    
    /**
    * Инициализирует и возвращает объект Request.
    *
    * @return object.
    */
    public function newRequest($method        = null, 
                               $uri           = null, 
                         array $headers       = null, 
                         array $cookies       = null,
                         array $serverParams  = null,
                               $body          = null, 
                         array $uploadedFiles = []
    ) {
        return (new Request($this->env))->newRequest($method, 
                                         $uri, 
                                         $headers, 
                                         $cookies, 
                                         $serverParams, 
                                         $body, 
                                         $uploadedFiles
        )->withCookieParams($_COOKIE)
         ->withQueryParams($_GET)
         ->withParsedBody($_POST);
    }
    
    /**
    * Инициализирует и возвращает объект Response.
    *
    * @return object.
    */
    public function newResponse($body    = 'php://temp', 
                                $status  = 200, 
                          array $headers = []
    ) {
        return new Response($body, $status, $headers);
    }
    
    /**
    * Инициализирует и возвращает объект Uri.
    *
    * @return object.
    */
    public function createUri($uri = '')
    {
        return (new Uri)->newUri($uri);
    }    

    /**
    * Инициализирует и возвращает объект Stream.
    *
    * @return object.
    */
    public function createStream($stream, $mode = 'r')
    {
        return new Stream($stream, $mode);
    }    
    
    /**
    * Инициализирует и возвращает объект UploadedFile.
    *
    * @return object.
    */
    public function createUploadedFile($file = null, 
                                       $name = null, 
                                       $type = null, 
                                       $size = null, 
                                       $error = UPLOAD_ERR_OK
    ) {
        return new UploadedFile($file, $name, $type, $size, $error);
    }
}
