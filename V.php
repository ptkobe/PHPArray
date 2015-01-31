<?php

if (!defined('PHPARRAY_DOMAIN')) {
	define('PHPARRAY_DOMAIN','phparray');
	$encoding = 'utf8';
	$domain = PHPARRAY_DOMAIN;

	bindtextdomain($domain, dirname(__FILE__).'/locale');
	bind_textdomain_codeset($domain, $encoding);
	#textdomain($domain);
}

require_once 'A.php';

/**
 *
 */
class V implements ArrayAccess, Countable, Iterator
{
	protected $storage;
	
	/*
	 * Default value to fill the array with.
	 */
	protected $default_value = 0;

	/**
	 * Used by ArrayAccess:: (and by Iterator::, indirectlty)
	 */
	protected $keys;

	/**
	 * Used by Iterator::
	 */
	protected $position = 0;

	/**
	 * Implements Iterator::
	 */
	function rewind() {
		$this->position = 0;
	}
	function current() {
		return $this->offsetGet($this->position);
	}
	function key() {
		return $this->position;
	}
	function next() {
		++$this->position;
	}
	function valid() {
		return $this->offsetExists($this->position);
	}

	/**
	 * __construct
	 * 
	 * @param array $a An array.
	 * 
	 * @param callable $checkValue (Optional) Use to restrict the values allowed
	 *                 on the array first level. Defaults to no restriction.
	 * 
	 */
    public function __construct(&$a, $checkValue = NULL)
    {
		$this->storage = new A($a, $checkValue);
		$this->refreshKeys();
    }

	/**
	 * Creates an object from an array of size $m filled with $s
	 * 
	 * @param int $m Size of the array.
	 * 
	 * @param mixed $s (Optional.) The value to filil the array with. 
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
		return count($this->keys());
	}

	/**
	 * Auxiliary function 
	 */
	protected function is_a_key($k) {
		if ( is_int($k) ) {
			return TRUE;
		}
		return FALSE;
	}
 
	/**
	 * Update the index/key relation.
	 * 
	 * @return void
	 */
	public function refreshKeys() 
	{
		#$keys = &$this->keys();
		#$keys = &$this->storage->keys();
		$this->keys = &$this->storage->keys();
	}
	
	/**
	 * 
	 */
	public function &keys() 
	{
		return $this->keys;
	}
	
	/**
	 *
	 */
	protected function getKey($offset) 
	{
		$keys = &$this->keys();
		return $keys[$offset];
	}
	
	/**
	 * Implements ArrayAccess::offsetExists
	 */
	public function offsetExists($offset) {
		if ( !$this->is_a_key($offset) ) {
			return FALSE;
		}
		return array_key_exists($offset, $this->keys());
	}

	/**
	 * Implements ArrayAccess::offsetUnset
	 */
	public function offsetUnset($offset) {
		#echo __METHOD__,"\n";
		#echo ' offset ', $offset, "\n";
		
		if ( !$this->offsetExists($offset) ) {
			$x = $this->keys[$offset];
			return NULL;
		}
		unset($this->storage[$this->getKey($offset)]);
		$this->refreshKeys();
	}

	/**
	 * Implements ArrayAccess::offsetGet
	 */
	public function offsetGet($offset) {
		#echo __METHOD__,"\n";
		#echo ' offset ', $offset, "\n";

		if ( !is_null($offset) && !$this->is_a_key($offset) ) {
			$x = $this->keys[array(1)];
			return NULL;
		}
		$k = $this->getKey($offset);
		return $this->storage[$k];
	}

	/**
	 * Implements ArrayAccess::offsetSet
	 */
	public function offsetSet($offset, $value) {
		#echo __METHOD__,"\n";
		#echo ' offset ', $offset, "\n";
		#echo ' value '; print_r($value);echo "\n";
		
		return $this->offsetSetRef($offset, $value);
	}

	/**
	 * offsetSet() By Ref
	 */
	public function offsetSetRef($offset, &$value) 
	{
		if ( is_null($offset) ) {
			$this->storage->offsetSetRef(NULL, $value);
			$this->refreshKeys();
				#$iterator = $this->storage->getIterator();
				#$iterator->seek($iterator->count()-1);
				#$k = $iterator->key();
				##echo $k, "\n";
				#$keys = &$this->keys();
				#$keys[] = $k;
			
			return $value;
		} elseif ( !$this->is_a_key($offset) ) {
			$x = $this->keys[array(1)];
			return NULL;
		} elseif ( !$this->offsetExists($offset) ) {
			$x = $this->keys[$offset];
			return NULL;
		}
		$this->storage->offsetSetRef($this->getKey($offset), $value);
		return $this->offsetGet($offset);
	}
	
	/**
	 *
	 */
	public function appendRef(&$value) 
	{
		$this->storage->offsetSetRef(NULL, $value);
		$this->refreshKeys();
		return $this->count();
	}

	/**
	 *
	 */
	public function &getArray()
	{
		$r = &$this->storage->getArray();
		return $r;
	}

	/**
	 *
	 */
	public function &getArrayCopy()
	{
		$r = &$this->storage->getArrayCopy();
		return $r;
	}

	/**
	 *
	 */
	public function &getArrayClone()
	{
		$r = &$this->storage->getArrayClone();
		return $r;
	}

	public function &getVector()
	{
		$A = &$this->storage;
		$r = array();
		$A->rewind();
		while ($A->valid()) {
			#$r[] = &$A->current(); // Not by Ref
			$k = $A->key();
			$r[] = &$A[$k];
			$A->next();
		}
		return $r;
	}

	public function &getVectorClone()
	{
		#$a = &$this->getArrayClone();
		#$r = &array_values($a);
		$a = &$this->getArray();
		$r = &array_values($a);
		$r = &$this->array_clone_recursive($r);
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
	 * 
	 */
	public function var_clone(&$a) {
		return $a;
	}

	/**
	 * Test function 
	 */
	public function testFor()
	{
		$r = 0;
		for ($i = 0; $i < count($this); $i++) {
			$r += $this[$i];
		}
		return $r;
	}

	/**
	 * Test function 
	 */
	public function testForeach() {
		$i = 0;
		foreach ($this as $k=>$v) {
			#echo $k, "\n";
			$i++;
		}
		return $i;
	}
 
}
