<?php

!defined("ICEWORK_UTILS_FIRST_IN_ARRAY") && define("ICEWORK_UTILS_FIRST_IN_ARRAY", "@first");
!defined("ICEWORK_UTILS_LAST_IN_ARRAY") && define("ICEWORK_UTILS_LAST_IN_ARRAY", "@last");

/**
 * Find property in object (fake recursive)
 * @param $object
 * @param $path
 * @param string $delimiter
 * @return mixed
 */
function find_property_in_object($object, $path, $delimiter='.') {
  $path=trim($path, $delimiter . ' '); // clear space and delimiter
  if ($path==='') {
    return $object;
  }
  $each=function ($obj, $pathElement) {
    if (!is_array($obj) && !is_object($obj)) {
      return null;
    }
    $isArraySelector=is_numeric($pathElement) || in_array($pathElement, [ICEWORK_UTILS_FIRST_IN_ARRAY, ICEWORK_UTILS_LAST_IN_ARRAY]);
    if ($isArraySelector && !is_array($obj)) {
      return null;
    }
    if ($isArraySelector) {
      switch ($pathElement) {
        case ICEWORK_UTILS_FIRST_IN_ARRAY:
          # PHP >= 7.3
          if (function_exists('array_key_first')) {
            return $obj[array_key_first($obj)];
          }
          $keys=array_keys($obj);
          return $obj[$keys[0]];
        case ICEWORK_UTILS_LAST_IN_ARRAY:
          # PHP >= 7.3
          if (function_exists('array_key_last')) {
            return $obj[array_key_last($obj)];
          }
          $keys=array_keys($obj);
          return $obj[$keys[count($keys) - 1]];
      }
      return $obj[$pathElement];
    }
    return $obj->{$pathElement};
  };
  return array_reduce(explode($delimiter, $path), $each, $object);
}