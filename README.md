# phputils
boilerplate code

```php
/**
 * @param stdClass|array $object 
 * @param string $path Search path
 * @param string $delimiter Path delimiter
 * @return mixed 
 */
function find_property_in_object($object, $path, $delimiter);
```
```php
$target=new stdClass();
$target->prop="Test me";
$test=new stdClass();
$test->foo=new stdClass();
$test->foo->bar=[$target];

echo find_property_in_object($object, 'foo.bar.0.prop');
echo find_property_in_object($object, 'foo/bar/0/prop', '/');
echo find_property_in_object($object, 'foo/bar/@first/prop', '/');
echo find_property_in_object($object, 'foo/bar/@last/prop', '/');
$array=find_property_in_object($object, 'foo/bar', '/');
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