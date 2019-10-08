<?php

namespace DocumentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Inform
 *
 * @ORM\Table(name="documents_module_inform")
 * @ORM\Entity(repositoryClass="DocumentBundle\Repository\InformRepository")
 */
class Inform
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
    * @ORM\ManyToOne(targetEntity="SiteConfigurationBundle\Entity\User")
    */
    private $user;

    /**
    * @ORM\ManyToOne(targetEntity="DocumentBundle\Entity\Document")
    */
    private $document;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="generated_day", type="datetime")
     */
    private $generatedDay;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set generatedDay.
     *
     * @param \DateTime $generatedDay
     *
     * @return Inform
     */
    public function setGeneratedDay($generatedDay)
    {
        $this->generatedDay = $generatedDay;

        return $this;
    }

    /**
     * Get generatedDay.
     *
     * @return \DateTime
     */
    public function getGeneratedDay()
    {
        return $this->generatedDay;
    }

    /**
     * Set user.
     *
     * @param \SiteConfigurationBundle\Entity\User|null $user
     *
     * @return Inform
     */
    public function setUser(\SiteConfigurationBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \SiteConfigurationBundle\Entity\User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set document.
     *
     * @param \DocumentBundle\Entity\Document|null $document
     *
     * @return Inform
     */
    public function setDocument(\DocumentBundle\Entity\Document $document = null)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document.
     *
     * @return \DocumentBundle\Entity\Document|null
     */
    public function getDocument()
    {
        return $this->document;
    }
}
