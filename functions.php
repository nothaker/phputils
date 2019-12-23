<?php

define("IW_UTILS_FIRST_IN_ARRAY", "@first");
define("IW_UTILS_LAST_IN_ARRAY", "@last");

//define("IW_GUID_CASE_INSENSITIVE", 0x00);
define("IW_GUID_CASE_LOWER", 0x01);
define("IW_GUID_CASE_UPPER", 0x02);
define("IW_GUID_FORMAT_BRACKETS", 0x04);
define("IW_GUID_FORMAT_NO_CHUNK_DIVIDED", 0x08);

/**
 * Default case insensitive, format - chunks divided by '-' with no brackets.
 * Setting other formats: IW_GUID_CASE_LOWER or IW_GUID_CASE_UPPER, IW_GUID_FORMAT_BRACKETS, IW_GUID_FORMAT_NO_CHUNK_DIVIDED combined with binary "or" operation
 * @param $guid
 * @param int $options IW_GUID_CASE_LOWER, IW_GUID_CASE_UPPER, IW_GUID_FORMAT_BRACKETS, IW_GUID_FORMAT_NO_CHUNK_DIVIDED
 * @return false|int
 */
function is_guid($guid, $options=0) {
  $case=$options & 0x03;
  $enableBrackets=$options & IW_GUID_FORMAT_BRACKETS;
  $enableNoDividers=$options & IW_GUID_FORMAT_NO_CHUNK_DIVIDED;

  $hexChunkPattern=$case>IW_GUID_CASE_LOWER
    ? 'A-F0-9'
    : 'a-f0-9';

  $bodyPattern=$enableNoDividers
    ? '[' . $hexChunkPattern . ']{32}'
    : implode('\-', [
      '[' . $hexChunkPattern . ']{8}',
      '[' . $hexChunkPattern . ']{4}',
      '[' . $hexChunkPattern . ']{4}',
      '[' . $hexChunkPattern . ']{4}',
      '[' . $hexChunkPattern . ']{12}',
    ]);

  $pattern=
    '/^' .
    ($enableBrackets ? '\{' . $bodyPattern . '\}' : $bodyPattern) .
    '$/' .
    ($case===0 ? 'i' : ''); // if insensitive case

  return preg_match($pattern, $guid);
}

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
    $isArraySelector=is_numeric($pathElement) || in_array($pathElement, [IW_UTILS_FIRST_IN_ARRAY, IW_UTILS_LAST_IN_ARRAY]);
    if ($isArraySelector && !is_array($obj)) {
      return null;
    }
    if ($isArraySelector) {
      switch ($pathElement) {
        case IW_UTILS_FIRST_IN_ARRAY:
          # PHP >= 7.3
          if (function_exists('array_key_first')) {
            return $obj[array_key_first($obj)];
          }
          $keys=array_keys($obj);
          return $obj[$keys[0]];
        case IW_UTILS_LAST_IN_ARRAY:
          # PHP >= 7.3
          if (function_exists('array_key_last')) {
            return $obj[array_key_last($obj)];
          }
          $keys=array_keys($obj);
          return $obj[$keys[count($keys) - 1]];
      }
      return $obj[$pathElement];
    }
    return property_exists($obj, $pathElement) ? $obj->{$pathElement} : null;
  };
  return array_reduce(explode($delimiter, $path), $each, $object);
}