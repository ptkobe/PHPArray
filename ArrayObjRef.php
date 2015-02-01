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
 * ArrayObject other methods don't work here. 
 */
class ArrayObjRef extends ArrayObject
{
	protected $storage;

	/**
	 *
	 */
	public function check_value($v) {
		#return (is_numeric($v) && !is_string($v));
		return TRUE;
	}

	/**
	 *
	 */
	protected function is_a_key($k) {
		if ( !is_int($k) && !is_string($k) ) {
			return FALSE;
		}
		return TRUE;
	}
 
	/**
	 *
	 */
    public function __construct(&$a)
    {
		if ( (gettype($a) != 'array') ) {
			throw new DomainException(dgettext(PHPARRAY_DOMAIN, 'invalid argument'));
		}
		$this->storage = &$a;
		
		foreach ($a as $k=>&$v) {
			if ( !$this->check_value($v) ) {
				throw new DomainException(dgettext(PHPARRAY_DOMAIN, 'Illegal array value'));
			}
			#$this->offsetSetRef($k, $v);
		} unset($v);
    }

	/**
	 *
	 */
	public static function &create($m, $s = NULL) 
	{
		if ( is_null($s) ) {
			$s = 0;
		}
		$a = array_fill(0, $m, $s);
		$X = new static($a); 
		return $X;
	}

	/**
	 *
	 */
	public function count() 
	{
		return count($this->storage);
	}
	
	/**
	 *
	 */
	public function &getArray() 
	{
		#$r = $this->storage;
		return $this->storage;
	}

	/**
	 * offsetSet() By Ref
	 */
	public function offsetSetRef($offset, &$value) 
	{
		if ( !$this->check_value($value) ) {
			throw new DomainException(dgettext(PHPARRAY_DOMAIN, 'Illegal array value'));
		}
		
		// Allow push key?
		#if ( !$this->offsetExists($offset) ) {
		#	throw new DomainException(dgettext(PHPARRAY_DOMAIN, 'Invalid key'));
		#}
		
		if ( is_null($offset) ) {
			$this->storage[] = &$value;
				#end($this->a);
				#$offset = key($this->a);
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
	public function offsetGet($offset) {
		#echo __METHOD__,"\n";
		#echo ' offset ', $offset, "\n";
		
		return $this->storage[$offset];
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

}

class ArrayRef_R extends ArrayRef
{
	public function check_value ($v) {
		return (is_numeric($v) && !is_string($v));
	}
}
