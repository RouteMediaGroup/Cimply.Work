<?php

namespace Cimply\Core\Database\Presenter
{
	/**
	 * ViewPresenter\RegistEntity short summary.
	 *
	 * ViewPresenter\RegistEntity description.
	 *
	 * @version 1.0
	 * @author MikeCorner
	 */

    class RegistEntity {
        private $entities = [];
        public function __construct($manager = null, $entities = []) {
            foreach($entities as $key => $entity) {
                $this->entities[$key] = new Presenter($manager, $entity);
            }    
        }
        public function getEntity($name = null):Presenter {
            return Presenter::Cast($this->entities[$name]);
        }
        public function getEntities(): self {
            return $this;
        }
        public function addEntity($manager = null, $name = null, $entity): self {
            isset($this->entities[$name]) ? : $this->entities[$name] = new Presenter($manager, $entity);
            return $this;
        }
    }
}