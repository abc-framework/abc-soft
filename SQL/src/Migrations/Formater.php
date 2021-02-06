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
class Formater
{
    protected $namespace;
    
    public function __construct($namespace)
    {    
        $this->namespace = $namespace;
    }
    
/**
* Пустая миграция для произвольного запроса
*
*/
    public function __call($type, $args)
    {
        $methods = $this->$type($args[1], $args[2]);

        return <<<EOD
<?php

namespace {$this->namespace};

class {$args[0]}
{
{$methods}
}
EOD;
    }


/**
* Пустая миграция для произвольного запроса
*
*/
    protected function empty($table)
    {
        return <<<EOD

    public function up(\$command)
    {
       \$command->createCommand(" "
                )->execute();
    }

    public function down(\$command)
    {
       \$command->createCommand(" "
                )->execute();
    }

EOD;
    }  
    
/**
* Создание таблицы
*/
    protected function createTable($table, $num)
    {    
        return <<<EOD

    public function up(\$command)
    {
        \$command->createTable('{$table}', [ // Columns
                                  'id'     => 'int(10) NOT NULL AUTO_INCREMENT',
{$this->generateColumns($num)}                              ],
                                [ // Keys
                                 'id'    => 'PRIMARY KEY'
                              ],
                              'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
                );
    }        

    public function down(\$command)
    {
        \$command->dropTable('{$table}');
    }        

EOD;
    }

    /**
    * 
    *
    */ 
    protected function dropTable($table, $num)
    {    
        return <<<EOD

    public function up(\$command)
    {
        \$command->dropTable('{$table}');
    }

    public function down(\$command)
    {
        \$command->createTable('{$table}', [ // Columns
                                  'id'     => 'int(10) NOT NULL AUTO_INCREMENT',
{$this->generateColumns($num)}                              ],
                                [ // Keys
                                 'id'    => 'PRIMARY KEY'
                              ],
                              'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
                );
    }

EOD;
    }  
    
    /**
    * 
    *
    */ 
    protected function renameTable($table)
    {
        return <<<EOD

    public function up(\$command)
    {
        \$command->renameTable('{$table}', '');
    }

    public function down(\$command)
    {
        \$command->renameTable('', '{$table}');
    }

EOD;
    }
    
    /**
    * 
    *
    */ 
    protected function truncateTable($table)
    {    
        return <<<EOD

    public function up(\$command)
    {
        \$command->truncateTable('{$table}');
    }

    public function down(\$command)
    {
        return false;
    }

EOD;
    }  
    
    /**
    * 
    *
    */ 
    protected function addColumn($table, $num)
    {    
        return <<<EOD

    public function up(\$command)
    {
        \$command->addColumns('{$table}',  [ // Columns
{$this->generateColumns($num)}                              ], // AFTER
                                              ''
                                        );
    }        

    public function down(\$command)
    {
        \$command->dropColumns('{$table}',  [ // Columns
{$this->generateColumnNames($num)}                              ]
                                        );
    }

EOD;
    } 
    
    /**
    * 
    *
    */ 
    protected function dropColumn($table, $num)
    {    
        return <<<EOD

    public function up(\$command)
    {
        \$command->dropColumns('{$table}', '');
    }        

    public function down(\$command)
    {
        \$command->addColumns('{$table}',  [ // Columns
{$this->generateColumns($num)}                              ], // AFTER
                                              ''
                                        );
    }

EOD;
    }

    /**
    * 
    *
    */ 
    protected function renameColumn($table)
    {    
        return <<<EOD

    public function up(\$command)
    {
        \$command->renameColumn('{$table}', '', '');
    }        

    public function down(\$command)
    {
        \$command->renameColumn('{$table}', '', '');
    }

EOD;
    }

    /**
    * 
    *
    */ 
    protected function afterColumn($table)
    {    
        return <<<EOD

    public function up(\$command)
    {
        \$command->afterColumn('{$table}', '', '');
    }        

    public function down(\$command)
    {
        \$command->afterColumn('{$table}', '', '');
    }

EOD;
    }

    /**
    * 
    *
    */ 
    protected function addPrimaryKey($table)
    {    
        return <<<EOD

    public function up(\$command)
    {
        \$command->addPrimaryKey('{$table}', '');
    }        

    public function down(\$command)
    {
        \$command->dropPrimaryKey('{$table}', '');
    }

EOD;
    }

    /**
    * 
    *
    */ 
    protected function dropPrimaryKey($table)
    {    
        return <<<EOD

    public function up(\$command)
    {
        \$command->dropPrimaryKey('{$table}');
    }        

    public function down(\$command)
    {
        \$command->addPrimaryKey('{$table}', '');
    }

EOD;
    }

    /**
    * 
    *
    */ 
    protected function createIndex($table, $num)
    {    
        return <<<EOD

    public function up(\$command)
    {
        \$command->createIndex('{$table}',  [ // Columns
{$this->generateColumnNames($num)}                              ], // Index Name
                                              '',
                                              true // Unique
                                        );
    }        

    public function down(\$command)
    {
        \$command->dropIndex('{$table}', '');
    }

EOD;
    }

    /**
    * 
    *
    */ 
    protected function createUniqueIndex($table)
    {    
        return <<<EOD

    public function up(\$command)
    {
        \$command->createUniqueIndex('{$table}', '', '');
    }        

    public function down(\$command)
    {
        \$command->dropIndex('{$table}', '');
    }

EOD;
    } 

    /**
    * 
    *
    */ 
    protected function dropIndex($table)
    {    
        return <<<EOD

    public function up(\$command)
    {
        \$command->dropIndex('{$table}', '');
    }        

    public function down(\$command)
    {
        \$command->createIndex('{$table}', '', '');
    }

EOD;
    }
    
    
    protected function generateColumns($num)
    {    
        $columns = '';
        for($i = 0; $i < $num; $i++){
            $columns .= "                                  ''     => '',\n";
        }
        
        return $columns;
    } 

    protected function generateColumnNames($num)
    {    
        $columns = '';
        for($i = 0; $i < $num; $i++){
            $columns .= "                                            '',\n";
        }
        
        return $columns;
    }      
}
 
