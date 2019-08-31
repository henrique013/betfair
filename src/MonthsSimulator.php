<?php

namespace Betfair;


use RuntimeException;

class MonthsSimulator
{
	public const STAKE_MIN = 10;

	public const GREEN = 'green';
	public const RED = 'red';
	public const YELLOW = 'yellow';

	public const HANDICAP_1_OVER = 1;
	public const HANDICAP_0_75_OVER = 0.75;
	public const HANDICAP_0_5_OVER = 0.5;
	public const HANDICAP_0_25_OVER = 0.25;
	public const HANDICAP_0 = 0;
	public const HANDICAP_0_25_UNDER = -0.25;
	public const HANDICAP_0_5_UNDER = -0.5;
	public const HANDICAP_0_75_UNDER = -0.75;
	public const HANDICAP_1_UNDER = -1;

	public const HANDICAPS = [
		self::HANDICAP_1_OVER,
		self::HANDICAP_0_75_OVER,
		self::HANDICAP_0_5_OVER,
		self::HANDICAP_0_25_OVER,
		self::HANDICAP_0,
		self::HANDICAP_0_25_UNDER,
		self::HANDICAP_0_5_UNDER,
		self::HANDICAP_0_75_UNDER,
		self::HANDICAP_1_UNDER,
	];

	public const HANDICAP_ENDING_75_OVER = 75;   // 0.75, 1.75, ...
	public const HANDICAP_ENDING_25_OVER = 25;   // 0.25, 1.25, ...
	public const HANDICAP_ENDING_5_OVER = 5;     // 0.5, 1.5, ...
	public const HANDICAP_ENDING_0 = 0;          // -1, 0, 1, ...
	public const HANDICAP_ENDING_5_UNDER = -5;   // -1.5, -0.5, ...
	public const HANDICAP_ENDING_25_UNDER = -25; // -1.25 -0.25, ...
	public const HANDICAP_ENDING_75_UNDER = -75; // -1.75 -0.75, ...


	private $walletStart = 1000;
	private $walletEnd = 0;
	private $stakes = 50;
	private $odds = 2.00;
	private $betsMonth = 20;
	private $greensPercentage = 50;
	private $redsPercentage = 25;
	private $yellowsPercentage = 25;
	private $maxRedsSequence = 0;
	private $months = 11;
	private $commission = 0.065; // 6.5%
	private $handicapEnding = self::HANDICAP_ENDING_0;
	private $results = [];
	private $resultIndex = 0;


	public function __construct()
	{
		$this->buildResults();
	}


	public function getWalletEnd(): int
	{
		return $this->walletEnd;
	}


	public function getMaxRedsSequence(): int
	{
		return $this->maxRedsSequence;
	}


	public function setWalletStart(int $walletStart): MonthsSimulator
	{
		if ($walletStart < self::STAKE_MIN)
		{
			throw new RuntimeException("walletStart can't be less than " . self::STAKE_MIN);
		}


		$this->walletStart = $walletStart;


		return $this;
	}


	public function setStakes(int $stakes): MonthsSimulator
	{
		if ($stakes < 1)
		{
			throw new RuntimeException("stakePercentage can't be less than 1");
		}


		$this->stakes = $stakes;


		return $this;
	}


	public function setOdds(float $odds): MonthsSimulator
	{
		if ($odds <= 1)
		{
			throw new RuntimeException("odds can be greater than 1");
		}
		if ($odds > 1000)
		{
			throw new RuntimeException("odds can't be greater than 1000");
		}


		$this->odds = $odds;


		return $this;
	}


	public function setBetsMonth(int $betsMonth): MonthsSimulator
	{
		if ($betsMonth < 1)
		{
			throw new RuntimeException("betsMonth can't be less than 1");
		}


		$this->betsMonth = $betsMonth;


		return $this;
	}


	public function setMonths(int $months): MonthsSimulator
	{
		if ($months < 1)
		{
			throw new RuntimeException("months can't be less than 1");
		}


		$this->months = $months;


		return $this;
	}


	public function setCommission(float $commission): MonthsSimulator
	{
		if ($commission < 0)
		{
			throw new RuntimeException("commission can't be less than 0");
		}
		if ($commission >= 100)
		{
			throw new RuntimeException("commission can't be greater or equals than 100");
		}


		$this->commission = $commission / 100;


		return $this;
	}


