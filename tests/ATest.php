<?php
/**
* @package PHPArray
*/

$class = 'A';

// see autoloading
require_once '../'.$class.'.php';

// use to test ArrayObject instead
#$class = 'ArrayObject';

class PHPArrayTest extends PHPUnit_Framework_TestCase
{

	var $a = array(
		array(-1.01,   0.86,  -4.60,  3.31,  -4.81  ),
		array( 3.98,   0.53,  -7.04,  5.29,   3.55  ),
		#array( 3.30,   array(3,8.26),  -3.89,  8.20,  -1.51  ),
		array( 3.30,   8.26,  -3.89,  8.20,  -1.51  ),
		array( 4.43,   4.96,  -7.66, -7.33,   6.18  ),
		array( 7.31,  -6.43,  -6.16,  2.47,   5.58  ),
	);

	var $vector = array( 4.43,   4.96,  -7.66, -7.33,   6.18  );
	var $vector1 = array( 3.30,   array(3,8.26),  -3.89,  8.20,  -1.51  );
	var $vector2 = array( 'a'=>3.30,   1 => 8.26,  'c' => -3.89,  'd' => 8.20,  -1.51  );
	var $vector_R_error = array( 'a'=>'string',   1 => 8.26,  'c' => -3.89,  'd' => 8.20,  -1.51  );


	public function testArrayUnset()
	{
		global $class;
		$v = array(1,2,'a'=>3,4,5,6,7,8,9);
		$A = new $class($v);
		unset($v);
		$r = $A->getArray();

		$this->assertEquals(array(1,2,'a'=>3,4,5,6,7,8,9), $r);
	}

	public function testARealCheckValue()
	{
		global $class;
		$v = array(1,2,'a'=>3,4,5,6,7,8,9);
		$checkValue = function ($v) {return (is_numeric($v) && !is_string($v));};
		$A = new $class($v, $checkValue);
		$r = $A->getArray();

		$this->assertEquals(array(1,2,'a'=>3,4,5,6,7,8,9), $r);
	}

	/**
     * @expectedException DomainException
	 */
	public function testARealCheckValue_Error()
	{
		global $class;
		$v = $this->vector_R_error;
		$checkValue = function ($v) {return (is_numeric($v) && !is_string($v));};
		$A = new $class($v, $checkValue);
	}

	public function testAArrayCheckValue()
	{
		global $class;
		$v = array('x' => array(1,2,'a'=>3,4,5,6,7,8,9));
		$n = 9;
		$checkValue = function ($v) use ($n) {return ( is_array($v) && (count($v) == $n) );};
		$A = new $class($v, $checkValue);
		$r = $A->getArray();

		$this->assertEquals(array('x' => array(1,2,'a'=>3,4,5,6,7,8,9)), $r);
	}

	/**
     * @expectedException DomainException
	 */
	public function testAArrayCheckValue_Error()
	{
		global $class;
		$v = array($this->vector1);
		$n = 4;
		$checkValue = function ($v) use ($n) {return ( is_array($v) && (count($v) == $n) );};
		$A = new $class($v, $checkValue);
	}

	public function testTestForeach()
	{
		global $class;
		#$v = array(1,2,3,4,5);
		$v = $this->vector2;
		$A = new $class($v);
		$r = $A->testForeach();
		#$r[0] = 5;

		$this->assertEquals(5, $r);
	}

	public function testForeach()
	{
		global $class;
		$v = array(1,2,'a'=>3,4,5,6,7,8,9);
		$A = new $class($v);

		#$A->rewind();
		#echo 'key: ', $A->key(), "\n";
		#var_dump($A->current()); echo "\n";

		$r = 0;
		foreach ($A as $k=>$val) { // Don't use $k=>$v as it will replace $A array.
			#echo $k, "\n";
			#echo $val, "\n";
			$A[$k] += 1; 
			#echo $A->current(), "\n";
			#echo $val, "\n";
			$r += $val; // Notice val was not change. You may use current() instead.
		}
		$r += $A['a'];

		$this->assertEquals(45+(3+1), $r);
	}

	public function testTestWhile()
	{
		global $class;
		$v = $this->vector2;
		$A = new $class($v);
		$r = $A->testWhile();
		#$r[0] = 5;

		$this->assertEquals(5, $r);
	}

	public function testWhile()
	{
		#$vector2 = array( 'a'=>3.30,   1 => 8.26,  'c' => -3.89,  'd' => 8.20,  -1.51  );
		global $class;
		$v = array(1,2,'a'=>3,4,5,6,7,8,9);
		$A = new $class($v);
		
		$r = 0;
		$A->rewind();
		while ($A->valid()) {
			#echo $A->key(), "\n";
			#echo $A->current(), "\n";
			$k = $A->key();
			$A[$k] += 1; 
			$r += $A->current(); // current() was updated
			$A->next();
		}
		$r += $A['a'];

		$this->assertEquals(45+9+(3+1), $r);
	}

	public function testGetArray_1()
	{
		global $class;
		$v = $this->vector1;
		$A = new $class($v);
		$r = $A->getArray();
		$r[0] = 5;

		$this->assertEquals(3.3, $A[0]);
	}

	public function testGetArray_2()
	{
		global $class;
		$v = $this->vector1;
		$A = new $class($v);
		$r = &$A->getArray();
		$r[0] = 5;

		$this->assertEquals(5, $A[0]);
	}

