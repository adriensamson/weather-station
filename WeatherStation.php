<?php

require_once __DIR__.'/Tinkerforge/IPConnection.php';
require_once __DIR__.'/Tinkerforge/BrickletLCD20x4.php';
require_once __DIR__.'/Tinkerforge/BrickletAmbientLight.php';
require_once __DIR__.'/Tinkerforge/BrickletHumidity.php';
require_once __DIR__.'/Tinkerforge/BrickletBarometer.php';

use Tinkerforge\IPConnection;
use Tinkerforge\BrickletLCD20x4;
use Tinkerforge\BrickletAmbientLight;
use Tinkerforge\BrickletHumidity;
use Tinkerforge\BrickletBarometer;

class WeatherStation
{
    const HOST = 'localhost';
    const PORT = 4223;

    /**
     * @var IPConnection
     */
    protected $ipcon;

    /**
     * @var BrickletLCD20x4
     */
    protected $brickletLCD;

    /**
     * @var BrickletAmbientLight
     */
    protected $brickletAmbientLight;

    /**
     * @var BrickletHumidity
     */
    protected $brickletHumidity;

    /**
     * @var BrickletBarometer
     */
    protected $brickletBarometer;

    protected $displayOff = false;

    public function __construct()
    {
        $this->ipcon = new IPConnection();
        while(true) {
            try {
                $this->ipcon->connect(self::HOST, self::PORT);
                break;
            } catch(Exception $e) {
                sleep(1);
            }
        }
        echo "Connected\n";

        $this->ipcon->registerCallback(IPConnection::CALLBACK_ENUMERATE, array($this, 'onEnumerate'));
        $this->ipcon->registerCallback(IPConnection::CALLBACK_CONNECTED, array($this, 'onConnected'));

        while(true) {
            try {
                $this->ipcon->enumerate();
                break;
            } catch(Exception $e) {
                sleep(1);
            }
        }
        echo "Enumerated\n";
    }

    public function onConnected($connectedReason)
    {
        if ($connectedReason == IPConnection::CONNECT_REASON_AUTO_RECONNECT) {
            echo "Auto Reconnect\n";
            while(true) {
                try {
                    $this->ipcon->enumerate();
                    break;
                } catch(Exception $e) {
                    sleep(1);
                }
            }
            echo "Re-enumerated\n";
        }
    }

    public function onEnumerate($uid, $connectedUid, $position, $hardwareVersion,
        $firmwareVersion, $deviceIdentifier, $enumerationType)
    {
        if (in_array($enumerationType, [IPConnection::ENUMERATION_TYPE_CONNECTED, IPConnection::ENUMERATION_TYPE_AVAILABLE])) {
        
            if ($deviceIdentifier == BrickletLCD20x4::DEVICE_IDENTIFIER) {
                try {
                    $this->brickletLCD = new BrickletLCD20x4($uid, $this->ipcon);
                    $this->brickletLCD->clearDisplay();
                    $this->brickletLCD->backlightOn();
                    $this->brickletLCD->registerCallback(BrickletLCD20x4::CALLBACK_BUTTON_PRESSED, array($this, 'onButtonPressed'));
                    $this->brickletLCD->registerCallback(BrickletLCD20x4::CALLBACK_BUTTON_RELEASED, array($this, 'onButtonReleased'));
                    echo "LCD 20x4 initialized\n";
                } catch(Exception $e) {
                    $this->brickletLCD = null;
                    echo "LCD 20x4 init failed: $e\n";
                }

            } elseif ($deviceIdentifier == BrickletAmbientLight::DEVICE_IDENTIFIER) {
                try {
                    $this->brickletAmbientLight = new BrickletAmbientLight($uid, $this->ipcon);
                    $this->brickletAmbientLight->setIlluminanceCallbackPeriod(1000);
                    $this->brickletAmbientLight->registerCallback(BrickletAmbientLight::CALLBACK_ILLUMINANCE, array($this, 'onIlluminance'));
                    echo "Ambient Light initialized\n";
                } catch(Exception $e) {
                    $this->brickletAmbientLight = null;
                    echo "Ambient Light init failed: $e\n";
                }
            } elseif ($deviceIdentifier == BrickletHumidity::DEVICE_IDENTIFIER) {
                try {
                    $this->brickletHumidity = new BrickletHumidity($uid, $this->ipcon);
                    $this->brickletHumidity->setHumidityCallbackPeriod(1000);
                    $this->brickletHumidity->registerCallback(BrickletHumidity::CALLBACK_HUMIDITY, array($this, 'onHumidity'));
                    echo "Humidity initialized\n";
                } catch(Exception $e) {
                    $this->brickletHumidity = null;
                    echo "Humidity init failed: $e\n";
                }

            } elseif ($deviceIdentifier == BrickletBarometer::DEVICE_IDENTIFIER) {
                try {
                    $this->brickletBarometer = new BrickletBarometer($uid, $this->ipcon);
                    $this->brickletBarometer->setAirPressureCallbackPeriod(1000);
                    $this->brickletBarometer->registerCallback(BrickletBarometer::CALLBACK_AIR_PRESSURE, array($this, 'onAirPressure'));
                    echo "Barometer initialized\n";
                } catch(Exception $e) {
                    $this->brickletBarometer = null;
                    echo "Barometer init failed: $e\n";
                }
            }
        }
    }

    public function onIlluminance($illuminance)
    {
        if ($this->brickletLCD !== null && !$this->displayOff) {
            $text = sprintf("Illuminance   %3d lx", $illuminance/10.0);
            $this->brickletLCD->writeLine(0, 0, $text);
        }
    }

    public function onHumidity($humidity)
    {
        if ($this->brickletLCD !== null && !$this->displayOff) {
            $text = sprintf("Humidity      %3d %%", $humidity/10.0);
            $this->brickletLCD->writeLine(1, 0, $text);
        }
    }

    public function onAirPressure($airPressure)
    {
        if ($this->brickletLCD !== null && !$this->displayOff) {
            $text = sprintf("Air Press    %4d mb", $airPressure/1000.0);
            $this->brickletLCD->writeLine(2, 0, $text);

            $temperature = $this->brickletBarometer->getChipTemperature();
            // 0xDF == ° on LCD 20x4 charset
            $text = sprintf("Temperature  %4.1f %cC", $temperature/100.0, 0xDF);
            $this->brickletLCD->writeLine(3, 0, $text);
        }
    }

    public function onButtonPressed($buttonId)
    {
        if ($buttonId === 0) {
            if ($this->brickletLCD->isBacklightOn()) {
                $this->brickletLCD->backlightOff();
            } else {
                $this->brickletLCD->backlightOn();
            }
        } elseif ($buttonId === 1) {
            $this->displayOff = true;
            $altitude = $this->brickletBarometer->getAltitude();
            $this->brickletLCD->clearDisplay();
            $this->brickletLCD->writeLine(0, 0, sprintf('Altitude      %4d m', $altitude/100.0));
        }
    }

    public function onButtonReleased($buttonId)
    {
        if ($buttonId === 1) {
            $this->displayOff = false;
        }
    }
    
    public function dispatchCallbacks()
    {
        $this->ipcon->dispatchCallbacks(-1);
    }
}

$weatherStation = new WeatherStation();
$weatherStation->dispatchCallbacks();

