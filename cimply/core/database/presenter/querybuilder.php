<?php

namespace Cimply\Core\Database\Presenter
{
        /**
         * QueryBuilder short summary.
         *
         * QueryBuilder description.
         *
         * @version 1.0
         * @author MikeCorner
         */
        use \Cimply\Interfaces\Database\Enum\OperatorList;

        abstract class QueryBuilder
        {
                protected $tables = array(), $query, $result = [], $tableAs = null, $tableExt = null, $table = null, $onlyExt = null, $on = null, $operator = 'SELECT', $where = ' WHERE ', $selector = ' * ', $from = ' FROM ', $selectBy = null, $groupBy = null, $orderBy = null, $top = null, $limit = null, $once = false, $distinct = null, $join = null, $chain = [], $colsas = null, $combine = null, $union = null, $extend = null, $subquery = null, $entity = null, $model = null, $infoMessage = true, $refresh = false, $namespace = null, $dbconnect = null;
                private static $sort = ' ASC ';
                public $params = [];
                public function query(): ?string {
                        $this->query = isset($this->query) ? $this->query : $this->operator
                        .$this->top
                        .$this->selector
                        .$this->colsas
                        .$this->from
                        .$this->namespace.$this->table
                        .$this->tableAs
                        .$this->selectBy
                        .$this->join
                        .$this->combine
                        .$this->on
                        .implode('', $this->chain)
                        .$this->union
                        .$this->distinct
                        .$this->subquery
                        .(isset($this->orderBy) ? $this->orderBy.self::$sort : $this->orderBy)
                        .$this->groupBy
                        .$this->limit
                        .$this->extend;
                        return preg_replace('/\s+/', ' ', $this->query);
                }
                public function setQuery($query = null): self {
                        isset($query) ? $this->query = $query : null;
                        return $this;
                }
                
        }
}