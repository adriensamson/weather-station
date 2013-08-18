<?php

class HistoryDataStore extends DataStore
{
    const TYPE_ILLUMINANCE  = 'illuminance';
    const TYPE_HUMIDITY     = 'humidity';
    const TYPE_AIR_PRESSURE = 'air-pressure';
    const TYPE_TEMPERATURE  = 'temperature';

    const RANGE_12_MINUTES = '12minutes';
    const RANGE_1_HOUR     = '1hour';

    protected $history = array();

    public function setIlluminance($illuminance)
    {
        parent::setIlluminance($illuminance);
        $this->addHistory(self::TYPE_ILLUMINANCE, $illuminance);
    }
    public function setHumidity($humidity)
    {
        parent::setHumidity($humidity);
        $this->addHistory(self::TYPE_HUMIDITY, $humidity);
    }
    public function setAirPressure($airPressure)
    {
        parent::setAirPressure($airPressure);
        $this->addHistory(self::TYPE_AIR_PRESSURE, $airPressure);
    }
    public function setTemperature($temperature)
    {
        parent::setTemperature($temperature);
        $this->addHistory(self::TYPE_TEMPERATURE, $temperature);
    }

    public function getHistory($type, $range)
    {
        list($start, $step) = $this->getStartStepForRange($range);
        $timeRef = strtotime($start);
        $history = array();
        for ($i = 0; $i < 12; $i++) {
            $timeRef = strtotime($step, $timeRef);
            $date = date('H:i', $timeRef);
            if (isset($this->history[$type][$date])) {
                $history[$i] = $this->history[$type][$date];
            } else {
                $history[$i] = null;
            }
        }

        return $history;
    }

    protected function getStartStepForRange($range)
    {
        $return = array('-11 minutes', '+1 minute');
        switch ($range) {
            case self::RANGE_12_MINUTES:
                // default
                break;
            case self::RANGE_1_HOUR:
                $return = array('-1 hour', '+5 minutes');
                break;
        }
        return $return;
    }

    protected function addHistory($type, $value)
    {
        if (!isset($this->history[$type][date('H:i')])) {
            $this->history[$type][date('H:i')] = $value;
        }
    }
}
