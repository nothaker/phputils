<?php

namespace icework;

include __DIR__ . "/functions.php";

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
  /**
   * @dataProvider findElementByPathProvider
   * @param $path
   * @param $testData
   * @param $expected
   * @param int $options
   * @throws \Exception
   */
  public function testExtractElementByPath($path, $testData, $expected, $options=0) {
    try {
      $actual=extract_element_by_path($testData, $path, '/', $options);
//      echo "", $this->getName(), " expected=' ", print_r($expected ,true), "' actual = '", print_r($actual, true), "'\n";
      $this->assertEquals($expected, $actual, $path);
    } catch (\Exception $e) {
      $isExpected=$expected instanceof IwException && $e instanceof IwException;
      $this->assertTrue($expected instanceof IwException && $e instanceof IwException);

      if (!$isExpected) {
        echo "", $this->getName(), " exception {", get_class($e), "} with message='", $e->getMessage(), "' trace='", $e->getTraceAsString() , "'\n";
        throw $e;
      }
    }
  }

  public function findElementByPathProvider() {
    return [
      // path of object/array
      '1#foo/bar' => ['foo/bar', (object)[
        'foo' => [
          'bar' => 'tested'
        ]
      ], 'tested', 0],
      '2#foo/bar' => ['foo/bar', (object)[
        'foo' => (object)[
          'bar' => 'tested'
        ]
      ], 'tested', 0],
      '3#foo/bar' => ['foo/bar', [
        'foo' => [
          'bar' => 'tested'
        ]
      ], 'tested', 0],
      // array access testing
      '4#foo/0/bar' => ['foo/0/bar', [
        'foo'=>[[
          'bar'=>'tested'
        ], [
          'bar' => 'no'
        ]]
      ], 'tested', 0],
      '5#foo/1/bar' => ['foo/1/bar', [
        'foo'=>[[
          'bar'=>'no'
        ], [
          'bar' => 'tested'
        ]]
      ], 'tested', 0],
      '6#foo/@first/bar' => ['foo/@first/bar', [
        'foo' => [[
          'bar'=>'tested'
        ], [
          'bar' => 'no'
        ]]
      ], 'tested', 0],

      '7#foo/@last/bar' => ['foo/@last/bar', [
        'foo' => [[
          'bar'=>'no'
        ], [
          'bar' => 'tested'
        ]]
      ], 'tested', 0],
      '8#foo/0' => ['foo/0', [
        'foo' => [1, 2, 3]
      ], 1, 0],
      '9#foo/@last' => ['foo/@last', [
        'foo' => [1, 2, 3]
      ], 3, 0],

      // @collect testing
      // simply
      '10#foo/@collect' => ['foo/@collect', [
        'foo' => ['tested', 'tested']
      ], ['tested', 'tested'], 0],

      '11#foo/@collect' => ['foo/@collect', [
        'foo' => ['tested', 'tested']
      ], ['tested'], IW_UNIQUE_OPTION],


      // with forwarding the properties
      '12#foo/bar/@collect/my/hand' => ['foo/bar/@collect/my/hand', [
        'foo' => (object)[
          'bar' => [
            (object)['my' => ['hand' => 'tested1']],
            (object)['my' => ['hand' => 'tested1']],
            (object)['my' => ['hand' => 'tested2']],
          ],
          'dummy' => []
        ]
      ], ['tested1', 'tested1', 'tested2'], 0],

      // nested @collect anchor
      '13#foo/bar/@collect/my/body/@collect/hand' => ['foo/bar/@collect/my/body/@collect/hand', [
        'foo' => (object)[
          'bar' => [
            (object)['my' => [
              'body' => [[
                'hand' => 'tested1'
              ],[
                'hand' => 'tested2'
              ]]
            ]],
            (object)['my' => [
              'body' => [[
                'hand' => 'tested1'
              ],[
                'hand' => 'tested2'
              ]]
            ]],
            (object)['my' => [
              'body' => [[
                'hand' => 'tested3'
              ],[
                'hand' => 'tested4'
              ]]
            ]],
            (object)['my' => [
              'body' => [[
                'hand' => 'tested5'
              ],[
                'hand' => 'tested5'
              ]]
            ]],
          ],
          'dummy' => []
        ]
      ], ['tested1', 'tested2', 'tested3', 'tested4', 'tested5'], IW_UNIQUE_OPTION],


      // throws
      '90#foo/bar' => ['foo/bar', [
        'foo' => 'bar'
      ], new IwException(), 0],

      '91#foo/@collect/my/hand' => ['foo/@collect/my/hand', [
        'foo' => (object)[
        ]
      ], new IwException(), 0],

    ];
  }
}