<?php

namespace App\Data;

class ChatMessagePayload
{
    public $sender = '';

    public $sender_profile = '';

    public $sender_deleted = '';

    public $receiver = '';

    public $receiver_profile = '';

    public $receiver_deleted = '';

    public $seen = false;

    public $delivered = false;

    public $type = 'text';

    public $message = '';

    /**
     * ChatMessagePayload constructor.
     *
     * @param  string  $sender
     * @param  string  $sender_profile
     * @param  bool  $sender_deleted
     * @param  string  $receiver
     * @param  string  $receiver_profile
     * @param  bool  $receiver_deleted
     * @param  bool  $seen
     * @param  bool  $delivered
     * @param  string  $type
     * @param  string  $message
     */
    public function __construct(
        $sender = '',
        $sender_profile = '',
        $sender_deleted = false,
        $receiver = '',
        $receiver_profile = '',
        $receiver_deleted = false,
        $seen = false,
        $delivered = false,
        $type = 'text',
        $message = '')
    {
        $this->sender = $sender;
        $this->sender_profile = $sender_profile;
        $this->sender_deleted = $sender_deleted;
        $this->receiver = $receiver;
        $this->receiver_profile = $receiver_profile;
        $this->receiver_deleted = $receiver_deleted;
        $this->seen = $seen;
        $this->delivered = $delivered;
        $this->type = $type;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param  string  $sender
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
    }

    /**
     * @return string
     */
    public function getSenderProfile()
    {
        return $this->sender_profile;
    }

    /**
     * @param  string  $sender_profile
     */
    public function setSenderProfile($sender_profile)
    {
        $this->sender_profile = $sender_profile;
    }

    /**
     * @return string
     */
    public function getSenderDeleted()
    {
        return $this->sender_deleted;
    }

    /**
     * @param  string  $sender_deleted
     */
    public function setSenderDeleted($sender_deleted)
    {
        $this->sender_deleted = $sender_deleted;
    }

    /**
     * @return string
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * @param  string  $receiver
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;
    }

    /**
     * @return string
     */
    public function getReceiverProfile()
    {
        return $this->receiver_profile;
    }

    /**
     * @param  string  $receiver_profile
     */
    public function setReceiverProfile($receiver_profile)
    {
        $this->receiver_profile = $receiver_profile;
    }

    /**
     * @return string
     */
    public function getReceiverDeleted()
    {
        return $this->receiver_deleted;
    }

    /**
     * @param  string  $receiver_deleted
     */
    public function setReceiverDeleted($receiver_deleted)
    {
        $this->receiver_deleted = $receiver_deleted;
    }

    /**
     * @return bool
     */
    public function isSeen()
    {
        return $this->seen;
    }

    /**
     * @param  bool  $seen
     */
    public function setSeen($seen)
    {
        $this->seen = $seen;
    }

    /**
     * @return bool
     */
    public function isDelivered()
    {
        return $this->delivered;
    }

    /**
     * @param  bool  $delivered
     */
    public function setDelivered($delivered)
    {
        $this->delivered = $delivered;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param  string  $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param  string  $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}
