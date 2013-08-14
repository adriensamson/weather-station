<?php

use Tinkerforge\BrickletLCD20x4;

class LinesDisplay
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
        $line0 = sprintf("Illuminance   %3d lx", $this->dataStore->getIlluminance());
        $this->brickletLcd->writeLine(0, 0, $line0);

        $line1 = sprintf("Humidity      %3d %%", $this->dataStore->getHumidity());
        $this->brickletLcd->writeLine(1, 0, $line1);

        $line2 = sprintf("Air Pressure %4d mb", $this->dataStore->getAirPressure());
        $this->brickletLcd->writeLine(2, 0, $line2);

        $line3 = sprintf("Temperature  %4.1f %cC", $this->dataStore->getTemperature(), 0xDF);
        $this->brickletLcd->writeLine(3, 0, $line3);
    }
}
