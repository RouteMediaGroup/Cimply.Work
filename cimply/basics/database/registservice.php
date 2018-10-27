<?php

namespace Cimply\Basics\Database {
    use \Cimply\Core\{
        Core,
        Database\Database,
        Database\Presenter\Presenter,
        Database\Presenter\RegistEntity,
        Database\DatabaseFactory
    };

    use \Cimply\Core\Request\Request;
    
    class RegistService extends Database
    {
        private $dbManager = [];
        protected function registManager(string $model): void {
            $this->dbManager[$model] = (parent::Cast($this->services))->getInstance($model);
        }

        protected function registEntities(string $model, array $entities = []): RegistEntity {
            return (new RegistEntity($this->getManager($model)->dbm(), $entities));
        }

        protected function getManager($name= null): DatabaseFactory {
            return $this->dbManager[$name];
        }
    } 
}