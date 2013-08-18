<?php

use Tinkerforge\BrickletLCD20x4;

class HistoryDisplay
{
    protected $brickletLcd;
    protected $dataStore;
    protected $dataType = HistoryDataStore::TYPE_ILLUMINANCE;
    protected $range = HistoryDataStore::RANGE_12_MINUTES;

    public function __construct(BrickletLCD20x4 $brickletLcd, HistoryDataStore $dataStore)
    {
        $this->brickletLcd = $brickletLcd;
        $this->dataStore = $dataStore;
    }

    public function clear()
    {
        $this->brickletLcd->setCustomCharacter(0, [0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x1F]);
        $this->brickletLcd->setCustomCharacter(1, [0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x1F, 0x1F]);
        $this->brickletLcd->setCustomCharacter(2, [0x00, 0x00, 0x00, 0x00, 0x00, 0x1F, 0x1F, 0x1F]);
        $this->brickletLcd->setCustomCharacter(3, [0x00, 0x00, 0x00, 0x00, 0x1F, 0x1F, 0x1F, 0x1F]);
        $this->brickletLcd->setCustomCharacter(4, [0x00, 0x00, 0x00, 0x1F, 0x1F, 0x1F, 0x1F, 0x1F]);
        $this->brickletLcd->setCustomCharacter(5, [0x00, 0x00, 0x1F, 0x1F, 0x1F, 0x1F, 0x1F, 0x1F]);
        $this->brickletLcd->setCustomCharacter(6, [0x00, 0x1F, 0x1F, 0x1F, 0x1F, 0x1F, 0x1F, 0x1F]);
        $this->brickletLcd->setCustomCharacter(7, [0x1F, 0x1F, 0x1F, 0x1F, 0x1F, 0x1F, 0x1F, 0x1F]);
        $this->brickletLcd->clearDisplay();
        $this->updateType();
    }

    public function update()
    {
        $history = $this->dataStore->getHistory($this->dataType, $this->range);

        $min = min(array_filter($history));
        $max = max(array_filter($history));
        $step = max(($max - $min) / 14.0, 1/14.0);
        $lineHigh = '';
        $lineLow = '';
        for ($i = 0; $i < 12; $i++) {
            if ($history[$i] > 0) {
                $height = round(($history[$i] - $min) / $step);
                $lineHigh .= ($height <= 7) ? ' ' : chr(max($height - 7, 0) + 8);
                $lineLow  .= chr(min($height, 7) + 8);
            } else {
                $lineHigh .= ' ';
                $lineLow  .= ' ';
            }
        }

        $this->brickletLcd->writeLine(2, 0, $lineHigh);
        $this->brickletLcd->writeLine(3, 0, $lineLow);
        $this->updateTimes();
        $format = $this->getFormat();
        $this->brickletLcd->writeLine(3, 13, sprintf($format, $min));
        $this->brickletLcd->writeLine(2, 13, sprintf($format, $max));
    }

    public function onButton2()
    {
        switch ($this->dataType) {
            case HistoryDataStore::TYPE_ILLUMINANCE:
                $this->dataType = HistoryDataStore::TYPE_HUMIDITY;
                break;
            case HistoryDataStore::TYPE_HUMIDITY:
                $this->dataType = HistoryDataStore::TYPE_AIR_PRESSURE;
                break;
            case HistoryDataStore::TYPE_AIR_PRESSURE:
                $this->dataType = HistoryDataStore::TYPE_TEMPERATURE;
                break;
            case HistoryDataStore::TYPE_TEMPERATURE:
                $this->dataType = HistoryDataStore::TYPE_ILLUMINANCE;
                break;
        }
        $this->brickletLcd->clearDisplay();
        $this->updateType();
        $this->update();
    }

    public function onButton3()
    {
        switch ($this->range) {
            case HistoryDataStore::RANGE_12_MINUTES:
                $this->range = HistoryDataStore::RANGE_1_HOUR;
                break;
            case HistoryDataStore::RANGE_1_HOUR:
                $this->range = HistoryDataStore::RANGE_12_MINUTES;
                break;
        }
        $this->update();
    }

    protected function updateType()
    {
        $type = '';
        $unit = '  ';
        switch ($this->dataType) {
            case HistoryDataStore::TYPE_ILLUMINANCE:
                $type = 'Illuminance';
                $unit = 'lx';
                break;
            case HistoryDataStore::TYPE_HUMIDITY:
                $type = 'Humidity';
                $unit = '%';
                break;
            case HistoryDataStore::TYPE_AIR_PRESSURE:
                $type = 'Air Pressure';
                $unit = 'mb';
                break;
            case HistoryDataStore::TYPE_TEMPERATURE:
                $type = 'Temperature';
                $unit = "\xDFC";
                break;
        }

        $this->brickletLcd->writeLine(0, 0, $type);
        $this->brickletLcd->writeLine(2, 18, $unit);
        $this->brickletLcd->writeLine(3, 18, $unit);
    }

    protected function updateTimes()
    {
        switch ($this->range) {
            case HistoryDataStore::RANGE_12_MINUTES:
                $this->brickletLcd->writeLine(1, 0, date('H:i', strtotime('-11 minutes')));
                break;
            case HistoryDataStore::RANGE_1_HOUR:
                $this->brickletLcd->writeLine(1, 0, date('H:i', strtotime('-1 hour')));
                break;
        }
        $this->brickletLcd->writeLine(1, 7, date('H:i'));
    }

    protected function getFormat()
    {
        switch ($this->dataType) {
            case HistoryDataStore::TYPE_ILLUMINANCE:
                return ' %3d';
            case HistoryDataStore::TYPE_HUMIDITY:
                return ' %3d';
            case HistoryDataStore::TYPE_AIR_PRESSURE:
                return '%4d';
            case HistoryDataStore::TYPE_TEMPERATURE:
                return '%4.1f';
        }
        return '';
    }
}
