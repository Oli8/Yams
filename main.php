<?php

class Yams {

	public $args;
	public $combinations = ['brelan', 'suite', 'full', 'carre', 'yams'];
	public $combination;
	public $combinationParams;
	public $dice = [];
	public $perms = [];

	public function __construct ($args) {
		$this->args = $args;
		$this->checkArgs();
		//$this->permutations(5);
		$this->getProba();
	}

	public function checkArgs () {
		if(count($this->args) !== 6)
			exit(-1);

		//check dice
		if(count($this->dice = array_filter(array_slice($this->args, 0, 5), function($v){
			return in_array($v, range(1, 6));
		})) !== 5)
			exit(-1);

		if(!in_array($this->combination = explode('_', end($this->args))[0], $this->combinations))
			exit(-1);
		$this->combinationParams = array_slice(explode('_', end($this->args)), 1);
	}

	public function permutations ($n) {
		//ugly af & dumb but no time
		$perms = [];
		for($i=1; $i<7; $i++){
			if($n === 1){
				$perms[] = [$i];
			} else {
				for($j=1; $j<7; $j++){
					if($n === 2){
						$perms[] = [$i, $j];
					} else {
						for($k=1; $k<7; $k++){
							if($n === 3){
								$perms[] = [$i, $j, $k];
							} else {
								for($l=1; $l<7; $l++){
									if($n === 4){
										$perms[] = [$i, $j, $k, $l];
									} else {
										for($m=1; $m<7; $m++){
											if($n === 5){
												$perms[] = [$i, $j, $k, $l, $m];
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		$this->perms = $perms;
	}

	public function getProba () {
		if(in_array($this->combination, ['brelan', 'carre', 'yams'])){
			$locked = @array_count_values($this->dice)[$this->combinationParams[0]] ?: 0;
			$needed =  ['brelan' => 3, 'carre' => 4, 'yams' => 5][$this->combination] - $locked;
			
			if($needed <= 0)
				return $this->displayResult(100);

			$rolls = 5 - $locked;
			//echo "$locked $needed $rolls\n";
			$this->permutations($rolls);

			$this->displayResult(count(array_filter($this->perms, function($v) use ($needed){
				return @array_count_values($v)[$this->combinationParams[0]] >= $needed;
			})) / pow(6, $rolls) * 100);
		}

		else if($this->combination === 'full'){
			$lockedX = @array_count_values($this->dice)[$this->combinationParams[0]] ?: 0;
			$lockedY = @array_count_values($this->dice)[$this->combinationParams[1]] ?: 0;
			$locked = $lockedX + $lockedY;
			echo "full\n";
			echo "$lockedX $lockedY $locked\n";
			if(($lockedX === 3 && $lockedY === 2) || ($lockedX === 2 && $lockedY === 3))
				return $this->displayResult(100);

			$rolls = 5 - $locked;
			$this->permutations($rolls);

			$this->displayResult(count(array_filter($this->perms, function($v) use ($lockedX, $lockedY){
				return (@array_count_values($v)[$this->combinationParams[0]] + $lockedX === 3  && @array_count_values($v)[$this->combinationParams[1]] + $lockedY === 2) || (@array_count_values($v)[$this->combinationParams[0]] + $lockedX === 2  && @array_count_values($v)[$this->combinationParams[1]] + $lockedY === 3);
			})) / pow(6, $rolls) * 100);

		}

		else if($this->combination === 'suite'){
			// choose which suite to do 1,2,3,4,5 or 2,3,4,5,6
			
		}

	}

	public function displayResult ($value) {	
		echo number_format($value, 2) . '%';
	}

	public static function displayHelp () {
		echo join("\n", ['USAGE', 'php my_yams.php d1 d2 d3 d4 d5 c', 'DESCRIPTION', 'd1 la valeur de premier dé', 'd2 la valeur de deuxième dé',
		'd3 la valeur de troisième dé', 'd4 la valeur de quatrième dé', 'd5 la valeur de cinquième dé', 'd6 la valeur de sixième dé', 'c la combinaison souhaitée']);
	}

}

$options = getopt('h', ['help']);

if(array_key_exists('h', $options) || array_key_exists('help', $options))
	return Yams::displayHelp();

new Yams(array_slice($argv, 1));

echo PHP_EOL;


/*
Brelan
Suite
Full
Carré
Yams
*/

?>