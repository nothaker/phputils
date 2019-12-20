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