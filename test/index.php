<?php

use Betfair\MonthsSimulator;
use Betfair\RoundsSimulator;


require_once '../vendor/autoload.php';


$monthsSimulator = (new MonthsSimulator)
	->setWalletStart(1000)
	->setBetsMonth(24)
	->setMonths(11)
	->setCommission(0)
	// ----
	->setStakes(50)
	->setOdds(1.54)
	->setHandicap(-1, 46, 26);

(new RoundsSimulator($monthsSimulator))
	->setRounds(1000)
	->simulate()
	->printRepport();