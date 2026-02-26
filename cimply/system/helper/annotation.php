<?php
/*
 * Cimply.Work - Business Framework 2012-2025: Proprietary commercial license © RouteMedia® – Represented by Michael Eckebrecht.
 * Contact: direkt@route-media.info. All rights reserved.
*/

namespace {
    use Cimply\Core\Annotation\Annotation as Annotate;

    trait Annotation {

        public static function GetAnnotations($classObject = null): Annotate
        {
            $classObject = $classObject !== null ? (string)$classObject : '';

            $objectExpl = \explode("::", $classObject);
            $className = $objectExpl[0] ?? null;
            $method = $objectExpl[1] ?? null;

            try {
                $annotate = new Annotate($className, $method);
            } catch (\Exception | \ReflectionException | \AnnotateException | \ArgumentCountError $e) {
                \Debug::VarDump($e->getMessage());
                $annotate = new Annotate(null, null);
            }

            return $annotate;
        }
    }
}
