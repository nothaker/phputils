# phputils
boilerplate code

```php
/**
 * @param stdClass|array $object 
 * @param string $path Search path
 * @param string $delimiter Path delimiter
 * @return mixed 
 */
function extract_element_by_path($object, $path, $delimiter, $options);
```
```php
$target=new stdClass();
$target->prop="Test me";
$test=new stdClass();
$test->foo=new stdClass();
$test->foo->bar=[$target];

echo extract_element_by_path($object, 'foo.bar.0.prop');
echo extract_element_by_path($object, 'foo/bar/0/prop', '/');
echo extract_element_by_path($object, 'foo/bar/@first/prop', '/');
echo extract_element_by_path($object, 'foo/bar/@last/prop', '/');
$array=extract_element_by_path($object, 'foo/bar', '/');
```

or usable with @collect anchor

```php
$array=[
  'foo'=>[
    ['bar' => 1],
    ['bar' => 2],
    ['bar' => 2],
    ['bar' => 4]
  ]
];

// no grouping

$values=extract_element_by_path($array, 'foo/@collect/bar', '/');
// $values == [1,2,2,4]

// with grouping
// also support multiple @collect anchors
$values=extract_element_by_path($array, 'foo/@collect/bar', '/', IW_UNIQUE_OPTION);
// $values == [1,2,4]
```

```php
function is_guid($guid, $options=0);
```
```php
echo is_guid('51ad8a36-7a8c-40aa-9c7b-ef55c2b31a6b') 
  ? 'yeap' 
  : 'nope'; // yeap

echo is_guid('51AD8A36-7A8C-40AA-9C7B-EF55C2B31A6B', IW_GUID_CASE_LOWER) 
  ? 'yeap' 
  : 'nope'; // nope
echo is_guid('{51ad8a36-7a8c-40aa-9c7b-ef55c2b31a6b}', IW_GUID_CASE_LOWER | IW_GUID_FORMAT_BRACKETS) 
  ? 'yeap' 
  : 'nope'; // yeap, strict case
echo is_guid('{51ad8a367a8c40aa9c7bef55c2b31a6b}', IW_GUID_CASE_LOWER|IW_GUID_FORMAT_BRACKETS|IW_GUID_FORMAT_NO_CHUNK_DIVIDED)
  ? 'yeap'
  : 'nope'; // yeap
```