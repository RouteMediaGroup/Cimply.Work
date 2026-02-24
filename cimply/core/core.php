<?php
/*
 * Cimply.Work - Business Framework 2012-2025: Proprietary commercial license © RouteMedia® – Represented by Michael Eckebrecht. 
 * Contact: direkt@route-media.info. All rights reserved.
*/

declare(strict_types=1);

namespace Cimply\Core {

    use Cimply\Interfaces\ICast;

    abstract class Core implements ICast
    {
        /** @var array<int, string>|null */
        public static ?array $fillable = null;

        /**
         * Cast a Object to Current Class-Object to itself
         * @param mixed $mainObject
         * @param string $selfObject
         */
        public static function Cast($mainObject, $selfObject = self::class): object
        {
            // call Cull on the concrete class that uses the trait
            if (is_string($selfObject) && method_exists($selfObject, 'Cull')) {
                return $selfObject::Cull($mainObject, $selfObject);
            }

            // fallback: use late static binding
            return static::Cull($mainObject, $selfObject);
        }

        /**
         * Fill an object from stdClass/array
         * @param mixed $std
         * @param object|string $selfObject
         */
        public static function FillObjectFromStdClass($std, $selfObject = self::class): object
        {
            $instance = is_object($selfObject) ? clone $selfObject : new $selfObject();

            foreach ((array)$std as $attribute => $value) {
                $attr = (string)$attribute;

                if (self::fillableIsSetAndContainsAttribute($attr) || self::fillableNotSet($selfObject)) {
                    $instance->{$attr} = $value;
                }
            }

            return $instance;
        }

        /**
         * Returns if the fillable array exists and contains the attributes requested.
         */
        public static function fillableIsSetAndContainsAttribute(string $attribute): bool
        {
            return (
                isset(static::$fillable)
                && is_array(static::$fillable)
                && count(static::$fillable) > 0
                && in_array($attribute, static::$fillable, true)
            );
        }

        /**
         * Returns whether fillable attribute is not set.
         * @param object|string $selfObject
         */
        public static function fillableNotSet($selfObject): bool
        {
            if (is_string($selfObject) && class_exists($selfObject)) {
                return !isset($selfObject::$fillable);
            }

            return !isset(static::$fillable);
        }
    }
}