	public function setHandicap(float $handicap, float $greensPercentage, float $yellowsPercentage = null): MonthsSimulator
	{
		$greensPercentage = (int)floor($greensPercentage);

		$yellowsPercentage = (int)floor(($yellowsPercentage ?: 0));

		$noReds = $greensPercentage + $yellowsPercentage;

		$handicapEnding = explode('.', (string)abs($handicap));
		$handicapEnding = intval(count($handicapEnding) === 2 ? $handicapEnding[1] : $handicapEnding[0]);


		if (!in_array($handicap, self::HANDICAPS))
		{
			throw new RuntimeException("handicap not allowed");
		}
		if ($greensPercentage > 100)
		{
			throw new RuntimeException("greenPercentage can't be greater than 100");
		}
		if ($greensPercentage < 0)
		{
			throw new RuntimeException("greenPercentage can't be less than 0");
		}
		if ($noReds >= 100)
		{
			throw new RuntimeException("greenPercentage + yellowsPercentage can't be greater or equals than 100");
		}
		if (($handicapEnding !== 5) && ($yellowsPercentage <= 0))
		{
			throw new RuntimeException("yellowsPercentage can't be less or equals than 0");
		}


		if ($handicap < 0)
		{
			$handicapEnding *= -1;
		}


		$this->handicapEnding = $handicapEnding;
		$this->greensPercentage = $greensPercentage;
		$this->yellowsPercentage = $yellowsPercentage;
		$this->redsPercentage = 100 - $noReds;


		$this->buildResults();


		return $this;
	}


	public function simulate(): MonthsSimulator
	{
		$redsSequence = 0;


		$this->walletEnd = $this->walletStart;
		$this->maxRedsSequence = 0;


		for ($month = 1; $month <= $this->months; $month++)
		{
			$stake = $this->calcStake();


			for ($bet = 1; $bet <= $this->betsMonth; $bet++)
			{
				$this->walletEnd -= $stake;


				$result = $this->getResult();


				if (in_array($result, [self::GREEN, self::YELLOW]))
				{
					$redsSequence = 0;


					if ($result === self::GREEN)
					{
						$gain = $this->calcGreenGain($stake);
					}
					else
					{
						$gain = $this->calcYellowGain($stake);
					}


					$this->walletEnd += $gain;
				}
				else
				{
					$redsSequence++;


					if ($redsSequence > $this->maxRedsSequence)
					{
						$this->maxRedsSequence = $redsSequence;
					}
				}
			}
		}


		return $this;
	}


	private function calcStake(): int
	{
		$stake = $this->walletEnd / $this->stakes;
		$stake = (int)floor($stake);


		if ($stake < self::STAKE_MIN)
		{
			if (($this->walletEnd - $stake) <= 0)
			{
				throw new RuntimeException('wallet broke');
			}


			return self::STAKE_MIN;
		}


		if ($stake < 50)
		{
			$divisor = 5;
		}
		else if ($stake < 100)
		{
			$divisor = 10;
		}
		else if ($stake < 500)
		{
			$divisor = 50;
		}
		else
		{
			$divisor = 100;
		}


		$rest = $stake % $divisor;
		$stake -= $rest;


		return $stake;
	}


	private function calcGain(int $stake, float $profitPercentege): float
	{
		$profit = ($this->odds - 1) * $stake;


		$profit *= $profitPercentege;


		$commission = $profit * $this->commission;


		$netProfit = $profit - $commission;


		$gain = $stake + $netProfit;


		$gain = round($gain, 2);


		return $gain;
	}


	private function calcGreenGain(int $stake): float
	{
		$gain = $this->calcGain($stake, 1);


		return $gain;
	}


	private function calcYellowGain(int $stake): float
	{
		switch ($this->handicapEnding)
		{
			case self::HANDICAP_ENDING_75_OVER:
			case self::HANDICAP_ENDING_25_UNDER:

				$gain = $stake / 2;

				break;


			case self::HANDICAP_ENDING_25_OVER:
			case self::HANDICAP_ENDING_75_UNDER:

				$gain = $this->calcGain($stake, 0.5);

				break;


			default:

				$gain = $stake;

				break;
		}


		return $gain;
	}


	private function getResult(): string
	{
		if (empty($this->results[$this->resultIndex]))
		{
			$this->resultIndex = 0;


			shuffle($this->results);
		}


		$result = $this->results[$this->resultIndex];


		$this->resultIndex++;


		return $result;
	}


	private function buildResults(): MonthsSimulator
	{
		$this->results = [];
		$this->resultIndex = 0;


		array_push($this->results, ...array_pad([], $this->greensPercentage, self::GREEN));
		array_push($this->results, ...array_pad([], $this->redsPercentage, self::RED));


		if ($this->yellowsPercentage)
		{
			array_push($this->results, ...array_pad([], $this->yellowsPercentage, self::YELLOW));
		}


		shuffle($this->results);


		return $this;
	}
}