<?php

namespace icework;

use function foo\func;

define("IW_UTILS_FIRST_IN_ARRAY", "@first");
define("IW_UTILS_LAST_IN_ARRAY", "@last");
define("IW_UTILS_COLLECT", '@collect');

//define("IW_GUID_CASE_INSENSITIVE", 0x00);
define("IW_GUID_CASE_LOWER", 0x01);
define("IW_GUID_CASE_UPPER", 0x02);
define("IW_GUID_FORMAT_BRACKETS", 0x04);
define("IW_GUID_FORMAT_NO_CHUNK_DIVIDED", 0x08);

// find options
define("IW_UNIQUE_OPTION", 0x01);
define("IW_PATH_DEFAULT_DELIMITER", ".");
# IS SECTION

class IwException extends \Exception {}

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


# ARRAY SECTION

/**
 * Get First element from array. Safe for pointer (uses current() and other array system utils).
 * @param array $input
 * @return mixed
 */
function array_first(array $input) {
  reset($input);
  return current($input);
}

/**
 * Get last element. Safe for pointer.
 * @param array $input
 * @return mixed
 */
function array_last(array $input) {
  return end($input);
}

/**
 * Extracting data by specific path. Examples:
 *
 * Using path of the nested properties (or array keys)
 * path: foo/bar
 * input: (object)['foo' => ['bar' => 'get me']]
 * result: "get me"
 *
 * Using @first (first in array) anchor
 * path: foo/bar/@first/me
 * input: (object)['foo' => ['bar' => [
 *  ['me' => 'one'], ['me' => 'two']
 * ]]]
 * result: "one"
 *
 * Using @last (last in array) anchor
 * path: foo/bar/@last/me
 * input: (object)['foo' => ['bar' => [
 *  ['me' => 'one'], ['me' => 'two']
 * ]]]
 * result: "two"
 *
 * Using @collect (all values) anchor. For getting all values from collections. Supporting nested anchors.
 * Use IW_UNIQUE_OPTION for unique results
 * path: foo/bar/@collect/models/@collect/prop with IW_UNIQUE_OPTION
 * input: (object)['foo' => ['bar' => [
 *  ['models' => [[
 *      'prop' => 'Property 1',
 *    ], [
 *      'prop' => 'Property 2',
 *    ]],
 *  ['models' => [[
 *      'prop' => 'Property 2',
 *  ], [
 *      'prop' => 'Property 3',
 *  ]]
 * ]]]
 * result: ["Property 1", "Property 2", "Property 2"]
 *
 *
 * @param array|object $input Input data
 * @param string $path Extraction path
 * @param string $delimiter Path delimiter, default [[IW_PATH_DEFAULT_DELIMITER]]
 * @param int $options for now support only IW_UNIQUE_OPTION
 * @return array|mixed
 */
function extract_element_by_path($input, string $path, string $delimiter=IW_PATH_DEFAULT_DELIMITER, int $options=0) {
  // empty path protection
  if (trim($path)==='' || $path===null) {
    return $input;
  }

  $uniqueMap=[];

  $enter=function($pathItems, $element, $deepIndex) use(&$enter, &$uniqueMap, $delimiter, $path, $options) {
    $pathItem=trim(array_shift($pathItems));
    $isLastOfPath=empty($pathItems);

    if (!is_array($element) && !is_object($element)) {
      throw new IwException(sprintf("Invalid element type encountered, array or object expected. Path {%s}, Item {%s}, Item type {%s}",
        $path,
        $pathItem,
        gettype($element)
      ));
    }
    // processing IW_UTILS_COLLECT anchor
    if ($pathItem===IW_UTILS_COLLECT) {
      if (!is_array($element)) {
        throw new IwException(sprintf("The element for which '%s' anchor will be applied must be an array. Path {%s}, Item {%s}, Item type {%s}",
          IW_UTILS_COLLECT,
          $path,
          $pathItem,
          gettype($element)
        ));
      }
      // @collect - if last item of path, then no recursive selection
      if ($isLastOfPath) {
        return $options & IW_UNIQUE_OPTION
          ? array_unique($element)
          : $element;
      }
      // but if the path is not passed
      // extraction the current path for recursive calls
      $results=[];
      // and for each
      foreach ($element as $item) {
        // recursive call with $currentPath
        $result=$enter($pathItems, $item, $deepIndex + 1);

        // if nested calls have already @collect extraction
        if (is_array($result)) {
          $results=array_merge($results, $result);
        } else {
          // check and collect
          if (!is_scalar($result)) {
            $typeof=gettype($result);
            throw new IwException(sprintf("Selection of '%s' anchor result must be an scalar type, '%s' given. Path {%s}, Item {%s}, Item type {%s}",
              IW_UTILS_COLLECT,
              $typeof,
              $path,
              $pathItem,
              gettype($element)
            ));
          }

          if ($options & IW_UNIQUE_OPTION) {
            if (!isset($uniqueMap[$result])) {
              $results[]=$result;
              $uniqueMap[$result]=true;
            }
            continue;
          }

          $results[]=$result;
        }
      }
      return $results;
    }

    $nextElement=null;
    // if array selector
    $isArraySelection=
      is_numeric($pathItem) ||
      $pathItem===IW_UTILS_FIRST_IN_ARRAY ||
      $pathItem===IW_UTILS_LAST_IN_ARRAY;

    if ($isArraySelection) {
      // if array selection and element is not array, then is so bad
      if (!is_array($element)) {
        throw new IwException(sprintf("Invalid element type encountered, array expected. Path {%s}, Item {%s}, Item type {%s}",
          $path,
          $pathItem,
          gettype($element)
        ));
      }

      switch ($pathItem) {
        case IW_UTILS_FIRST_IN_ARRAY: $nextElement=array_first($element); break;
        case IW_UTILS_LAST_IN_ARRAY: $nextElement=array_last($element); break;
        default:
          // default extracting by index
          if (!array_key_exists($pathItem, $element)) {
            throw new IwException(sprintf("Index %s out of bounds. Path {%s}, Item {%s}, Item type {%s}",
              $pathItem,
              $path,
              $pathItem,
              gettype($element)
            ));
          }
          $nextElement=$element[$pathItem];
      }

      return $isLastOfPath
        ? $nextElement
        : $enter($pathItems, $nextElement, $deepIndex + 1);
    }

    // check key or property access
    // the "isset" function is not suitable, because null is also a value
    $isGettable=false;
    if (is_array($element)) { // classic arrays
      $isGettable=array_key_exists($pathItem, $element);
    } elseif ($element instanceof \ArrayAccess) { // ArrayAccess interface
      $isGettable=$element->offsetExists($pathItem);
    } else { // default behavior = stdClass
      $isGettable=property_exists($element, $pathItem);
    }

    if (!$isGettable && is_object($element)) {
      throw new IwException(sprintf("Unknown property '%s'. Path {%s}, Item {%s}, Class {%s} ",
        $pathItem,
        $path,
        $pathItem,
        get_class($element)
      ));
    }
    if (!$isGettable && is_array($element)) {
      throw new IwException(sprintf("The key '%s' not found. Path {%s}, Item {%s}, Item type {%s} ",
        $pathItem,
        $path,
        $pathItem,
        gettype($element)
      ));
    }
    $nextElement=is_array($element)
      ? $element[$pathItem]
      : $element->$pathItem;

    return $isLastOfPath
      ? $nextElement
      : $enter($pathItems, $nextElement, $deepIndex + 1);
  };
  return $enter(explode($delimiter, trim($path)), $input, 0);
}