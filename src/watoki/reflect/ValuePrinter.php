<?php
namespace watoki\reflect;

class ValuePrinter {

    public static function serialize($value) {
        if (is_array($value) || $value instanceof \Traversable) {
            $withKeys = array();
            $values = array();
            $onlyNumericKeys = true;
            foreach ($value as $key => $item) {
                $onlyNumericKeys = $onlyNumericKeys && is_int($key);

                $exported = self::serialize($item);
                $withKeys[] = self::serialize($key) . ': ' . $exported;
                $values[] = $exported;
            }
            return (is_object($value) ? '<' . get_class($value) . '>' : '')
            . '[' . implode(', ', $onlyNumericKeys ? $values : $withKeys) . ']';

        } else if (is_object($value)) {
            $string = '';
            if (method_exists($value, '__toString')) {
                $string = '(' . self::serialize($value->__toString()). ')';
            } else if ($value instanceof \DateTime) {
                $string = '(' . $value->format('c') . ')';
            }
            return '<' . get_class($value) . '>' . $string;
        } else if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        } else {
            return var_export($value, true);
        }
    }
}