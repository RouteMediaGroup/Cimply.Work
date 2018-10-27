<?php

namespace Cimply\Core\Database\Presenter
{
    use Cimply\Core\Core;
	/**
	 * ViewPresenter short summary.
	 *
	 * ViewPresenter description.
	 *
	 * @version 1.0
	 * @author MikeCorner
	 */
    use Cimply\Core\Database\Provider;
    class Presenter extends QueryBuilder {
        use \Cast;
        protected $manager, $entity;
        public function __construct(Provider $manager = null, $entity) {
            $this->setManager($manager);
            $this->setTable($entity->table ?? null);
            $this->setEntity($entity);
        }

        static final function Cast($mainObject, $selfObject = self::class): self {
            return self::Cull($mainObject, $selfObject);
        }

        public function setNamespace(string $namespace = null) {
            isset($namespace) ? $this->namespace = $namespace.'.' : $this->namespace;
            return $this;
        }

        private function setManager(Provider $manager = null) {
            $this->manager = $manager ? $manager : $this->manager;
            return $this;
        }

        public function getManager():Provider {
            return $this->manager;
        }

        public function setTable(string $table = null) {
            $this->table = $table ? $table : $this->table;
            return $this;
        }

        public function getTable() {
            return $this->table;
        }

        public function setParams(array $params = []) {
            $this->params = $params ? $params : $this->$params;
            return $this;
        }

        public function tableAs($string = null) {
            if(isset($string)) {
                $this->tableAs = ' AS '.$string;
                $this->tableExt = $string.'.';
                $this->onlyExt = $string;
            }
            return $this;
        }

        public function off() {
            $this->where = null;
            $this->operator = null;
            return $this;
        }

        /**
         * set string name of table
         * @param string $string
         * @return Presenter
         */
        public function on(string $string = null) {
            $this->where = null;
            isset($string) ? $this->on = ''.$this->tables['WHERE'].' ON '.$string.' ' : $this->on = ' '.(isset($this->tables['WHERE']) ? $this->tables['WHERE'] : null);

            return $this;
        }

        public function operate($string = null) {
            (empty($this->on) && (isset($string) && (self::isValidName($string)))) ? $this->operator = ' '.$string.' ' : $string;
            return $this;
        }

        public function select($string = null) {
            if(isset($string)) {
                $expSelect = explode(',', str_replace(' ','',$string));
                $part = array();
                foreach($expSelect as $value) {
                    $part[] = ' '.$this->tableExt.$value.'';
                }
                $this->selector = implode(',', $part);
            }
            return $this;
        }

        public function selectById($id = null) {
            if(isset($id)) {
                $indexField = $this->manager->getIndexField($this->table)->name;
                if($indexField) {
                    $this->selectBy = ' '.$this->where.' '. $this->tableExt.$indexField . '  = '.$id.' ';
                }
            }
            return $this;
        }

        public function selectAll() {
            //$indexField = $this->manager->getIndexField($this->table)->name;
            //if($indexField) {
                $this->selectBy = null;
            //}
            return $this;
        }

        public function fetchAll($options = []): array {
            //die(var_dump($this->manager->lastIndexId($this->Query())));
            return $this->manager->fetchAll($this->Query(), $options) ?? [];
        }

        public function selectBy($lambdaExpression) {
            if(isset($lambdaExpression)) {
                $expression = explode("=>", $lambdaExpression);
                if(isset($expression[1])) {
                    $this->selectBy = $this->where.' '.$this->tableExt.implode('=', $expression) .' ';
                } else {
                    $this->selectBy = $this->where.' '. $lambdaExpression;
                }
            }
            return $this;
        }

        public function groupBy($string) {
            isset($string) ? $this->groupBy = ' GROUP BY '.$this->tableExt.$string.' ' : null;
            return $this;
        }

        public function orderBy($string) {
            isset($string) ? $this->orderBy = ' ORDER BY '.$this->tableExt.$string.' ' : null;
            return $this;
        }

        public function asc() {
            self::$sort = ' ASC ';
            return $this;
        }

        public function desc() {
            self::$sort = ' DESC ';
            return $this;
        }

