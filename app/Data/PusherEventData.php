<?php

namespace App\Data;

use App\Models\User;

class PusherEventData
{
    public $title = '';

    public $type = '';

    public $message = '';

    public $channel = '';

    public $event = '';

    public $targets = '';

    public $date = '';

    public $causer = '';

    /**
     * PusherEventData constructor.
     *
     * @param  string  $title
     * @param  string  $type
     * @param  string  $message
     * @param  string  $channel
     * @param  string  $event
     * @param  string  $targets
     * @param  string  $date
     * @param  string  $causer
     */
    public function __construct($title = '', $type = '', $message = '', $channel = '', $event = '', $targets = '', $date = '', $causer = '')
    {
        if (empty($targets)) {
            $targets = User::admins()->pluck('id')->toArray();
        }
        $this->title = $title;
        $this->type = $type;
        $this->message = $message;
        $this->channel = $channel;
        $this->event = $event;
        $this->targets = $targets;
        $this->date = $date;
        $this->causer = $causer;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param  mixed  $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param  mixed  $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param  mixed  $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param  mixed  $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param  mixed  $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return mixed
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * @param  mixed  $targets
     */
    public function setTargets($targets)
    {
        $this->targets = $targets;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param  mixed  $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getCauser()
    {
        return $this->causer;
    }

    /**
     * @param  mixed  $causer
     */
    public function setCauser($causer)
    {
        $this->causer = $causer;
    }
}
