<?php
namespace watoki\reflect;

class ValuePrinter {

    public static function serialize($value) {
        if (is_array($value) || $value instanceof \Traversable) {
            return self::serializeTraversable($value);

        } else if (is_object($value)) {

            if ($value instanceof \Exception) {
                return self::serializeException($value);
            } else if ($value instanceof \DateTime) {
                return self::serializeObject($value, '(' . $value->format('c') . ')');
            } else if (method_exists($value, '__toString')) {
                return self::serializeObject($value, '(' . self::serialize($value->__toString()) . ')');
            } else {
                return self::serializeObject($value);
            }

        } else if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';

        } else {
            return var_export($value, true);
        }
    }

    /**
     * @param array|\Traversable $traversable
     * @return string
     */
    private static function serializeTraversable($traversable) {
        $withKeys = array();
        $values = array();
        $onlyNumericKeys = true;

        foreach ($traversable as $key => $item) {
            $onlyNumericKeys = $onlyNumericKeys && is_int($key);

            $exported = self::serialize($item);
            $withKeys[] = self::serialize($key) . ': ' . $exported;
            $values[] = $exported;
        }

        $content = '[' . implode(', ', $onlyNumericKeys ? $values : $withKeys) . ']';

        if (is_object($traversable)) {
            return self::serializeObject($traversable, $content);
        } else {
            return $content;
        }
    }

    /**
     * @param \Exception $exception
     * @return string
     */
    private static function serializeException(\Exception $exception) {
        $appendix = '';

        $info = array_merge(
            ($exception->getMessage() ? array(self::serialize($exception->getMessage())) : array()),
            ($exception->getCode() ? array(self::serialize($exception->getCode())) : array())
        );

        if ($info) {
            $appendix = '(' . implode(', ', $info) . ')';
        }

        if ($exception->getPrevious()) {
            $appendix .= ' <- ' . self::serializeException($exception->getPrevious());
        }

        return self::serializeObject($exception, $appendix);
    }

    /**
     * @param object $object
     * @param string $appendix
     * @return string
     */
    private static function serializeObject($object, $appendix = '') {
        return '<' . get_class($object) . '>' . $appendix;
    }
}