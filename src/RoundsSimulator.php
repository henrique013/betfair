<?php


namespace Betfair;


use Malenki\Math\Stats\Stats;
use RuntimeException;

class RoundsSimulator {

	/** @var MonthsSimulator */
	private $monthsSimulator;
	private $rounds = 1000;
	private $maxRedsSequences = [];
	private $maxRedsSeqPercentages = [];
	private $wallets = [];
	private $walletsMax = 0;
	private $walletsMin = 0;
	private $walletsAvg = 0;
	private $walletsMedian = 0;


	public function __construct(MonthsSimulator $monthsSimulator) {

		$this->monthsSimulator = $monthsSimulator;
	}


	public function setRounds(int $rounds): RoundsSimulator {

		if ($rounds < 1)
		{
			throw new RuntimeException("stakePercentage can't be less than 1");
		}


		$this->rounds = $rounds;


		return $this;
	}


	public function simulate(): RoundsSimulator {

		$this->resetCounters();


		for ($round = 1; $round <= $this->rounds; $round++)
		{
			$this->monthsSimulator->simulate();


			$redsSequence = $this->monthsSimulator->getMaxRedsSequence();
			$walletEnd = $this->monthsSimulator->getWalletEnd();


			$this->wallets[] = $walletEnd;


			$this
				->updateMaxRedsSequences($redsSequence);
		}


		$this
			->setWalletsStats()
			->setRedsSeqStats();


		return $this;
	}


	public function printRepport(): RoundsSimulator {

		echo PHP_EOL;
		echo '[WALLETS]' . PHP_EOL;
		echo '     max: ' . number_format($this->walletsMax, 0, ',', '.') . PHP_EOL;
		echo '     min: ' . number_format($this->walletsMin, 0, ',', '.') . PHP_EOL;
		echo '     avg: ' . number_format($this->walletsAvg, 0, ',', '.') . PHP_EOL;
		echo '  median: ' . number_format($this->walletsMedian, 0, ',', '.') . PHP_EOL;


		echo PHP_EOL;
		echo '-----------------------------' . PHP_EOL . PHP_EOL;
		echo '[REDS]' . PHP_EOL;
		echo '  max sequences:' . PHP_EOL;


		foreach ($this->maxRedsSeqPercentages as $sequence => $percentage)
		{
			echo "  -> {$sequence} : {$percentage}" . PHP_EOL;
		}


		return $this;
	}


	private function resetCounters(): RoundsSimulator {

		$this->maxRedsSequences = [];
		$this->maxRedsSeqPercentages = [];
		$this->wallets = [];
		$this->walletsMax = 0;
		$this->walletsMin = 0;
		$this->walletsAvg = 0;
		$this->walletsMedian = 0;


		return $this;
	}


	private function setRedsSeqStats(): RoundsSimulator {


		foreach ($this->maxRedsSequences as $sequence => $occurrences)
		{
			$percentage = ($occurrences / $this->rounds) * 100;
			$percentage = number_format($percentage, 2, ',', '.') . '%';
			$percentage = str_pad($percentage, 6, '0', STR_PAD_LEFT);


			$sequence = str_pad($sequence, 2, '0', STR_PAD_LEFT);


			$this->maxRedsSeqPercentages[$sequence] = $percentage;
		}


		ksort($this->maxRedsSeqPercentages);


		return $this;
	}


	private function setWalletsStats(): RoundsSimulator {

		$stats = new Stats($this->wallets);


		$this->walletsMin = $stats->min();
		$this->walletsMax = $stats->max();
		$this->walletsAvg = $stats->mean();
		$this->walletsMedian = $stats->median();


		return $this;
	}


	private function updateMaxRedsSequences(int $redsSequence): RoundsSimulator {

		if (!isset($this->maxRedsSequences[$redsSequence]))
		{
			$this->maxRedsSequences[$redsSequence] = 0;
		}


		$this->maxRedsSequences[$redsSequence]++;


		return $this;
	}
}