<?php

class VmdRequest
{
    /**
     * @var string
     */
    protected $AppVerison;

    /**
     * @var string
     */
    protected $Timestamp;

    /**
     * @var string
     */
    protected $IpAddress;

    /**
     * @var string
     */
    protected $FullAgent;

    /**
     * @var string
     */
    protected $UserName;

    /**
     * @var string
     */
    protected $Password;

    /**
     * @var string
     */
    protected $Token;

    /**
     * @var string
     */
    protected $LoginPage;

    /**
     * @var string
     */
    protected $AesKey;

    /**
     * VmdRequest constructor.
     * @param string $AppVerison
     * @param string $Timestamp
     * @param string $IpAddress
     * @param string $FullAgent
     * @param string $UserName
     * @param string $Password
     * @param string $Token
     * @param string $LoginPage
     * @param string $AesKey
     */
    public function __construct($AppVerison, $Timestamp, $IpAddress, $FullAgent, $UserName, $Password, $Token, $LoginPage, $AesKey)
    {
        $this->AppVerison = $AppVerison;
        $this->Timestamp = $Timestamp;
        $this->IpAddress = $IpAddress;
        $this->FullAgent = $FullAgent;
        $this->UserName = $UserName;
        $this->Password = $Password;
        $this->Token = $Token;
        $this->LoginPage = $LoginPage;
        $this->AesKey = $AesKey;
    }


}