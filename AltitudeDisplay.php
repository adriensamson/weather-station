<?php

use Tinkerforge\BrickletLCD20x4;

class AltitudeDisplay
{
    protected $brickletLcd;
    protected $dataStore;
    protected $lastData = array();

    public function __construct(BrickletLCD20x4 $brickletLcd, DataStore $dataStore)
    {
        $this->brickletLcd = $brickletLcd;
        $this->dataStore = $dataStore;
    }

    public function clear()
    {
        $this->brickletLcd->clearDisplay();
    }

    public function update()
    {
        $line0 = sprintf("Altitude      %4d m", $this->dataStore->getAltitude());
        $this->brickletLcd->writeLine(0, 0, $line0);
    }
}
