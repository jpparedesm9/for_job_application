<?php

namespace DocumentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Inbox
 *
 * @ORM\Table(name="documents_module_trash")
 * @ORM\Entity(repositoryClass="DocumentBundle\Repository\TrashRepository")
 */
class Trash
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
     * @var string
     *
     * @ORM\Column(name="tray", type="string", length=255, nullable=true)
     */
    private $tray;


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
     * @return Inbox
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
     * @return Inbox
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
     * @return Inbox
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

    /**
     * Set tray.
     *
     * @param string|null $tray
     *
     * @return Trash
     */
    public function setTray($tray = null)
    {
        $this->tray = $tray;

        return $this;
    }

    /**
     * Get tray.
     *
     * @return string|null
     */
    public function getTray()
    {
        return $this->tray;
    }
}
