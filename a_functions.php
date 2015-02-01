<?php
/**
* @package PHPArray
*/

function a_check_value ($v) {
	#echo $v."\n";
	return ( (is_numeric($v) && !is_string($v)) || is_complex($v) );
}

function &a_transpose ($a) {
	reset($a);
	$kc = array_keys(current($a));
	$kr = array_keys($a);
	$x = array();
	foreach ($kc as $key) {
		$x[$key] = array_column($a, $key, $kr);
	}
	return $x;
}

function &a_identity ($m, $n = NULL) {
	if ( is_null($n) ) {
		$n = $m;
	}
	if ( !is_int($m) || !is_int($n) ) {
		throw new InvalidArgumentException(dgettext(PHPJAMA_DOMAIN, 'dimensions must be positive integers'));
	}
		
	$x = array_fill(0, $m, array_fill(0, $n, 0));
	$d = min($m,$n);
	for($i = 0; $i < $d; $i++) {
		$x[$i][$i] = 1;
	}
	return $x;
}
   
function &a_random ($m, $n, $min = NULL, $max = NULL) {
	if ( !is_int($m) || !is_int($n) || ($m <= 0) || ($n <= 0) ) {
		throw new InvalidArgumentException(dgettext(PHPJAMA_DOMAIN, 'dimensions must be positive integers'));
	}
	if ( is_null($min) ) {
		$min = 0;
	}
	if ( is_null($max) ) {
		$max = 1;
	}
	if ( !is_numeric($min) || !is_numeric($max) || ($max <= $min) ) {
		throw new InvalidArgumentException(dgettext(PHPJAMA_DOMAIN, 'invalid random limits'));
	}

	$x = array_fill(0, $m, array_fill(0, $n, 0));
	for($i = 0; $i < $m; $i++) {
		for($j = 0; $j < $n; $j++) {
			$x[$i][$j] = $min + mt_rand() / mt_getrandmax() * ($max - $min);
		}
	}
	return $x;
}

function &a_plus (&$a, &$b, $equals = NULL) {
	if ( !(gettype($a) == 'array') ) {
		throw new InvalidArgumentException(dgettext(PHPJAMA_DOMAIN, 'THE first').' '._('argument must be an array'));
	}
	if ( !(gettype($b) == 'array') ) {
		throw new InvalidArgumentException(dgettext(PHPJAMA_DOMAIN, 'second').' '._('argument must be an array'));
	}
	if ( !empty($equals) ) {
		$c = &$a;
	} else {
		$c = $a;
	}
	// Check dims ?

	reset($b);
	$rb = current($b);
	foreach ($c as &$r) {
		$vb = current($rb);
		foreach ($r as &$v) {
			if ( !a_check_value(c_add($v,$vb)) ) {
				throw new DomainException(dgettext(PHPJAMA_DOMAIN, 'invalid result'));
			}
			$v = c_add($v,$vb);
			$vb = next($rb);
		} unset($v);
		$rb = next($b);
	} unset($r);
	return $c;
}

function &a_minus (&$a, &$b, $equals = NULL) {
	if ( !(gettype($a) == 'array') ) {
		throw new InvalidArgumentException(dgettext(PHPJAMA_DOMAIN, 'THE first').' '._('argument must be an array'));
	}
	if ( !(gettype($b) == 'array') ) {
		throw new InvalidArgumentException(dgettext(PHPJAMA_DOMAIN, 'second').' '._('argument must be an array'));
	}
	if ( !empty($equals) ) {
		$c = &$a;
	} else {
		$c = $a;
	}
	// Check dims ?
	
	reset($b);
	$rb = current($b);
	foreach ($c as &$r) {
		$vb = current($rb);
		foreach ($r as &$v) {
			if ( !a_check_value(c_sub($v,$vb)) ) {
				throw new DomainException(dgettext(PHPJAMA_DOMAIN, 'invalid result'));
			}
			$v = c_sub($v,$vb);
			$vb = next($rb);
		} unset($v);
		$rb = next($b);
	} unset($r);
	return $c;
}

function &a_uminus(&$a) {
	$v = -1;
	$c = &a_times($a, $v);
	return $c;
}

function &a_times (&$a, &$b, $equals = NULL) {
	if ( !(gettype($a) == 'array') ) {
		throw new InvalidArgumentException(dgettext(PHPJAMA_DOMAIN, 'THE first').' '._('argument must be an array'));
	}
	if ( !empty($equals) ) {
		$c = &$a;
	} else {
		$c = $a;
	}
	if ( a_check_value($b) ) {
		foreach ($c as &$r) {
			foreach ($r as &$v) {
				if ( !a_check_value(c_mult($v,$b)) ) {
					throw new DomainException(dgettext(PHPJAMA_DOMAIN, 'invalid result'));
				}
				$v = c_mult($v,$b);
			} unset($v);
		} unset($r);
		return $c;
	}
	
	if ( !(gettype($b) == 'array') ) {
		throw new InvalidArgumentException(dgettext(PHPJAMA_DOMAIN, 'second').' '._('argument must be a numeric or an array'));
	}
	// Check dims ?
	
	reset($b);
	$lb = current($b);
	$k = array_keys(current($b));
	foreach ($c as &$r) {
		$l = array();
		foreach ($k as $kb) {
			$cb = array_column($b, $kb);
			$v = array_sum(a_arrayTimes($r, $cb));
			if ( !a_check_value($v) ) {
				throw new DomainException(dgettext(PHPJAMA_DOMAIN, 'invalid result'));
			}
			$l[] = $v;
		}
		$r = $l;
	} unset($r);
	return $c;
}

