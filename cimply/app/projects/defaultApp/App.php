<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of App
 *
 * @author MikeCorner
 */

namespace Cimply\App\Projects\defaultApp {
    use \Cimply\App\Repository\Assets;
    use \Cimply\Core\{Database\Database, Database\Presenter, Database\Enum\FetchStyleList, Routing\Routing, View\Scope, View\View, Gui\Gui};
    use \Cimply\Core\Validator\Validator;
    use \Cimply\Basics\{ServiceLocator\ServiceLocator, Repository\Support};
    use \Cimply\App\Models\CimplyWork\ProcessModel;
    use \Cimply\Interfaces\Support\Enum\RootSettings;
    class App {
        use \Annotation;
        function __construct(ServiceLocator $instance) {
            //Instanziere Route
            $route = Routing::Cast($instance->getService());

            //Instanziere Scope
            $scope = Scope::Cast($instance->addInstance((new Scope())->set($route->getScope())));
            
            //Instanziere View
            $view = $instance->addInstance((new View($scope))->set(Support::Cast($instance->getService())->getSettings([]), true));
            
            //Instanziere Gui
            $instance->addInstance((new Gui($view)));

            //Instanziere Validator

            $valid = new Validator();
            $valid->addRules(['mail' => ['type'=>'email', 'required'=>true, 'min'=>1, 'max'=>3, 'trim'=>true],'katze' => ['type'=>'string', 'required'=>true, 'min'=>1, 'max'=>3, 'trim'=>true]]);
            $valid->addSource(['mail' => 'testmax.se']);
            $valid->addSource(['katze' => 'te23']);
            //die(var_dump($valid->run()));
            
            //$instance->addInstance();

            //Action
            View::Cast($instance->getService())->set(Scope::Cast($instance->getService()));
            $instance->addInstance(new Database(Support::Cast($instance->getService())->getRootSettings(RootSettings::DBCONNECT)));
            
            //Default Annotations
            View::Assign((self::GetAnnotations($scope->getAction()))->getParameters());
        
            return ($scope->getAction())($instance);
        }
    }
}