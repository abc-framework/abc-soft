<?php

namespace Abcsoft\SQL\Migrations;


use Abcsoft\SQL\DbCommand\DbCommand; 
if(!function_exists('dbg')){
    function dbg($var){
echo "\n\n";
    var_dump($var);
echo "\n";
$bactrace = debug_backtrace()[0];
    echo  $bactrace['file'] .': '. $bactrace['line'];
    
    exit("\n\n");
    }    
}  
/** 
 * Миграции
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su
 * @copyright © 2017
 * @license http://www.wtfpl.net/ 
 */  
class Migrations
{
    protected $config;
    protected $command;
    protected $loger;
    protected $formatter;    
    protected $report = [ABC_MIGRATION_ERROR];    
    
    /**
    * Конструктор.
    *
    */ 
    public function __construct(array $config)
    {
        $this->config  = $config['migrations'];
        $this->command = new DbCommand($config);        
        $this->formatter = new Formater($this->config['namespace']);
        $this->loger = new Loger($this->command);
        $report = $this->loger->createTable();
        
        if(false !== $report){
            $this->report = [$report];
        }
    }
    
    /**
    * Роутер.
    *
    */ 
    public function route($argv)
    {
        if(empty($argv[1])){
            exit;
        }
        
        $rout = explode('/', $argv[1]);
        $arg1 = $argv[2] ?? null;
        $arg2 = $argv[3] ?? null;
        $arg3 = $argv[4] ?? null;
        
        if(empty($rout[1])){
           return $this->run($arg1);
        }
        
        $command = $rout[1] ?? null;
        switch(strtolower($command)){
            case 'create' :
                if(isset($arg1)){    
                    $this->report[] = $this->create($arg1, $arg2 ?? '--e', $arg3 ?? 2);                
                } else {
                    $this->report[] = ABC_MIGRATION_NO_TABLENAME;
                }
            break;
         
            case 'wipe' :
                $this->report[] = $this->loger->wipe($arg1);
            break;
         
            case 'rollback' :
                $this->report[] = (new Executor($this->config, $this->command, $this->loger))->rollback($arg1);
            break;
            
            case 'history' : 
                $report = $this->getHistory($arg1 ?? 20);
               $this->report[] = empty($report) ? ABC_MIGRATION_APPLY_EMPTY 
                                                : Helper::prepareReport($report, ABC_MIGRATION_HISTORY);
            break;
            
            case 'help' : 
            case 'doc' :  
                print((new Doc)->getDocRu());
            break;
            
            default :
                $this->report[] = sprintf(ABC_MIGRATION_INVALID_COMMAND, $command);    
            
        }
    }    

    /**
    * История
    *
    */ 
    protected function getHistory($limit)
    {
        $history = $this->loger->getHistory($limit);
        $report  = [];
        foreach($history['version'] as $k => $item){
            $report[] = $item .' ('. date('Y-m-d H:i:s', $history['apply_time'][$k]) .')';
        }
        return $report;
    }     
    
    /**
    * Создание миграции
    *
    */ 
    protected function create($name, $type, $numcol)
    {
        $date   = date('ymd_His');
        $format = $method = Dialog::getFormat($type);
        $format = ('empty' !== $format) ? $format .'_' : '';
        $class  = 'm'. $date .'_'. $format . $name;
        $text   = $this->formatter->$method($class, $name, $numcol);
     
        if(false === @file_put_contents($this->config['dir'] . $class .'.php', $text)){
            return sprintf(ABC_MIGRATION_INVALID_PATH, $this->config['dir'], $this->config['dir']);
        }
     
        return sprintf(ABC_MIGRATION_SUCCESS, $class);
    }    

    /**
    * Выполнение миграций
    *
    */ 
    protected function run($limit)
    { 
        $limit = strtolower($limit);
        $limit = '' === $limit || 'all' === $limit ? 'all' : $limit;        
        if(false === ($migrates = $this->getNewMigrations($limit))) {
            $this->report[] = sprintf(ABC_MIGRATION_INVALID_COMMAND, $limit);
            return false;
        } elseif(empty($migrates)) {
            $this->report[] = ABC_MIGRATION_NEW_EMPTY;
            return false;
        }
     
        print(Helper::prepareReport($migrates, ABC_MIGRATION_LIST_NEW));
        switch(Dialog::confirm(ABC_MIGRATION_APPLY)) {
            case 1 :
                $executor = new Executor($this->config, $this->command, $this->loger);
                $result = $executor->run($migrates);
                $mess = $executor->getMessage();
                $this->report[] = Helper::prepareReport($result, $mess);
                return false;
            case 2 :
                $this->report[] = ABC_MIGRATION_CANCEL;
                return false;
                
        }    
    }

    /**
    * Получение новых миграций
    *
    */ 
    protected function getNewMigrations($limit)
    {
        if(!is_numeric($limit) && strtolower($limit) !== 'all' && !is_null($limit)) {
            return false;
        }
//     'C:\OpenServer\domains\marmosetka\App\Domain\Sources\Migrations\\'
        $history = $this->loger->getHistory('all')['version'];   
        $migrations = scandir($this->config['dir']);
        array_splice($migrations, 0, 2);
        
        $newMigrates = [];
        foreach($migrations as $migrate){
            $migrate = basename($migrate, '.php');
            if(!in_array($migrate, $history)){
                $newMigrates[] = $migrate;
            }
        }
     
        if((int)$limit > 0){
            return array_slice($newMigrates, -(int)$limit);
        }
        
        return $newMigrates;
    }
    
    /**
    * 
    *
    */     
    public function getReport()
    {
        return Helper::stripTags(implode($this->report, PHP_EOL)) . PHP_EOL;
    }    
}
 