<?php
function factorize($num) {
  // Returns a sorted array of the prime factorization of $num
  // Caches prior results.  Returns empty array for |$num|<2
  // eg. factorize(360) => [5, 3, 3, 2, 2, 2]
  static $aFactors = array();
  if (2>$num=abs($num)) return array();  // negatives, 1, 0

  if ($aFactors[$key = "x$num"]) {     // handles doubles
    // Been there, done that
    if (($factor=$aFactors[$key])==$num) return array($num);
    return array_merge(factorize($num/$factor),array($factor)); }
 
  // Find a smallest factor
  for ($sqrt = sqrt($num),$factor=2;$factor<=$sqrt;++$factor)
    if (floor($num/$factor)==$num/$factor)
      return array_merge(factorize($num/$factor),
                         array($aFactors[$key] = $factor));

  return (array($aFactors[$key] = $num));  }  // $num is prime

function primeFactors($num) {
  // Returns an array of each distinct prime factor of $num
  // eg. primeFactors(360) => [5, 3, 2]
  return array_keys(array_flip(factorize($num)));  }

function allFactors($num) {
  // Returns an (unsorted) array of each factor of $num
  // eg. allFactors(30) => [1, 5, 3, 15, 2, 10, 6, 30]
  // eg. allFactors(64) => [1, 2, 4, 8, 16, 32, 64]
  $aFacts = array(1 => 1);
  foreach (factorize($num) as $factor)
    foreach ($aFacts as $fact => $whatever)
      $aFacts[$fact * $factor] = 1;
  return array_keys($aFacts);  }
?>
