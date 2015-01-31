<?php
/**
* @package PHPArray
*/

$class = 'ArrayObjRef';

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

	public function &getData(&$v) 
	{
		$r = &$v;
		#$r[0] = &$v[0]; // testArray() will fail
		return $r;
	}

	public function testArray()
	{
		global $class;
		$v = $this->vector1;
		$r = $this->getData($v);
		$r[0] = 5;

		$this->assertEquals(3.3, $v[0]);
	}

	public function testInvalidArrayObjectsMethods()
	{
		// Other ArrayObject methods won't work.
		global $class;
		$v = $this->vector;
		$A = new $class($v);
		$r = $A->getArrayCopy();

		$this->assertEquals(array(), $r);
	}

// End ArrayObjRef specific

	/**
     * @expectedException DomainException
	 */
	public function testARealCheckValue_Error()
	{
		global $class;
		$R_class = $class.'_R';
		$v = $this->vector_R_error;
		$A = new $R_class($v);
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
		$r = $A['x'];
	}

	public function testAssignNonExistingKey_Error()
	{
		global $class;
		$v = $this->vector1;
		$A = new $class($v);
		$A['x'] = 5;

		$this->assertEquals(5, $A['x']);
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
