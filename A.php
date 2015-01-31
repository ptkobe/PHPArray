<?php

if (!defined('PHPARRAY_DOMAIN')) {
	define('PHPARRAY_DOMAIN','phparray');
	$encoding = 'utf8';
	$domain = PHPARRAY_DOMAIN;

	bindtextdomain($domain, dirname(__FILE__).'/locale');
	bind_textdomain_codeset($domain, $encoding);
	#textdomain($domain);
}

/**
 *
 */
class A implements ArrayAccess, Countable, Iterator
#class A implements ArrayAccess, Countable, IteratorAggregate
{
	protected $storage;
	
	protected $iterator = 'ArrayIterator';
	
	/**
	 * Restricts the values allowed on the array first level. 
	 * See __construct().
	 * 
	 * @param mixed $v A value.
	 * 
	 * @return boolean
	 */
	protected $checkValue = NULL;

	/*
	 * Default value to fill the array with.
	 */
	protected $default_value = 0;

	/**
	 * Implements IteratorAggregate::getIterator()
	 */
	public function getIterator() {
		return new $this->iterator($this->storage);
	}

	/**
	 * Implements Iterator::
	 */
	public function rewind() {
		return reset($this->storage);
	}
	public function current() {
		return current($this->storage);
	}
	public function key() {
		return key($this->storage);
	}
	public function next() {
		return next($this->storage);
	}
	public function valid() {
		return (key($this->storage) !== NULL);
	}

	/**
	 * Auxiliary function 
	 */
	protected function is_a_key($k) {
		if ( !is_int($k) && !is_string($k) ) {
			return FALSE;
		}
		return TRUE;
	}
 
	/**
	 * __construct
	 * 
	 * @param array $a
	 * 
	 * @param callable $checkValue (Optional) Use to restrict the values allowed
	 *                 on the array first level. Defaults to no restriction.
	 * 
	 */
    public function __construct(&$a, $checkValue = NULL)
    {
		if ( (gettype($a) != 'array') ) {
			throw new DomainException(dgettext(PHPARRAY_DOMAIN, 'invalid argument'));
		}
		
		if ( is_null($checkValue) ) {
			$checkValue = function ($v) {return TRUE;};
		}
		
		$this->checkValue = $checkValue;
		$this->storage = &$a;
		foreach ($a as $k=>&$v) {
			if ( !$checkValue($v) ) {
				throw new DomainException(dgettext(PHPARRAY_DOMAIN, 'Illegal array value'));
			}
			$this->offsetSetRef($k, $v);
		} unset($v);

    }

	/**
	 * Creates an object from an array of size $m filled with $s
	 * 
	 * @param int $m Size of the array.
	 * 
	 * @param mixed $s (Optional.) The default value to fill the array with. 
	 *                 Defaults to $default_value.
	 */
	public static function &create($m, $s = NULL, $checkValue = NULL) 
	{
		if ( is_null($s) ) {
			$s = $this->default_value;
		}
		$a = array_fill(0, $m, $s);
		$X = new static($a, $checkValue); 
		return $X;
	}

	/**
	 * Countable::count implementation.
	 * For inner dimensions use count($Obj[$key]).
	 * 
	 * @return int
	 */
	public function count() 
	{
		return count($this->storage);
	}
	
	/**
	 *
	 */
	public function &keys() 
	{
		$keys = array_keys($this->storage);
		return $keys;
	}

	/**
	 * Implements ArrayAccess::offsetExists
	 */
	public function offsetExists($offset) {
		if ( !$this->is_a_key($offset) ) {
			return FALSE;
		}
		return array_key_exists($offset, $this->storage);
	}

	/**
	 * Implements ArrayAccess::offsetUnset
	 */
	public function offsetUnset($offset) {
		#echo __METHOD__,"\n";
		#echo ' offset ', $offset, "\n";
		
		if ( !$this->offsetExists($offset) ) {
			return $this->storage[$offset];
		}
		unset($this->storage[$offset]);
	}

	/**
	 * Implements ArrayAccess::offsetGet
	 */
	public function &offsetGet($offset) {
		#echo __METHOD__,"\n";
		#echo ' offset ', $offset, "\n";
		
		if ( is_null($offset) || !$this->offsetExists($offset) ) {
			// So that PHP Errors are issued.
			$x = $this->storage[$offset];
			return NULL;
		}
		return $this->storage[$offset];
	}

	/**
	 * Implements ArrayAccess::offsetSet
	 */
	public function offsetSet($offset, $value) {
		#echo __METHOD__,"\n";
		#echo ' offset ', $offset, "\n";
		#echo ' value '; var_dump($value); echo "\n";
		
		return $this->offsetSetRef($offset, $value);
	}

	/**
	 * offsetSet() By Ref
	 */
	public function offsetSetRef($offset, &$value) 
	{
		$checkValue = $this->checkValue;
		if ( !$checkValue($value) ) {
		#if ( !$this->checkValue($value) ) {
		#if ( !static::checkValue($value) ) {
		#if ( !$this->check_value($value) ) {
			throw new DomainException(dgettext(PHPARRAY_DOMAIN, 'Illegal array value'));
		}
		
		// Allow push key?
		#if ( !$this->offsetExists($offset) ) {
		#	throw new DomainException(dgettext(PHPARRAY_DOMAIN, 'Invalid key'));
		#}
		
		if ( is_null($offset) ) {
			$this->storage[] = &$value;
				#end($this->storage);
				#$offset = key($this->storage);
			return $value;
		}
		$this->storage[$offset] = &$value;
		if ( !$this->offsetExists($offset) ) {
			return NULL;
		}
		return $this->offsetGet($offset);
	}

	/**
	 *
	 */
	public function appendRef(&$value) 
	{
		$this->offsetSetRef(NULL, $value);
		return $this->count();
	}

	/**
	 * Returns the array.
	 * Use $a = $A->getArray() for an array copy with references
	 * Use $a = &$A->getArray() for a reference to the array
	 * 
	 * @return array
	 */
	public function &getArray() 
	{
		return $this->storage;
	}

	/**
	 * Returns the array.
	 * alias of $A->getArray().
	 * 
	 * @return array
	 */
	public function &getArrayCopy()
	{
		$r = $this->getArray();
		return $r;
	}

	/**
	 * Returns a copy of the array without references.
	 * 
	 * @return array
	 */
	public function &getArrayClone()
	{
		$a = &$this->getArray();
		$r = &$this->array_clone_recursive($a);
		return $r;
	}

	/**
	 *
	 */
	public function &array_clone_recursive(&$a) {
		$r = &array_map(
			function(&$element) {
				if ( is_array($element) ) {
					return $this->array_clone_recursive($element);
				} elseif ( is_object($element) ) {
					return clone($element);
				}
				return $element;
			},
			$a
		);
		return $r;
	}

	/**
	 * If $a is a link to another var, it is replaced by a copy.
	 */
	public function var_clone(&$a) {
		return $a;
	}

	/**
	 * Test function for iterator.
	 */
	public function testForeach() {
		$i = 0;
		foreach ($this as $k=>$v) {
			#echo $k, "\n";
			$i++;
		}
		return $i;
	}

	public function testWhile()
	{
		$r = 0;
		$this->rewind();
		while ($this->valid()) {
			#echo $this->key(), "\n";
			#echo $this->current(), "\n";
			$r++;
			$this->next();
		}
		return $r;
	}

}