	public function testGetArray_3()
	{
		global $class;
		$v = $this->vector1;
		$v2 = array(1,2); 
		$v[2] = &$v2;
		$A = new $class($v);
		$r = &$A->getArray();
		$r[2][1] = 5;

		$this->assertEquals(5, $v2[1]);
	}

	public function testGetArrayClone_1()
	{
		global $class;
		$v = $this->vector1;
		$A = new $class($v);
		$r = &$A->getArrayClone();
		$r[0] = 5;

		$this->assertEquals(3.3, $A[0]);
	}

	public function testGetArrayClone_2()
	{
		global $class;
		$v = $this->vector1;
		$v2 = array(1,2); 
		$v[2] = &$v2;
		$A = new $class($v);
		$r = &$A->getArrayClone();
		$r[2][1] = 5;

		$this->assertEquals(2, $v2[1]);
	}

	/**
	 * @expectedException PHPUnit_Framework_Error_Warning
	 */
	public function testInvalidKey_Error()
	{
		global $class;
		$v = $this->vector;
		$A = new $class($v);
		$r = $A[array(2)];
	}

	public function testInvalidKeyChain()
	{
		global $class;
		$v = $this->vector;
		$A = new $class($v);
		@$r = $A[array(2)] = 55;

		$this->assertEquals(55, $r);
	}

	/**
	 * @expectedException PHPUnit_Framework_Error_Warning
	 */
	public function testAssignInvalidKey_Error()
	{
		global $class;
		$v = $this->vector1;
		$A = new $class($v);
		$A[array(2)] = 5;
	}

	/**
	 * @expectedException PHPUnit_Framework_Error_Notice
	 */
	public function testNullKey_Error()
	{
		global $class;
		$v = $this->vector;
		$A = new $class($v);
		$r = $A[NULL];
	}

	public function testNullKey()
	{
		global $class;
		$v = $this->vector;
		$A = new $class($v);
		@$r = $A[NULL];

		$this->assertEquals(NULL, $r);
	}

	/**
	 * @expectedException PHPUnit_Framework_Error_Notice
	 */
	public function testNonExistingKey_Error()
	{
		global $class;
		$v = $this->vector;
		$A = new $class($v);
		$r = $A['x'];
	}

	public function testNonExistingKey()
	{
		global $class;
		$v = $this->vector;
		$A = new $class($v);
		@$r = $A['x'];

		$this->assertEquals(NULL, $r);
	}

	public function testAssignNonExistingKey()
	{
		global $class;
		$v = $this->vector1;
		$A = new $class($v);
		$A['x'] = 5;

		$this->assertEquals(5, $A['x']);
	}

	public function testAssignEmptyKey_Error()
	{
		global $class;
		$v = $this->vector1;
		$A = new $class($v);
		$A[] = 5;

		$this->assertEquals(5, $A[5]);
	}

	/**
	 * @expectedException PHPUnit_Framework_Error_Notice
	 */
	public function testNonExistingKeyUnset_Error()
	{
		global $class;
		$v = $this->vector;
		$A = new $class($v);
		unset($A['x']);
	}

	public function testSecondKey()
	{
		global $class;
		$v = $this->vector1;
		$A = new $class($v);

		$this->assertEquals(3, $A[1][0]);
	}

	// No link on ArrayObject's
	public function testLinkToInputArray()
	{
		global $class;
		$v = $this->vector1;
		$A = new $class($v);
		$v[1][0] = 5;

		$this->assertEquals(5, $A[1][0]);
	}

	// No meaning for ArrayObject's
	public function testUnsetInputArray()
	{
		global $class;
		$v = $this->vector1;
		$A = new $class($v);
		$v[1][0] = 5;
		unset($v);

		$this->assertEquals(5, $A[1][0]);
	}

	// ArrayObject don't have this method
	public function testOffsetSetRef()
	{
		global $class;
		$v = $this->vector;
		$v2 = $this->vector1;
		$A = new $class($v);
		$A->offsetSetRef(2,$v2);

		$this->assertEquals(3.3, $A[2][0]);
	}

	// ArrayObject don't have this method
	public function testOffsetSetRefLinkTo()
	{
		global $class;
		$v = $this->vector;
		$v2 = $this->vector1;
		$A = new $class($v);
		$A->offsetSetRef(2,$v2);
		$v2[0] = 5;
		unset($v2);

		$this->assertEquals(5, $A[2][0]);
	}

	public function testArrayObjRow()
	{
		global $class;
		$v = $this->vector;
		$v2 = $this->vector1;
		$A = new $class($v);
		$B = new $class($v2);
		$A[2] = $B;

		$this->assertEquals(3.3, $A[2][0]);
	}

	public function testArrayObjRowLinkTo()
	{
		global $class;
		$v = $this->vector;
		$v2 = $this->vector1;
		$A = new $class($v);
		$B = new $class($v2);
		$A[2] = $B;
		$B[0] = 5;

		$this->assertEquals(5, $A[2][0]);
	}

	public function testArrayObjThirdOffset()
	{
		global $class;
		$v = $this->vector;
		$v2 = $this->vector1;
		$A = new $class($v);
		$B = new $class($v2);
		$A[2] = $B;

		$this->assertEquals(8.26, $A[2][1][1]);
	}

}

?>
