<?php

class DataStore
{
    protected $illuminance;
    protected $humidity;
    protected $airPressure;
    protected $temperature;
    protected $altitude;

    /**
     * @param mixed $airPressure
     */
    public function setAirPressure($airPressure)
    {
        $this->airPressure = $airPressure;
    }

    /**
     * @return mixed
     */
    public function getAirPressure()
    {
        return $this->airPressure;
    }

    /**
     * @param mixed $altitude
     */
    public function setAltitude($altitude)
    {
        $this->altitude = $altitude;
    }

    /**
     * @return mixed
     */
    public function getAltitude()
    {
        return $this->altitude;
    }

    /**
     * @param mixed $humidity
     */
    public function setHumidity($humidity)
    {
        $this->humidity = $humidity;
    }

    /**
     * @return mixed
     */
    public function getHumidity()
    {
        return $this->humidity;
    }

    /**
     * @param mixed $illuminance
     */
    public function setIlluminance($illuminance)
    {
        $this->illuminance = $illuminance;
    }

    /**
     * @return mixed
     */
    public function getIlluminance()
    {
        return $this->illuminance;
    }

    /**
     * @param mixed $temperature
     */
    public function setTemperature($temperature)
    {
        $this->temperature = $temperature;
    }

    /**
     * @return mixed
     */
    public function getTemperature()
    {
        return $this->temperature;
    }
}
