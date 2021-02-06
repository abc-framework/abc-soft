<?php

namespace Abcsoft\SQL\Migrations;

use Abcsoft\SQL\DbCommand\DbCommand;
use Abcsoft\SQL\Migrations\ProgressBar;
 
/** 
 * Миграции
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su
 * @copyright © 2017
 * @license http://www.wtfpl.net/ 
 */  
class Executor
{
    protected $config;
    protected $command;
    protected $loger;
    protected $message;
    protected $compiled = [];
    
    public function __construct($config, $DBcommand, $loger)
    {
        $this->config  = $config;    
        $this->command = $DBcommand;
        $this->loger = $loger;
    }

    /**
    * Исполнение миграции
    *
    */ 
    public function run($migrates)
    {
        $compiled = [];
        try {
            $this->apply($migrates, 'up');
            $this->mark($this->compiled, 'up');
            $this->message = ABC_MIGRATION_LIST_EXECUTE;
            return $this->compiled;
            
        } catch (\Exception $e) {
            if(!empty($this->compiled)){
                $this->mark($this->compiled, 'up');
                print(Helper::prepareReport($this->compiled, ABC_MIGRATION_LIST_EXECUTE));
            }
            print(PHP_EOL . Helper::stripTags($e->getMessage()) . PHP_EOL);
            
            switch(Dialog::confirm(ABC_MIGRATION_ROLLBACK)) {
                case 1 :
                    try{
                        $this->apply(array_reverse($this->compiled), 'down');
                    } catch (\Throwable $t){
                        print(Helper::stripTags($e->getMessage()));
                        return false;
                    }
                    
                    if(!empty($this->compiled)){
                        $this->message = ABC_MIGRATION_LIST_CANCEL . PHP_EOL;
                    }
                    
                    $this->mark($this->compiled, 'down');                    
                    return array_unique($this->compiled);
                case 2 :
                    print(ABC_MIGRATION_CANCEL . PHP_EOL);
                    return false;            
            }
            return false;
        }
    }
    
    /**
    * Исполнение миграции
    *
    */ 
    public function rollback($limit = 10)
    {
        $migrates = $this->loger->getHistory($limit, 'desc')['version'];
        if(!empty($migrates)){
            try{
                $this->apply($migrates, 'down');
                $this->mark($this->compiled, 'down');
                return Helper::prepareReport($this->compiled, ABC_MIGRATION_LIST_CANCEL);
            } catch (\Throwable $t) {
                return ABC_MIGRATION_NO_ROLLBACK . PHP_EOL . Helper::stripTags($t->getMessage());
            }
        }
        
        return ABC_MIGRATION_APPLY_EMPTY;
    }
    
    /**
    * Исполнение миграции
    *
    */ 
    public function apply($migrates, $method)
    {
        $migrates = !is_array($migrates) ? [$migrates] : $migrates;
        $progressBar = new ProgressBar;
        $migrate = '';
        try{ 
            $cnt = 0;
            foreach($migrates as $migrate){
                $cnt = $cnt > 10 ? 0 : $cnt;
                $this->command->reset();
                $class = $this->config['namespace'] .'\\'. $migrate;
                
                if(false === (new $class)->$method($this->command)){
                    throw new \Exception(sprintf(ABC_MIGRATION_EXECUTE_ERROR, $migrate) 
                    . PHP_EOL . Helper::stripTags($e->getMessage()));
                }
                $this->compiled[] = $migrate;
                $progressBar->create($cnt++);
            }
            
            $progressBar->end("Аpplied 100% Please wait, writing history.". PHP_EOL
            ."Применено 100% миграций. Пожалуйста ждите, пишем историю.");
            
        }catch(\Throwable $t){
            
            throw new \Exception(sprintf(ABC_MIGRATION_EXECUTE_ERROR, $migrate) 
            . PHP_EOL . Helper::stripTags($t->getMessage()));
        }
    }
    
    /**
    * Исполнение миграции
    *
    */ 
    protected function mark($migrates, $method)
    {
        switch($method){
            case 'up':
                $this->loger->markMigrations($migrates);
            break;
            case 'down':
                $this->loger->deleteMigrations($migrates);
            break;
        }
    } 
    
    /**
    * Сообщение
    *
    */ 
    public function getMessage()
    {
        return $this->message;
    }
}
    