function &a_arrayTimes (&$a, &$b, $equals = NULL) {
	if ( !(gettype($a) == 'array') ) {
		throw new InvalidArgumentException(dgettext(PHPJAMA_DOMAIN, 'THE first').' '._('argument must be an array'));
	}
	if ( !(gettype($b) == 'array') ) {
		throw new InvalidArgumentException(dgettext(PHPJAMA_DOMAIN, 'second').' '._('argument must be a numeric or an array'));
	}
	if ( !empty($equals) ) {
		$c = &$a;
	} else {
		$c = $a;
	}
	// Check dims ?
	
	reset($b);
	$lb = current($b);
	if ( (gettype($lb) == 'array') ) {
		foreach ($c as &$r) {
			$l = array_map(function ($v1,$v2) {return c_mult($v1,$v2);}, $r, $lb);
			foreach ($l as $v) {
				if ( !a_check_value($v) ) {
					throw new DomainException(dgettext(PHPJAMA_DOMAIN, 'invalid result'));
				}
			}
			$r = $l;
			$lb = next($b);
		} unset($r);
	} else {
		foreach ($c as &$v) {
			if ( !a_check_value(c_mult($v,$lb)) ) {
				throw new DomainException(dgettext(PHPJAMA_DOMAIN, 'invalid result'));
			}
			$v = c_mult($v,$lb);
			$lb = next($b);
		} unset($v);
	}
	return $c;
}

function &a_arrayLeftDivide (&$a, &$b, $equals = NULL) {
	if ( !(gettype($a) == 'array') ) {
		throw new InvalidArgumentException(dgettext(PHPJAMA_DOMAIN, 'THE first').' '._('argument must be an array'));
	}
	if ( !(gettype($b) == 'array') ) {
		throw new InvalidArgumentException(dgettext(PHPJAMA_DOMAIN, 'second').' '._('argument must be an array'));
	}
	if ( !empty($equals) ) {
		$c = &$a;
	} else {
		$c = $a;
	}
	// Check dims ?
	
	reset($b);
	$lb = current($b);
	if ( (gettype($lb) == 'array') ) {
		foreach ($c as &$r) {
			$l = array_map(function ($v1,$v2) {return c_div($v1,$v2);}, $r, $lb);
			foreach ($l as $v) {
				if ( !a_check_value($v) ) {
					throw new DomainException(dgettext(PHPJAMA_DOMAIN, 'invalid result'));
				}
			}
			$r = $l;
			$lb = next($b);
		} unset($r);
	} else {
		// vector
		foreach ($c as &$v) {
			if ( !a_check_value(c_div($lb,$v)) ) {
				throw new DomainException(dgettext(PHPJAMA_DOMAIN, 'invalid result'));
			}
			$v = c_div($lb,$v);
			$lb = next($b);
		} unset($v);
	}
	return $c;
}

	/**
	* Matrix inverse or pseudoinverse.
	* @return Matrix ... Inverse(A) if A is square, pseudoinverse otherwise.
	*/
	function &a_inverse ($a) {
		if (class_exists('Lapack')) {
			$x = Lapack::pseudoInverse($a);
			return $x;
		}
		
		$m = count($a);
		$n = count(current($a));
		$ident = &a_identity($m, $n);
		$x = &a_solve($a, $ident);
		return $x;
	}
  
	/**
	* Solve A*X = B.
	* @param Matrix $B Right hand side
	* @return Matrix ... Solution if A is square, least squares solution otherwise
	*/
	function &a_solve (&$a, &$b) {  
		if (class_exists('Lapack')) {
			$x = Lapack::solveLinearEquation($a, $b);  
			return $x;
		}
		
		$A = new Matrix($a);
		if ( ($A->getRowDimension() == $A->getColumnDimension()) ) {
			$LU = new LUDecomposition($A);
			$X = $LU->solve($B);
		} else {
			$QR = new QRDecomposition($A);
			$X = $QR->solve($B);
		}
		$x = &getArray($X);
		return $x;
	}

	/**
	* eig
	* Eigenvalue decomposition
	* @return Matrix Eigenvalue decomposition
	*/
	function &a_eig (&$a, &$left = NULL, &$right = NULL) {
		
		if (class_exists('Lapack')) {
			// $lv and $rv can't be passed by ref. They must suffer some pointer op on the Lapack::function.
			if ( is_array($left) ) {
				$lv = array();
			}
			if ( is_array($right) ) {
				$rv = array();
			}
			$x = Lapack::eigenValues($a, $lv, $rv);
			#echo 'eig() left '."\n".print_r($lv, TRUE)."\n";
			#echo 'eig() right '."\n".print_r($rv, TRUE)."\n";
			
			array_walk($x, function(&$v){$v = atoc($v);});
			if ( is_array($lv) ) {
				$left = $lv;
				foreach ($left as &$u) {
					array_walk($u, function(&$v){$v = atoc($v);});
				} unset($u);
			}
			if ( is_array($rv) ) {
				$right = $rv;
				foreach ($right as &$u) {
					array_walk($u, function(&$v){$v = atoc($v);});
				} unset($u);
			}
			return $x;
		}
		
		$A = new Matrix($a);
		$X = new EigenvalueDecomposition($A);
		$x = $X->getRealEigenvalues();
		$y = $X->getImagEigenvalues();
		foreach ($x as $k=>&$u) {
			$u = new Complex($u, $y[$k]);
			$u = $u->flat();
		} unset($u);
		//
		return $x;
	}

	/**
	* svd
	* Singular value decomposition
	* @return Singular value decomposition
	*/
	function a_svd (&$a) {
		if (class_exists('Lapack')) {
			$x = Lapack::singularValues($a);
			return $x;
		}
		
		$A = new Matrix($a);
		$X = new SingularValueDecomposition($A);
		//
		return $X;
	}


?>
