<?php

namespace TeamGantt\Subscreeb\Models;

/**
 * Class Customer
 * @package TeamGantt\Subscreeb\Models
 */
class Customer
{
    /**
     * @var string
     */
    protected string $id;

    /**
     * @var string
     */
    protected string $firstName;

    /**
     * @var string
     */
    protected string $lastName;

    /**
     * @var string
     */
    protected string $emailAddress;

    /**
     * @var Payment
     */
    protected Payment $payment;

    /**
     * Customer constructor.
     * @param string $id
     * @param string $firstName
     * @param string $lastName
     * @param string $emailAddress
     */
    public function __construct(string $id, string $firstName, string $lastName, string $emailAddress)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->emailAddress = $emailAddress;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    /**
     * @return string
     */
    public function getPaymentToken(): string
    {
        return $this->getPayment()->getToken();
    }

    /**
     * @return boolean
     */
    public function isNew(): bool
    {
        return empty($this->id);
    }

    /**
     * @return Payment
     */
    public function getPayment(): Payment
    {
        return $this->payment;
    }

    /**
     * @param Payment $payment
     * @return Customer
     */
    public function setPayment(Payment $payment): self
    {
        $this->payment = $payment;

        return $this;
    }
}
