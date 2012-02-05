<?php

namespace MySociety\VoteBundle\Entity;
use Predis\Client;

class Voting
{
    const SEPARATOR = ':';
    
    /**
     *
     * @var Client
     */
    protected $redis;
    
    /**
     *
     * @var string
     */
    protected $db;
    
    public function __construct(Client $redis, $db = 'vote')
    {
        $this->redis  = $redis;
        $this->db     = $db;
    }
    
    public function getVotersCount()
    {
        $args = func_get_args();
        
        if (sizeof($args))
        {
            return $this->redis->scard(call_user_func_array(array($this, 'makeKey'), $args));
        }
        
        return $this->redis->scard($this->makeKey('voters'));
    }
    
    public function hasVoted()
    {
        $member = $this->redis->sismember($this->makeKey('voters'), $this->getIdentifier());
        return $member;
    }
    
    public function vote($votes)
    {
        foreach ($votes as $v) {
            $key = call_user_func_array(array($this, 'makeKey'), $v);
            $this->redis->sadd($key, $this->getIdentifier());
        }
        
        $this->redis->sadd($this->makeKey('voters'), $this->getIdentifier());
    }
    
    protected function makeKey()
    {
        $args = func_get_args();
        array_unshift($args, $this->db);
        return implode(self::SEPARATOR, $args);
    }
    
    
    protected function getIdentifier()
    {
        return $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REQUEST_TIME'];
    }
}