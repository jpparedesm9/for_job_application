<?php

namespace DocumentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Inbox
 *
 * @ORM\Table(name="history")
 * @ORM\Entity(repositoryClass="DocumentBundle\Repository\HistoryRepository")
 */
class History
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
     * @ORM\Column(name="action_date", type="datetime")
     */
    private $actionDate;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;


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
     * Set actionDate.
     *
     * @param \DateTime $actionDate
     *
     * @return History
     */
    public function setActionDate($actionDate)
    {
        $this->actionDate = $actionDate;

        return $this;
    }

    /**
     * Get actionDate.
     *
     * @return \DateTime
     */
    public function getActionDate()
    {
        return $this->actionDate;
    }

    /**
     * Set user.
     *
     * @param \SiteConfigurationBundle\Entity\User|null $user
     *
     * @return History
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
     * @return History
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
     * Set comment.
     *
     * @param string|null $comment
     *
     * @return History
     */
    public function setComment($comment = null)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string|null
     */
    public function getComment()
    {
        return $this->comment;
    }
}
