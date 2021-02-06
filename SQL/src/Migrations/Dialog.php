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
class Dialog
{
    
    /**
    * Подтверждение
    *
    */ 
    public static function confirm($message)
    {
        print("\n". $message);
        $std = fopen("php://stdin", "r");        
        $line = trim(strtolower(fgets($std)));
     
        switch($line) {
            case 'y':
            case 'yes':
                fclose($std);
                return 1;       
            case 'n' :
            case 'no':
                fclose($std);
                return 2;
            case '':
                printf(ABC_MIGRATION_NO_CONFIRM);
                self::confirm($message);                
            default:
                printf(ABC_MIGRATION_INVALID_COMMAND, $line);
                self::confirm($message);
        }      
        return false;
    }
   
    /**
    * Формат
    *
    */ 
    public static function getFormat($type)
    {
        switch(strtolower($type)) {
            case '--e':
            case '--empty':
                return 'empty';
         
            case '--c':
            case '--create':
                return 'CreateTable';
         
            case '--d':
            case '--drop':
                return 'DropTable';
         
            case '--r':
            case '--renamme':
                return 'RenameTable';            
         
            case '--t':
            case '--truncate':
                return 'TruncateTable'; 
         
            case '--ac':
            case '--addcolumn':
                return 'AddColumn'; 
         
            case '--dc':
            case '--dropcolumn':
                return 'DropColumn';; 
         
            case '--rc':
            case '--renamecolumn':
                return 'RenameColumn';
         
            case '--af':
            case '--aftercolumn':
                return 'AfterColumn';
         
            case '--ap':
            case '--addprymary':
                return 'AddPrimaryKey'; 
         
            case '--dp':
            case '--dropprimary':
                return 'DropPrimaryKey'; 
         
            case '--ci':
            case '--createindex':
                return 'CreateIndex';
             
            case '--cu':
            case '--createunique':
                return 'CreateUniqueIndex';                
         
            case '--di':
            case '--dropindex':
                return 'DropIndex'; 
          
            default:
                exit(sprintf(ABC_MIGRATION_INVALID_COMMAND, $type));
        }
      
        return false;
    }     
}
 