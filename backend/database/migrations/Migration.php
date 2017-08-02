<?php

namespace suplascripts\database\migrations;

use Phinx\Migration\AbstractMigration;
use Slim\App;
use suplascripts\app\Application;

require __DIR__ . '/../../vendor/autoload.php';

/**
 * Write your reversible migrations using the change() method.
 *
 * More information on writing migrations is available here:
 * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
 *
 * The following commands can be used in this method and Phinx will
 * automatically reverse them when rolling back:
 *
 *    createTable
 *    renameTable
 *    addColumn
 *    renameColumn
 *    addIndex
 *    addForeignKey
 *
 * Remember to call "create()" or "update()" and NOT "save()" when working
 * with the Table class.
 */
abstract class Migration extends AbstractMigration
{
    /** @var Application */
    protected static $application;

    public function init()
    {
        if (!self::$application) {
            self::$application = Application::getInstance();
        }
    }
}
