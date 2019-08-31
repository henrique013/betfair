<?php

use Betfair\MonthsSimulator;
use Betfair\RoundsSimulator;


require_once '../vendor/autoload.php';


$monthsSimulator = (new MonthsSimulator)
	->setWalletStart(1000)
	->setBetsMonth(20)
	->setMonths(11)
	->setCommission(0.065)
	// ----
	->setStakes(50)
	->setOdds(2.00)
	->setHandicap(0.25, 50, 25);

(new RoundsSimulator($monthsSimulator))
	->setRounds(1000)
	->simulate()
	->printRepport();