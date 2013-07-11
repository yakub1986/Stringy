<?php

namespace Stringy;

class Stringy {

    /**
     * Checks that the first argument ($ars[0]) supplied to the private method
     * is a string, and throws an exception if not. And if not provided, sets
     * the second argument ($ars[1]) to mb_internal_encoding(). It then calls
     * the static method.
     *
     * @param   string  $method  Private static method being called
     * @param   array   $args    Array of arguments supplied to the method call
     * @return  string  String returned by the private method called
     * @throws  BadMethodCallException    If $method doesn't exist
     * @throws  InvalidArgumentException  If $args[0] is not of type string
     */
    public static function __callStatic($method, $args) {
        if (!method_exists(__CLASS__, $method))
            throw new \BadMethodCallException("Method doesn't exist");

        if (!is_string($args[0])) {
            // Scalar type hinting isn't allowed, so have to throw exceptions
            $message = sprintf('Argument of type string expected, instead ' .
                'received an argument of type %s', gettype($args[0]));

            throw new \InvalidArgumentException($message, $args[0]);
        }

        // Set the character encoding ($args[1]/$encoding) if not provided
        if (sizeof($args) == 1 || !$args[1])
            $args[1] = mb_internal_encoding();

        // Set character encoding for multibyte regex
        mb_regex_encoding($args[1]);

        return forward_static_call_array(array(__CLASS__, $method), $args);
    }

    /**
     * Converts the first character of the supplied string to upper case.
     *
     * @param   string  $string    String to modify
     * @param   string  $encoding  The character encoding
     * @return  string  String with the first character being upper case
     */
    private static function upperCaseFirst($string, $encoding) {
        $first = mb_substr($string, 0, 1, $encoding);
        $rest = mb_substr($string, 1, mb_strlen($string, $encoding) - 1, $encoding);

        return mb_strtoupper($first, $encoding) . $rest;
    }

    /**
     * Converts the first character of the supplied string to lower case.
     *
     * @param   string  $string    String to modify
     * @param   string  $encoding  The character encoding
     * @return  string  String with the first character being lower case
     */
    private static function lowerCaseFirst($string, $encoding) {
        $first = mb_substr($string, 0, 1, $encoding);
        $rest = mb_substr($string, 1, mb_strlen($string, $encoding) - 1, $encoding);

        return mb_strtolower($first, $encoding) . $rest;
    }

    /**
     * Returns a camelCase version of a supplied string. Trims surrounding
     * spaces, capitalizes letters following digits, spaces, dashes and
     * underscores, and removes spaces, dashes, underscores.
     *
     * @param   string  $string    String to convert to camelCase
     * @param   string  $encoding  The character encoding
     * @return  string  String in camelCase
     */
    private static function camelize($string, $encoding) {
        $camelCase = preg_replace_callback(
            '/[-_\s]+(.)?/u',
            function ($matches) use (&$encoding) {
                return $matches[1] ? mb_strtoupper($matches[1], $encoding) : "";
            },
            self::lowerCaseFirst(trim($string), $encoding)
        );

        $camelCase = preg_replace_callback(
            '/[\d]+(.)?/u',
            function ($matches) use (&$encoding) {
                return mb_strtoupper($matches[0], $encoding);
            },
            $camelCase
        );

        return $camelCase;
    }

    /**
     * Returns an UpperCamelCase version of a supplied string. Trims surrounding
     * spaces, capitalizes letters following digits, spaces, dashes and
     * underscores, and removes spaces, dashes, underscores.
     *
     * @param   string  $string  String to convert to UpperCamelCase
     * @param   string  $encoding  The character encoding
     * @return  string  String in UpperCamelCase
     */
    private static function upperCamelize($string, $encoding) {
        $camelCase = self::camelize($string, $encoding);

        return self::upperCaseFirst($camelCase, $encoding);
    }

    /**
     * Returns a lowercase and trimmed string seperated by dashes. Dashes are
     * inserted before uppercase characters (with the exception of the first
     * character of the string), and in place of spaces as well as underscores.
     *
     * @param   string  $string    String to convert
     * @param   string  $encoding  The character encoding
     * @return  string  Dasherized string
     */
    private static function dasherize($string, $encoding) {
        $dasherized = mb_ereg_replace('\B([A-Z])', '-\1', trim($string));
        $dasherized = mb_ereg_replace('[-_\s]+', '-', $dasherized);

        return mb_strtolower($dasherized, $encoding);
    }

    /**
     * Returns a lowercase and trimmed string seperated by underscores.
     * Underscores are inserted before uppercase characters (with the exception
     * of the first character of the string), and in place of spaces as well as
     * dashes.
     *
     * @param   string  $string    String to convert
     * @param   string  $encoding  The character encoding
     * @return  string  Underscored string
     */
    private static function underscored($string, $encoding) {
        $underscored = mb_ereg_replace('\B([A-Z])', '_\1', trim($string));
        $underscored = mb_ereg_replace('[-_\s]+', '_', $underscored);

        return mb_strtolower($underscored, $encoding);
    }

    /**
     * Returns a case swapped version of a string.
     *
     * @param   string  $string    String to swap case
     * @param   string  $encoding  The character encoding
     * @return  string  String with each character's case swapped
     */
    private static function swapCase($string, $encoding) {
        $swapped = preg_replace_callback(
            '/[\S]/u',
            function ($match) use (&$encoding) {
                if ($match[0] == mb_strtoupper($match[0], $encoding))
                    return mb_strtolower($match[0], $encoding);
                else
                    return mb_strtoupper($match[0], $encoding);
            },
            $string
        );

        return $swapped;
    }

    /**
     * Capitalizes the first letter of each word in a string, after trimming.
     * Ignores the case of other letters, allowing for the use of acronyms.
     * Also accepts an array, $ignore, allowing you to list words not to be
     * capitalized.
     *
     * @param   string  $string    String to titleize
     * @param   string  $encoding  The character encoding
     * @param   array   $ignore    An array of words not to capitalize
     * @return  string  Titleized string
     */
    private static function titleize($string, $encoding, $ignore = null) {
        $titleized = preg_replace_callback(
            '/([\S]+)/u',
            function ($match) use (&$encoding, &$ignore) {
                if ($ignore && in_array($match[0], $ignore))
                    return $match[0];
                return Stringy::upperCaseFirst($match[0], $encoding);
            },
            trim($string)
        );

        return $titleized;
    }
}

?>