<?php

use Betfair\MonthsSimulator;
use Betfair\RoundsSimulator;


require_once '../vendor/autoload.php';


$monthsSimulator = (new MonthsSimulator)
	->setWalletStart(1000)
	->setBetsMonth(20)
	->setMonths(11)
	->setCommission(6.5)
	// ----
	->setStakes(50)
	->setOdds(1.22)
	->setHandicap(0, 73.33, 16.79);

(new RoundsSimulator($monthsSimulator))
	->setRounds(1000)
	->simulate()
	->printRepport();