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
		$this->getProba();
	}

	public function checkArgs () {
		if(count($this->args) !== 6)
			die("Nombre d'arguments incorrectes, entrez les 5 dés suivui de la figure souhaitée, tapez --help pour voir l'aide.\n");

		if(count($this->dice = array_filter(array_slice($this->args, 0, 5), function($v){
			return in_array($v, range(1, 6));
		})) !== 5)
			die("Veuillez rentrer les 5 dés compris entre 1 et 6, tapez --help pour voir l'aide.\n");

		$combination = explode('_', end($this->args));
		if(!in_array($this->combination = $combination[0], $this->combinations))
			die("Combinaison incorrecte, tapez --help pour voir l'aide.\n");
		$this->combinationParams = array_slice($combination, 1);
	}

	public function permutations ($n) {
		for($i=1; $i<7; $i++)
			if($n === 1)
				$this->perms[] = [$i];
			else
				for($j=1; $j<7; $j++)
					if($n === 2)
						$this->perms[] = [$i, $j];
					else
						for($k=1; $k<7; $k++)
							if($n === 3)
								$this->perms[] = [$i, $j, $k];
							else
								for($l=1; $l<7; $l++)
									if($n === 4)
										$this->perms[] = [$i, $j, $k, $l];
									else
										for($m=1; $m<7; $m++)
											if($n === 5)
												$this->perms[] = [$i, $j, $k, $l, $m];
	}

	public function getProba () {
		if(in_array($this->combination, ['brelan', 'carre', 'yams'])){
			if(count($this->combinationParams) !== 1 || empty($this->combinationParams[0]) || !in_array($this->combinationParams[0], range(1, 6)))
				die("Vous devez renseigner le $this->combination souhaité, exemple: {$this->combination}_3.\n");

			$locked = @array_count_values($this->dice)[$this->combinationParams[0]] ?: 0;
			$needed =  ['brelan' => 3, 'carre' => 4, 'yams' => 5][$this->combination] - $locked;
			
			if($needed <= 0)
				return $this->displayResult(100);

			$rolls = 5 - $locked;
			$this->permutations($rolls);

			$filterFunc = function($v) use ($needed){
				return @array_count_values($v)[$this->combinationParams[0]] >= $needed;
			};
		}

		else if($this->combination === 'full'){
			if(count($this->combinationParams) !== 2 || empty($this->combinationParams[0]) || empty($this->combinationParams[1]) || !in_array($this->combinationParams[0], range(1, 6)) || !in_array($this->combinationParams[1], range(1, 6)) || $this->combinationParams[0] === $this->combinationParams[1])
				die("Vous devez renseigner le full souhaité, exemple: full_2_3.\n");

			$lockedX = @array_count_values($this->dice)[$this->combinationParams[0]] ?: 0;
			$lockedY = @array_count_values($this->dice)[$this->combinationParams[1]] ?: 0;
			$locked = $lockedX + $lockedY;

			if(($lockedX === 3 && $lockedY === 2) || ($lockedX === 2 && $lockedY === 3))
				return $this->displayResult(100);

			$rolls = 5 - $locked;
			$this->permutations($rolls);

			$filterFunc = function($v) use ($lockedX, $lockedY){
				return (@array_count_values($v)[$this->combinationParams[0]] + $lockedX === 3  && @array_count_values($v)[$this->combinationParams[1]] + $lockedY === 2) || (@array_count_values($v)[$this->combinationParams[0]] + $lockedX === 2  && @array_count_values($v)[$this->combinationParams[1]] + $lockedY === 3);
			};
		}

		else if($this->combination === 'suite'){
			if(count($this->combinationParams) !== 1 || empty($this->combinationParams[0]) || !in_array($this->combinationParams[0], [5, 6]))
				die("Vous devez renseigner la suite souhaité (suite_5 ou suite_6).\n");

			$toMatch = range($this->combinationParams[0] - 4, $this->combinationParams[0]);
			$lockedDice = array_unique(array_intersect($this->dice, $toMatch));
			$locked = count($lockedDice);

			if($locked === 5)
				return $this->displayResult(100);

			$rolls = 5 - $locked;
			$this->permutations($rolls);

			$filterFunc = function($v) use ($lockedDice, $toMatch){
				$a = array_merge($lockedDice, $v);
				sort($a);
				return $a == $toMatch;
			};
		}

		$this->displayResult($this->filterPermutations($filterFunc, $rolls));
	}

	public function filterPermutations (Closure $filterFunc, $rolls) {
		return count(array_filter($this->perms, $filterFunc)) / pow(6, $rolls) * 100;
	}

	public function displayResult ($value) {	
		echo number_format($value, 2) . "%\n";
	}

	public static function displayHelp () {
		echo join("\n", ['USAGE', 'php my_yams.php d1 d2 d3 d4 d5 c', '', 'DESCRIPTION', 'd1 la valeur de premier dé', 'd2 la valeur de deuxième dé',
		'd3 la valeur de troisième dé', 'd4 la valeur de quatrième dé', 'd5 la valeur de cinquième dé', 'd6 la valeur de sixième dé', 'c la combinaison souhaitée', '', 'combinaison: brelan, carre, yams, full, suite', 'exemple:', 'php my_yams.php 1 2 3 2 1 brelan_2', 'php my_yams.php 4 5 4 3 1 full_4_3', 'php my_yams.php 2 3 3 5 4 suite_6']) . "\n";
	}

}

$options = getopt('h', ['help']);
if(array_key_exists('h', $options) || array_key_exists('help', $options))
	return Yams::displayHelp();

new Yams(array_slice($argv, 1));

?>
