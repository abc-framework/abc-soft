<?php

namespace Abcsoft\SQL\Migrations;
 
/** 
 * Миграции
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su
 * @copyright © 2017
 * @license http://www.wtfpl.net/ 
 */  
class Loger
{
    public $command;
    public $prefix;
    
    public function __construct($DBcommand)
    {
        $this->command = $DBcommand;               
    }

    /**
    * Создание таблицы истории
    *
    */ 
    public function createTable()
    {
        try {
            $this->command
                ->createCommand("CREATE TABLE IF NOT EXISTS {{%system_migration}} (
                                        `version` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                                        `apply_time` int(11) DEFAULT NULL,
                                          UNIQUE KEY `version` (`version`)
                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci")
                ->execute();
         
            $report = $this->insertBegin() ? ABC_MIGRATION_TABLE_SUCCESS : null;
            
            return $report;
            
        } catch (\Throwable $t) {
            return ABC_MIGRATION_FAILED_TABLE . PHP_EOL . $t->getMessage();
        }
        
        return false;
    }
    
    /**
    * Запись в историю
    *
    */ 
    public function markMigrations($migrates)
    {
        $migrates = !is_array($migrates) ? [$migrates] : $migrates;
        $insert = [];
        $progressBar = new ProgressBar;
        $cnt = 0;
        foreach($migrates as $migrate){
           $cnt = $cnt > 10 ? 0 : $cnt;
           $progressBar->create($cnt++);
           $insert[] = [$migrate, time()];
           sleep(1);
        }
        
        $this->command
            ->batchInsert('system_migration', [
                'version', 
                'apply_time'
                ], 
                $insert)
            ->execute();
        $this->command->reset();
        
        $progressBar->end("It's okay.". PHP_EOL ."Все отлично.");
    } 
    
    /**
    * Удаление из истории
    *
    */ 
    public function deleteMigrations($migrates)
    {
        $migrates = !is_array($migrates) ? [$migrates] : $migrates;
        $migrates = array_values($migrates);
        $insert = [];
      
        try{
            $this->command
                ->delete('system_migration',  ['in', 'version', $migrates])
                ->execute();
                
            $this->command->reset();
            $num = count($migrates);
            return Helper::prepareReport($migrates, sprintf(ABC_MIGRATION_DELETE, $num, $num));
        } catch (\Throwable $t) {
            return ABC_MIGRATION_NO_CLEAR . PHP_EOL . $t->getMessage();
        }
    } 
    
    /**
    * Просмотр истории
    *
    */ 
    public function getHistory($limit = 10, $order = 'asc')
    {
        if('all' === strtolower($limit)){
            $this->command
                ->select()
                ->from('system_migration')
                ->order([$order => 'version']);
         
        } else {
            $this->command
                ->select()
                ->from([
                    'm' => $this->command
                    ->subQuery()
                    ->select() 
                    ->from('system_migration')    
                    ->order('version DESC')   
                    ->limit((int)$limit)
                ])   
                ->order('m.version asc');
        }
        
        $result = ['version' => [], 'apply_time' => []];        
        foreach ($this->command->queryRow() as $row)
        {
            if($row['version'] === '000000_begin') continue;
            $result['version'][] = $row['version']; 
            $result['apply_time'][] = $row['apply_time'];
        }
        $this->command->reset();
        return $result;
    }  

    /**
    * Очистка истории
    *
    */ 
    public function wipe($limit = 10)
    {   
        if(!empty($limit)){
            $migrations = $this->getHistory($limit)['version'];
            if(!empty($migrations)){
                return $this->deleteMigrations($migrations);
            }
            return ABC_MIGRATION_EMPTY;
        }
        
        try {        
            $this->command->truncateTable('system_migration'); 
            $this->insertBegin();
            return ABC_MIGRATION_CLEAR;
        } catch (\Throwable $t) {
            return ABC_MIGRATION_NO_CLEAR . PHP_EOL . $t->getMessage();
        }
    }
    
    /**
    * Старт
    *
    */ 
    protected function insertBegin()
    {
        $this->command
            ->createCommand("INSERT IGNORE INTO {{%system_migration}} (`version`, `apply_time`) 
                                        VALUES ('000000_begin', '". time() ."')")
            ->execute();                        
                                    
        return $this->command->rowCount() > 0;    
    }
}

