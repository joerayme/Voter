<?php
namespace MySociety\VoteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * 
 * @author joe
 *
 * @ORM\Entity
 * @ORM\Table(name="constituencies")
 */
class Constituency
{
    /**
     * 
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $id;
    
    /**
     * 
     * @ORM\Column(type="string", length="80")
     */
    protected $name;
    

    /**
     * Set id
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    
    public function __toString()
    {
        return $this->getName();
    }
}