        public function limit($limit) {
            if(isset($limit)) {
                $limiter = explode(',', $limit);
                isset($limiter[1]) ? $this->limit = ' LIMIT '.$limit : $this->limit = ' LIMIT 0, '.$limit;
                if(($limit == "0,1") || ($limit == "1")) {
                    $this->once = true;
                }
            }
            return $this;
        }

        public function top($limit) {
            if(isset($limit)) {
                $this->top = " TOP ({$limit}) ";
            }
            return $this;
        }

        public function pushed($starts = 0, $limit = 1000) {
            $this->limit = ' LIMIT '.$starts.', '.$limit;
            return $this;
        }

        public function counts($name = 'counts') {
            return $this->manager->dbq('SELECT count(*) '.$name.' FROM '.$this->table, []);
        }

        public function distinct() {
            $this->distinct = ' DISTINCT ';
            return $this;
        }

        public function all() {
            $this->distinct = ' ALL ';
            return $this;
        }

        public function combine($arrayOfQueries = array()) {
            if(!(empty($arrayOfQueries))) {
                $this->tables = array();
                $output = array();
                $enumList = self::getValueList();
                $i = 1;
                foreach($arrayOfQueries as $key => $query) {
                    foreach($enumList as $k => $item) {
                        $r = explode($k, $query);
                        isset($r[1]) ? $output = $r : null;
                    }
                    isset($output[$i]) ? $this->tables[$k] = $output[1] : $this->tables[$k].= $output[1] ;
                    $i++;
                }
                $this->selector = $output[0];
                !isset($this->join) ? $this->join() : null;
                //$this->combine = $this->selectBy;
            }
            return $this;
        }

        public function union($arrayOfQueries = null) {
            if(is_array($arrayOfQueries)) {
                $part = null;
                foreach($arrayOfQueries as $query) {
                    $part.= ' UNION ('.$query.') ';
                }
                $this->union = $part;
            } else {
                $this->union = 'UNION '. $arrayOfQueries;
            }
            return $this;
        }

        public function from($string) {
            isset($string) ? $this->from.= ' '.$string.' ' : null;
            return $this;
        }

        public function join($join = null) {
            $this->join = ' '.$join.' JOIN ';
            return $this;
        }

        private function chain($lambdaExpression = null, $glue = null) {
            if(isset($lambdaExpression)) {
                $expression = explode("=>", $lambdaExpression);
                if(isset($expression[1])) {
                    $this->chain[] = $glue.' ('.implode('=', $expression) .') ';
                } else {
                    $this->chain[] = $glue. ' ' .$lambdaExpression .' ';
                }
            }
            return $this;
        }

        public function chainAnd($expression = null) {
            $this->chain($expression, ' AND');
            return $this;
        }

        public function chainOr($expression = null) {
            $this->chain($expression, ' OR');
            return $this;
        }

        public function fieldSwitchAs($from = null, $to = '', $value = null, $notnull = null) {
            if(isset($from)) {
                $hasNamespace = explode('.', $from);
                !(isset($hasNamespace[1])) ? $from = $this->tableExt.$from : null;
                if(isset($value)) {
                    isset($notnull)
                        ? $from = 'IF('.$from.' IS NULL OR '.$from.' = "", CONCAT('.$value.'), '.$from.')'
                        : $from = 'IF('.$from.' IS NULL, CONCAT('.$value.'), CONCAT('.$value.'))';
                }
                $this->colsas = $this->colsas.', '.$from.' AS '.$to;
            }
            return $this;
        }

        public function fieldVirtualAs($fieldAs = null, $value = '') {
            if(isset($fieldAs)) {
                $this->colsas = $this->colsas.', CONCAT('.$value.') AS '.$fieldAs;
            }
            return $this;
        }

        public function extend($string = '') {
            isset($string) ? $this->extend = ' '.$string.' '.$this->extend : $this->extend;
            return $this;
        }

        public function subQuery($table = '', $query = '', $where = null) {
            isset($query) ? $this->subquery = ' = (SELECT '.$query.' FROM '.$table.(isset($where) ? ' WHERE '.$where : $where).') '.$this->subquery : $this->subquery;
            return $this;
        }

        public function fetch($fetchMode = null) {
            isset($fetchMode) ? $this->manager->fetch($fetchMode) : null;
            return $this;
        }

