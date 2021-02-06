<?php

namespace Abcsoft\SQL\Migrations;

use Abcsoft\SQL\DbCommand\DbCommand;
 
 
/** 
 * Миграции
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su
 * @copyright © 2017
 * @license http://www.wtfpl.net/ 
 */  
class ProgressBar
{

    public function __construct()
    {
        $this->write(PHP_EOL);               
    }
    /**
    * Прогрессс-бар
    *
    */     
    public function create($cnt)
    { 
        if($this->hasAnsi()){    
            $progress = '[';
            for($i = 0; $i <= 10; $i++){
                if($i == $cnt) {
                    $progress .= '=>';            
                } else {
                    $progress .= '-';            
                }
            }
            $progress .= ']';
            $this->clear();
            $this->write($progress);
        } else {
            $this->write(PHP_EOL);
            $this->write("..");
            $this->write(PHP_EOL);
        }
    } 
    
    /**
    * Прогрессс-бар
    *
    */     
    public function end($message)
    {
        if($this->hasAnsi()){
            $this->clear();
            $this->write(PHP_EOL);
            $this->write($message);
            $this->write(PHP_EOL);
            $this->write(PHP_EOL);
        }
    }   
    
    /**
    * Прогрессс-бар
    *
    */    
    public function clear()
    {
        $this->write("\x0D");
        $this->write("\x1B[2K");
    }
    
    /**
    * Прогрессс-бар
    *
    */    
    public function write($message)
    {
        file_put_contents('php://stdout', $message);
    }  
    
    /**
    * Прогрессс-бар
    *
    */     
    protected function hasAnsi()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return
                0 >= version_compare(
                    '10.0.10586',
                    PHP_WINDOWS_VERSION_MAJOR . '.' . PHP_WINDOWS_VERSION_MINOR . '.' . PHP_WINDOWS_VERSION_BUILD
                )
                || false !== getenv('ANSICON')
                || 'ON' === getenv('ConEmuANSI')
                || 'xterm' === getenv('TERM');
        }
     
        return false;
    }
}
    