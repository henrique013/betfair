<?php

use Betfair\MonthsSimulator;
use Betfair\RoundsSimulator;


require_once '../vendor/autoload.php';


$monthsSimulator = (new MonthsSimulator)
	->setWalletStart(1000)
	->setStakes(50)
	->setAvgOdds(1.85)
	->setBetsMonth(24)
	->setGreensPercentage(60)
	->setMonths(11)
	->setCommission(0.065);

(new RoundsSimulator($monthsSimulator))
	->setRounds(10000)
	->simulate()
	->printRepport();