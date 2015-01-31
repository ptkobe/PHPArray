<?php
/**
* @package PHPArray
*/

$class = 'V';

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
		$r = $A->getVector();

		$this->assertEquals(array(1,2,  3,4,5,6,7,8,9), $r);
	}

	public function testARealCheckValue()
	{
		global $class;
		$v = array(1,2,'a'=>3,4,5,6,7,8,9);
		$checkValue = function ($v) {return (is_numeric($v) && !is_string($v));};
		$A = new $class($v, $checkValue);
		$r = $A->getVector();

		$this->assertEquals(array(1,2,  3,4,5,6,7,8,9), $r);
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
		$r = $A->getVector();

		$this->assertEquals(array(array(1,2,'a'=>3,4,5,6,7,8,9)), $r);
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

	public function testTestFor()
	{
		global $class;
		$v = array(1,2,'a'=>3,4,5,6,7,8,9);
		$A = new $class($v);
		$r = $A->testFor();

		$this->assertEquals(45, $r);
	}

	public function testFor()
	{
		global $class;
		$v = array(1,2,'a'=>3,4,5,6,7,8,9);
		$A = new $class($v);
		
		$r = 0;
		for ($i = 0; $i < count($A); $i++) {
			$r += $A[$i];
		}
		$this->assertEquals(45, $r);
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
		$r = 0;
		foreach ($A as $k=>$val) { // Don't use $k=>$v as it will replace $A array.
			#echo $k, "\n";
			$A[$k] += 1; 
			$r += $val; // Notice val was not change. You may use current() instead.
		}
		$r += $A[2];

		$this->assertEquals(45+(3+1), $r);
	}

	public function testGetVector_1()
	{
		global $class;
		$v = $this->vector2;
		$A = new $class($v);
		$r = $A->getVector();
		$r['a'] = 5;

		$this->assertEquals(3.3, $A[0]);
	}

	public function testGetVector_2()
	{
		global $class;
		$v = $this->vector2;
		$A = new $class($v);
		$r = &$A->getVector();
		$r[0] = 5;

		$this->assertEquals(5, $A[0]);
	}

	public function testGetVector_3()
	{
		global $class;
		$v = $this->vector1;
		$v2 = array(1,2); 
		$v[2] = &$v2;
		$A = new $class($v);
		$r = &$A->getVector();
		$r[2][1] = 5;

		$this->assertEquals(5, $v2[1]);
	}

	public function testGetVectorClone_1()
	{
		global $class;
		$v = $this->vector1;
		$A = new $class($v);
		$r = &$A->getVectorClone();
		$r[0] = 5;

		$this->assertEquals(3.3, $A[0]);
	}

	public function testGetVectorClone_2()
	{
		global $class;
		$v = $this->vector1;
		$v2 = array(1,2); 
		$v[2] = &$v2;
		$A = new $class($v);
		$r = &$A->getVectorClone();
		$r[2][1] = 5;

		$this->assertEquals(2, $v2[1]);
	}

	public function testUnset()
	{
		global $class;
		#$vector2 = array( 'a' => 3.30,   1 => 8.26,  'c' => -3.89,  'd' => 8.20,      -1.51  );
		#$A      -> array(  0  => 3.30,   1 => 8.26,   2  => -3.89,   3  => 8.20, 4 => -1.51  );
		$v = $this->vector2;
		$A = new $class($v);
		unset($A[2]);

		$this->assertEquals(8.2, $A[2]);
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

	/**
	 * @expectedException PHPUnit_Framework_Error_Notice
	 */
	public function testNonExistingKey_Error()
	{
		global $class;
		$v = $this->vector;
		$A = new $class($v);
		$r = $A[10];
	}

	/**
	 * @expectedException PHPUnit_Framework_Error_Warning
	 */
	public function testInvalidKey_Error_2()
	{
		global $class;
		$v = $this->vector;
		$A = new $class($v);
		$r = $A['x'];
	}

	/**
	 * @expectedException PHPUnit_Framework_Error_Warning
	 */
	public function testAssignInvalidKey_Error_2()
	{
		global $class;
		$v = $this->vector1;
		$A = new $class($v);
		$A['x'] = 5;
	}

	/**
	 * @expectedException PHPUnit_Framework_Error_Notice
	 */
	public function testAssignNonExistingKey_Error()
	{
		global $class;
		$v = $this->vector1;
		$A = new $class($v);
		$A[10] = 5;
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
