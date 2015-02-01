<?php
/**
* @package PHPArray
*/

require_once '../PHPComplex/phpcomplex.php';

define('PHPARRAY_DOMAIN','phpjama');

if (!defined('PHPARRAY_CLASS')) {
	define('PHPARRAY_CLASS','PHPArray');
}

// This function exists only on PHP 5.5
if ( !function_exists('array_column') ) {
	function array_column ($a, $key, $keys = NULL) {
		$x = array();
		if ( is_null($keys) ) {
			foreach ($a as $r) {
				$x[] = $r[$key];
			}
		} else {
			reset($keys);
			$k = current($keys);
			foreach ($a as $r) {
				$x[$k] = $r[$key];
				$k = next($keys);
			}
		}
		return $x;
	}
}

/**
* Collection of static a_methods for calculus with arrays
*/ 
Class PHPArray
{
	static function a_get_row_dimension(&$a) {
		return count($a);
	}

	static function a_get_column_dimension(&$a) {
		reset($a);
		return count(current($a));
	}

	static function a_check_value ($v)
	{
		#echo $v."\n";
		return ( (is_numeric($v) && !is_string($v)) || is_complex($v) );
	}

	/**
	* static method &a_standardize (&$a)
	* Assumes array is checked.
	* Returns &$a as standardized $a.
	* Usage:
	*  $ck = C::a_standardize($a);
	* @param array $a 
	* @return array Column keys of $a
	*/
	static function &a_standardize (&$a)
	{
		reset($a);
		$ckeys = array_keys(current($a));
		foreach ($a as $k=>&$r) {
			$rcopy = $r;
			$r = array();
			foreach ($ckeys as $k) {
				$r[] = $rcopy[$k];
			}
		}
		return $ckeys;
	}
	
	static function &a_check (&$a, &$error = NULL)
	{
		$ret = FALSE;
		$error = array();
		if ( !is_array($a) ) {
			return $ret;
		}
		$m = count($a);
		if ( ($m == 0) ) {
			return $ret;
		}

		reset($a);
		$error = array(key($a), NULL);
		$r = current($a);
		if ( !is_array($r) ) {
			return $ret;
		}
		$n = count($r);
		if ( ($n == 0) ) {
			return $ret;
		}
		$ckeys = array_keys($r);
		foreach ($r as $k=>$v) {
			$error[1] = $k;
			if ( !static::a_check_value($v) ) {
				return $ret;
			}
		}

		$i = 1;
		$r = next($a);
		while ($r) {
			$error = array(key($a), NULL);
			if ( !is_array($r) ) {
				return $ret;
			}
			if ( (count($r) != $n) ) {
				return $ret;
			}
			$j = 0;
			foreach ($r as $k=>$v) {
				$error[1] = $k;
				// ??
				if ( ($k != $ckeys[$j]) ) {
					return $ret;
				}
				if ( !static::a_check_value($v) ) {
					return $ret;
				}
				$j++;
			}
			$r = next($a);
			$i++;
		}
		$error = NULL;
		return $a;
	}

	static function a_arrayrightdivide (&$a, &$b) 
	{
		if ( !is_array($a) ) {
			throw new InvalidArgumentException(dgettext(PHPARRAY_DOMAIN, 'THE first')
					. ' ' . dgettext(PHPARRAY_DOMAIN, 'argument must be an array'));
		}

		if ( !is_array($b) ) {
			
			throw new InvalidArgumentException(dgettext(PHPARRAY_DOMAIN, 'THE second')
					. ' ' . dgettext(PHPARRAY_DOMAIN, 'argument must be an array'));
		}

		$args = func_get_args(); // Remove if using parameter &...$args
		array_shift($args); 

		$f = function ($v1,$v2) {
			$call_class = PHPCOMPLEX_CLASS; 
			return $call_class::c_div($v1,$v2)->flat();
		};

		foreach ($args as $b) {
			switch ( gettype($b) ) {
			case 'array':
				reset($b);
				if ( (gettype(current($b)) == 'array') ) {
					// Check dims ?
					static::a_op_array($f, $a, $b);
				} else {
					// Check dims ?
					static::a_op_vector($f, $a, $b);
				}
				break;
				
			case 'array':
				static::a_op_scalar($f, $a, $b);
				break;
			default:
				throw new InvalidArgumentException(dgettext(PHPARRAY_DOMAIN, 'invalid arguments'));
			}
		}
	}

	/**
	* only checks the second array.
	*/
	static function &a_op_array (callable $f, &$a, &$b) 
	{
		if ( !is_array($b) ) {
			throw new InvalidArgumentException(dgettext(PHPARRAY_DOMAIN, 'argument must be an array'));
		}

		// Check dims ?

		reset($b);
		$rb = current($b);
		foreach ($a as &$r) {
			if ( !is_array($rb) ) {
				throw new DomainException(dgettext(PHPARRAY_DOMAIN, 'invalid array'));
			}
			$rt = array_map($f, $r, $rb);
			foreach ($rt as $v) {
				if ( !static::a_check_value($v) ) {
					throw new DomainException(dgettext(PHPARRAY_DOMAIN, 'invalid result'));
				}
			}
			$r = $rt;
			$rb = next($b);
		} unset($r);
		return $a;
	}

	static function &a_op_vector (callable $f, &$a, &$b) 
	{
		if ( !is_array($b) ) {
			throw new InvalidArgumentException(dgettext(PHPARRAY_DOMAIN, 'argument must be a vector'));
		}

		reset($b);
		$vb = current($b);
		foreach ($a as &$v) {
			if ( !static::a_check_value($vb) ) {
				throw new DomainException(dgettext(PHPARRAY_DOMAIN, 'invalid vector'));
			}
			$vt = $f($v,$vb);
			if ( !static::a_check_value($vt) ) {
				throw new DomainException(dgettext(PHPARRAY_DOMAIN, 'invalid result'));
			}
			$v = $vt;
			$lb = next($b);
		} unset($v);
		return $a;
	}

	static function &a_op_scalar (callable $f, &$a, &$b) 
	{
		if ( !is_array($b) ) {
			throw new InvalidArgumentException(dgettext(PHPARRAY_DOMAIN, 'argument must be a vector'));
		}

		if ( !static::a_check_value($b) ) {
			throw new DomainException(dgettext(PHPARRAY_DOMAIN, 'invalid scalar'));
		}
		foreach ($a as &$r) {
			foreach ($l as &$v) {
				$vt = $f($v,$b);
				if ( !static::a_check_value($vt) ) {
					throw new DomainException(dgettext(PHPARRAY_DOMAIN, 'invalid result'));
				}
				$v = $vt;
			} unset($v);
		} unset($r);
		return $a;
	}

}

function a_check (&$a, &$error = NULL)
{
	$class = PHPARRAY_CLASS;
	$method = __FUNCTION__;
	$ret = &$class::$method($a, $error);
	return !($ret === FALSE);
}

function &a_standardize (&$a)
{
	$class = PHPARRAY_CLASS;
	$method = __FUNCTION__;
	return $class::$method($a);
}


?>
