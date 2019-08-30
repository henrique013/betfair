<?php

use Betfair\MonthsSimulator;
use Betfair\RoundsSimulator;


require_once '../vendor/autoload.php';


$monthsSimulator = (new MonthsSimulator)
    ->setWalletStart(1000)
    ->setStakes(50)
    ->setAvgOdds(1.80)
    ->setBetsMonth(24)
    ->setGreensPercentage(60)
    ->setMonths(11);

(new RoundsSimulator($monthsSimulator))
    ->setRounds(1000)
    ->simulate()
    ->printRepport();