<?php

namespace LikeLight;

use Phpiwire\Board;
use Phpiwire\Pin;

class Rpi
{
    /** @var Board */
    protected $board;
    /** @var Pin[] */
    protected $pins;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->board = new Board();
        $this->pins = [
            'r' => $this->board->getPin($config->get('pins')->r)->mode(Pin::SOFT_PWM_OUT),
            'g' => $this->board->getPin($config->get('pins')->g)->mode(Pin::SOFT_PWM_OUT),
            'b' => $this->board->getPin($config->get('pins')->b)->mode(Pin::SOFT_PWM_OUT)
        ];
    }

    /**
     * @return $this
     */
    public function resetAllPins()
    {
        return $this
            ->setPinValue('r', 0)
            ->setPinValue('g', 0)
            ->setPinValue('b', 0);
    }

    /**
     * @param int $delay
     * @return $this
     */
    public function fadeAllPinsIn($delay = 15000)
    {
        for ($i = 0; $i <= 100; $i++) {
            $this->setPinValue('r', $i)
                ->setPinValue('g', $i)
                ->setPinValue('b', $i);
            usleep($delay);
        }
        return $this;
    }

    /**
     * @param int $delay
     * @return $this
     */
    public function fadeAllPinsOut($delay = 15000)
    {
        for ($i = 100; $i >= 0; $i--) {
            $this->setPinValue('r', $i)
                ->setPinValue('g', $i)
                ->setPinValue('b', $i);
            usleep($delay);
        }
        return $this;
    }

    /**
     * @param $pin
     * @param int $delay In micro seconds
     * @return $this
     */
    public function fadePinIn($pin, $delay = 15000)
    {
        for ($i = 0; $i <= 100; $i++) {
            $this->setPinValue($pin, $i);
            usleep($delay);
        }
        return $this;
    }

    /**
     * @param $pin
     * @param int $delay In micro seconds
     * @return $this
     */
    public function fadePinOut($pin, $delay = 15000)
    {
        for ($i = 100; $i >= 0; $i--) {
            $this->setPinValue($pin, $i);
            usleep($delay);
        }
        return $this;
    }

    /**
     * @param $pin
     * @param $value
     * @return $this
     */
    public function setPinValue($pin, $value)
    {
        if (!in_array($pin, ['r', 'g', 'b'])) {
            return $this;
        }
        $this->pins[$pin]->softPwmWrite($value);
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setAllPinsValue($value)
    {
        $this->setPinValue('r', $value)
            ->setPinValue('g', $value)
            ->setPinValue('b', $value);
        return $this;
    }
}
