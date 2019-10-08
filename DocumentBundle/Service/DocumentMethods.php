<?php
namespace DocumentBundle\Service;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use SiteConfigurationBundle\Entity\Account;
use Symfony\Component\HttpFoundation\Response;

class DocumentMethods
{
    private $em;
    private $api_key;
    private $from;
    private $pass;
    private $contractAddress;
    private $container;
    private $templating;
    public function __construct($container,$em)  {
        $this->em = $em;
        $this->container= $container;
        $this->templating=$container->get('templating');
    }
    public function getEllaborationDocumentsCounter($userId)
    {
      $number=$this->em->createQueryBuilder()
              ->select('count(o)')
              ->from('DocumentBundle\Entity\Document','o')
              ->where('o.user=:user_id')
              ->andWhere('o.sent=0')
              ->setParameter('user_id',$userId)
              ->setMaxResults(1)
              ->getQuery()
              ->getSingleScalarResult();
       return $number;
    }

    public function getInboxDocuments($userId)
    {
      $records=$this->em->createQueryBuilder()
              ->select('o')
              ->from('DocumentBundle\Entity\Inbox','o')
              ->where('o.user=:user_id')
              ->setParameter('user_id',$userId)
              ->orderBy('o.id','DESC')
              ->setMaxResults(5)
              ->getQuery()
              ->getResult();
       return $records;

    }

    public function getDocumentsSentCounter($userId)
    {
      $number=$this->em->createQueryBuilder()
              ->select('count(o)')
              ->from('DocumentBundle\Entity\DocumentSent','o')
              ->where('o.user=:user_id')
              ->setParameter('user_id',$userId)
              ->setMaxResults(1)
              ->getQuery()
              ->getSingleScalarResult();
       return $number;
    }

    public function getTrayCounter($userId,$tray)
    {
      $table="";
      switch($tray)
      {
         case 'trash':
         {
           $table="DocumentBundle\Entity\Trash";
           break;
         }
         case 'inform':
         {
           $table="DocumentBundle\Entity\Inform";
           break;
         }

         case 'archive':
         {
           $table="DocumentBundle\Entity\Archive";
           break;
         }
         
         default:break;
      }
      $number=$this->em->createQueryBuilder()
              ->select('count(o)')
              ->from($table,'o')
              ->where('o.user=:user_id')
              ->setParameter('user_id',$userId)
              ->setMaxResults(1)
              ->getQuery()
              ->getSingleScalarResult();
       return $number;
    }

    public function getReceivedDocumentsCounter($userId)
    {
      $number=$this->em->createQueryBuilder()
              ->select('count(o)')
              ->from('DocumentBundle\Entity\Inbox','o')
              ->where('o.user=:user_id')
              ->setParameter('user_id',$userId)
              ->setMaxResults(1)
              ->getQuery()
              ->getSingleScalarResult();
       return $number;
    }

    public function getRegisteredUsersCounter()
    {
        $number=$this->em->createQueryBuilder()
                ->select('count(o)')
                ->from('SiteConfigurationBundle\Entity\User','o')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleScalarResult();
        return $number;
    }
}
