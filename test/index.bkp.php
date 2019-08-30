<?php

simulate(false);

// ======================================== //

function simulate($quiet)
{
    $wallet = 1000;
    $oddGreen = 74;
    $oddRed = 100 - $oddGreen;
    $resultsDefault = [];
    $results = [];


    for ($i = 0; $i < $oddGreen; $i++)
    {
        $resultsDefault[] = true;
    }
    for ($i = 0; $i < $oddRed; $i++)
    {
        $resultsDefault[] = false;
    }


    for ($month = 1; $month <= 11; $month++)
    {
        $stake = $wallet / 50;
        $greens = 0;
        $reds = 0;


        for ($bet = 1; $bet <= 24; $bet++)
        {
            if (!$results)
            {
                $results = $resultsDefault;
                shuffle($results);
            }


            $wallet -= $stake;
            $result = array_shift($results);


            if ($result)
            {
                $wallet += ($stake * 1.50);
                $greens++;
            }
            else
            {
                $reds++;
            }
        }


        if (!$quiet)
        {
            printf("month: %02d | greens: %02d | reds: %02d | wallet: %s" . PHP_EOL, $month, $greens, $reds, number_format($wallet, 0, ',', '.'));
        }
    }


    return $wallet;
}