        /**
         * Summary of styleMode
         * @param int $mode 
         * @return Presenter
         */
        public function styleMode(int $mode) {
            $this->manager->fetchStyleMode($mode);
            return $this;
        }

        /**
         * Summary of execute
         * @param mixed $name 
         * @param mixed $value 
         * @return void
         */
        public function execute($name = null, $value = null): self {
            $dbm = $this->getManager();
            $dbm->beginTransaction();
            $result = $dbm->dbq($this->query(), $this->params);
            if(isset($value)) {
                isset($name) ? $result = array($name => array($value => $result[$name])) : $result = array($value => $result);
            }
            (isset($name) && isset($result[$name])) ? $result = $result[$name] : null;
            $this->result = $result;
            return $this;
        }

        public function result() {
            return $this->result ??  [];
        }

        public function setEntity($value = null) {
            $value ? $this->entity = $value : null;
            return $this;
        }

        public function getEntity(): ?object {
            return $this->entity ?? null;
        }

        public function message($value) {
            $this->infoMessage = $value;
            return $this;
        }

        public function entity() {
            if(isset(Core::$Factory[$this->table]) ? false : true) {
                Core::addFactory(array($this->table => $this->entity));
                $this->model = Core::$Factory[$this->table];
            }
            return $this;
        }

        public function canExecute() {
            return (bool)isset($this->table);
        }

        public function model() {
            return $this->model;
        }

        public function validationRules() {
            return $this->manager->WrapperRules($this->table);
        }

        public function refresh($value = "true") {
            $this->refresh = $value;
            $this->manager->refresh($value);
            return $this;
        }

        public function update($entity = null) {
            die(var_dump($this->entity()));
            if((bool)$this->model) {
                $this->model->update($entity ? $entity : $this->execute());
            }
            return $this;
        }

        public function externalApi() {
            $dataSelector = null;
            $stripTags = false;
            $this->parameter = str_replace("�", "'", $this->parameter);
            isset($this->parameter['Table']) ? $this->setTable($this->parameter['Table']) : null;
            isset($this->parameter['Select']) ? $this->select($this->parameter['Select']) : null;
            isset($this->parameter['Top']) ? $this->top($this->parameter['Top']) : null;
            isset($this->parameter['SelectBy']) ? $this->selectBy($this->parameter['SelectBy'], $this->parameter['Params']) : null;
            isset($this->parameter['Params']) ? $this->setParams($this->parameter['Params']) : null;
            isset($this->parameter['SelectById']) ? $this->selectById($this->parameter['SelectById']) : null;
            isset($this->parameter['SelectAll']) ? $this->selectAll() : null;
            isset($this->parameter['From']) ? $this->from($this->parameter['From']) : null;
            isset($this->parameter['OrderBy']) ? $this->orderBy($this->parameter['OrderBy']) : null;
            isset($this->parameter['GroupBy']) ? $this->groupBy($this->parameter['GroupBy']) : null;
            isset($this->parameter['Message']) ? $this->message($this->parameter['Message']) : null;
            isset($this->parameter['Asc']) ? $this->asc() : null;
            isset($this->parameter['Desc']) ? $this->sesc() : null;
            isset($this->parameter['Limit']) ? $this->limit($this->parameter['Limit']) : null;
            isset($this->parameter['Refresh']) ? $this->refresh($this->parameter['Refresh']) : null;
            isset($this->parameter['Data']) ? $dataSelector = $this->parameter['Data'] : null;
            isset($this->parameter['StripTags']) ? $stripTags = true : $stripTags = false;
            if(isset($this->parameter['Export'])) {
                $data = isset($this->parameter['Execute']) ? ($this->canExecute() ? $this->execute($dataSelector) : $this->query()) : $this->query();
                $this->ExportCsv($data);
            } else {
                $result = array((isset($this->parameter['Execute']) ? ($this->canExecute() ? $stripTags == true ? ($this->execute($dataSelector)) : $this->execute($dataSelector) : $this->query()) : $this->query()));
                return (isset($this->parameter['Straight'])) ? $result[0] : array("result" => $result[0]);
            }
        }
    }
}