<?php

namespace Betfair;


use RuntimeException;

class MonthsSimulator {

	public const GREEN = 1;
	public const RED = 0;
	public const STAKE_MIN = 10;


	private $walletStart = 1000;
	private $walletEnd = 0;
	private $stakes = 50;
	private $avgOdds = 1.65;
	private $betsMonth = 20;
	private $greensPercentage = 75;
	private $redsPercentage = 25;
	private $maxRedsSequence = 0;
	private $months = 11;
	private $commission = 0.065; // 6.5%


	public function getWalletEnd(): int {

		return $this->walletEnd;
	}


	public function getMaxRedsSequence(): int {

		return $this->maxRedsSequence;
	}


	public function setWalletStart(int $walletStart): MonthsSimulator {

		if ($walletStart < self::STAKE_MIN)
		{
			throw new RuntimeException("walletStart can't be less than " . self::STAKE_MIN);
		}


		$this->walletStart = $walletStart;


		return $this;
	}


	public function setStakes(int $stakes): MonthsSimulator {

		if ($stakes < 1)
		{
			throw new RuntimeException("stakePercentage can't be less than 1");
		}


		$this->stakes = $stakes;


		return $this;
	}


	public function setAvgOdds(float $avgOdds): MonthsSimulator {

		if ($avgOdds <= 1)
		{
			throw new RuntimeException("avgOdds can be greater than 1");
		}
		if ($avgOdds > 1000)
		{
			throw new RuntimeException("avgOdds can't be greater than 1000");
		}


		$this->avgOdds = $avgOdds;


		return $this;
	}


	public function setBetsMonth(int $betsMonth): MonthsSimulator {

		if ($betsMonth < 1)
		{
			throw new RuntimeException("betsMonth can't be less than 1");
		}


		$this->betsMonth = $betsMonth;


		return $this;
	}


	public function setGreensPercentage(int $greensPercentage): MonthsSimulator {

		if ($greensPercentage > 100)
		{
			throw new RuntimeException("greenPercentage can't be greater than 100");
		}
		if ($greensPercentage < 0)
		{
			throw new RuntimeException("greenPercentage can't be less than 0");
		}


		$this->greensPercentage = $greensPercentage;
		$this->redsPercentage = 100 - $greensPercentage;


		return $this;
	}


	public function setMonths(int $months): MonthsSimulator {

		if ($months < 1)
		{
			throw new RuntimeException("months can't be less than 1");
		}


		$this->months = $months;


		return $this;
	}


	public function setCommission(float $commission): MonthsSimulator {

		if ($commission < 0)
		{
			throw new RuntimeException("commission can't be less than 0");
		}
		if ($commission >= 1)
		{
			throw new RuntimeException("commission can't be greater or equals than 1");
		}


		$this->commission = $commission;


		return $this;
	}


	public function simulate(): MonthsSimulator {

		$redsSequence = 0;
		$results = [];


		$this->walletEnd = $this->walletStart;
		$this->maxRedsSequence = 0;


		for ($month = 1; $month <= $this->months; $month++)
		{
			$stake = $this->calcStake();


			for ($bet = 1; $bet <= $this->betsMonth; $bet++)
			{
				if (!$results)
				{
					$results = $this->getResults();
				}


				$this->walletEnd -= $stake;


				$result = array_shift($results);


				if ($result === self::GREEN)
				{
					$redsSequence = 0;


					$gain = $this->calcGain($stake);


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


	private function calcStake(): int {

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


	private function calcGain(int $stake): float {

		$profit = ($this->avgOdds - 1) * $stake;


		$commission = $profit * $this->commission;


		$netProfit = $profit - $commission;


		$gain = $stake + $netProfit;


		return $gain;
	}


	private function getResults(): array {

		$results = [];
		$greens = [];
		$reds = [];


		$greens = array_pad($greens, $this->greensPercentage, self::GREEN);
		$reds = array_pad($reds, $this->redsPercentage, self::RED);


		array_push($results, ...$greens);
		array_push($results, ...$reds);


		shuffle($results);


		return $results;
	}
}