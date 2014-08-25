<?php

namespace Baliame\Utils\Interoperability;

class TypeNormalization {
    /**
     * Normalizes the boolean values 'true' and 'false' to string.
     * Does nothing if the value is already a string.
     *
     * @param boolean $value
     * @return string
     */
    public static function normalizeBooleanToString($value)
    {
        if (is_string($value)) {
            $value = static::normalizeStringToBoolean($value);
        }
        if (!is_bool($value)) {
            $value = boolval($value);
        }
        return $value ? 'true' : 'false';
    }

    /**
     * Normalizes strings (e.g. "true", "false") to native PHP boolean values.
     *
     * @param string|bool $value
     *   The value to convert.
     *
     * @return bool
     *   The boolean representation of the value passed in the XML.
     *
     * @throws \InvalidArgumentException
     *   If $value is not a string, boolean or is a string that doesn't contain
     *   'true' or 'false' case-insensitively, or '0' or '1'.
     */
    public static function normalizeStringToBoolean($value)
    {
        if (is_string($value)) {
            if (!strcasecmp($value, 'true') || $value === '1') {
                $value = true;
            } elseif (!strcasecmp($value, 'false') || $value === '0') {
                $value = false;
            } else {
                throw new \InvalidArgumentException(
                    'Invalid string argument, expecting "true", "false", "1" or "0": ' . $value
                );
            }
        } elseif ($value === null) {
            return null;
        } elseif (!is_bool($value)) {
            throw new \InvalidArgumentException(
                'Expecting argument to be a string or boolean: ' . gettype($value) . ' passed'
            );
        }

        return $value;
    }

    /**
     * Normalizes UNIX timestamps to the ISO format.
     *
     * @param int $date
     *   A timestamp value to convert.
     * @return string
     *   The ISO representation of the date.
     * @throws \InvalidArgumentException
     *   If the date is not a timestamp or numeric string.
     */
    public static function normalizeDateToString($date) {
        if (is_null($date)) {
            return null;
        }
        elseif (!is_int($date) && !is_numeric($date)) {
            throw new \InvalidArgumentException('Date must be a number or a numeric string, got '. gettype($date) . ' (' . $date . ')');
        }
        // Let's be very specific here. Demandware might have problems with anything that doesn't look like whatever
        // it created.
        return gmdate('Y-m-d\TH:i:s.000\Z', $date);
    }

    /**
     * Normalizes UNIX timestamps to the ISO format without the time part.
     *
     * @param int $date
     *   A timestamp value to convert.
     * @return string
     *   The ISO representation of the date.
     * @throws \InvalidArgumentException
     *   If the date is not a timestamp or numeric string.
     */
    public static function normalizeDateToStringWithoutTime($date) {
        if (is_null($date)) {
            return null;
        }
        elseif (!is_int($date) && !is_numeric($date)) {
            throw new \InvalidArgumentException('Date must be a number or a numeric string, got '. gettype($date) . ' (' . $date . ')');
        }
        // Let's be very specific here. Demandware might have problems with anything that doesn't look like whatever
        // it created.
        return gmdate('Y-m-d\Z', $date);
    }

    /**
     * Normalizes input date values to UNIX timestamps.
     *
     * @param string|int|float $date
     *   A date value to convert to a UNIX timestamp.
     * @return int
     *   A UNIX timestamp corresponding to the date string.
     * @throws \InvalidArgumentException
     *   If the date is not a number or parseable date string.
     */
    public static function normalizeStringToDate($date) {
        if (is_null($date)) {
            return null;
        }
        elseif (is_string($date)) {
            $time = strtotime($date);
            if ($time === false) {
                throw new \InvalidArgumentException(
                    'Invalid string argument, expecting parseable date: ' . $date
                );
            }
            return $time;
        }
        elseif (is_int($date) || is_float($date)) {
            return intval($date);
        }
        else {
            throw new \InvalidArgumentException(
                'Invalid date argument, expecting string, integer or float, got ' . gettype($date)
            );
        }
    }

    /**
     * Normalizes UNIX timestamps to the ISO format.
     *
     * @param int $date
     *   A timestamp value to convert.
     * @return string
     *   The ISO representation of the time. (An ISO-8601 datetime, with the date part stripped).
     * @throws \InvalidArgumentException
     *   If the date is not a timestamp or numeric string.
     */
    public static function normalizeTimeToString($date) {
        if (is_null($date)) {
            return null;
        }
        if (!is_int($date) && !is_numeric($date)) {
            throw new \InvalidArgumentException('Date must be a number or a numeric string, got '. gettype($date) . '(' . $date . ')');
        }
        return gmdate('H:i:s.000\Z', $date);
    }

    /**
     * Normalizes input time values to UNIX timestamps.
     *
     * @param string|int|float $date
     *   A time value to convert to a UNIX timestamp.
     * @return int
     *   A UNIX timestamp corresponding to the time string.
     * @throws \InvalidArgumentException
     *   If the date is not a number or parseable time string.
     */
    public static function normalizeStringToTime($date) {
        return static::normalizeStringToDate($date);
    }
}