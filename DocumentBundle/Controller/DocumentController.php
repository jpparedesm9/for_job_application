<?php

namespace DocumentBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use DocumentBundle\Entity\Document;
use DocumentBundle\Entity\DocumentSent;
use DocumentBundle\Entity\History;
use DocumentBundle\Entity\Inbox;
use DocumentBundle\Entity\Archive;
use DocumentBundle\Entity\Inform;
use DocumentBundle\Entity\Trash;

class DocumentController extends Controller
{

    public function showSavedDocumentAction($documentId,$trayId)
    {
      $em = $this->get('doctrine')->getManager();
      $url="";
      switch($trayId)
        {
          case 1:{
            $url=$this->generateUrl('show_inbox');
            break;
          }

          default: break;
        }
        $document = $em->createQueryBuilder()
                ->select('o')
                ->from("DocumentBundle:Document",'o')
                ->where('o=:document_id')
                ->setParameter('document_id',$documentId)
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();
     return $this->render('DocumentBundle:Document:show_saved_document.html.twig',array("documentId"=>$documentId,"url"=>$url,"trayId"=>$trayId,"document"=>$document));
    }
    public function extractSavedDocumentFromDiskAction($documentId,$trayId)
    {
        $em = $this->get('doctrine')->getManager();
        $pathToSave= $this->get('kernel')->getRootDir() . '/../documents/';
        $record = $em->createQueryBuilder()
                ->select('o')
                ->from("DocumentBundle:Document",'o')
                ->where('o=:document_id')
                ->setParameter('document_id',$documentId)
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();
        $fileName = $record->getPath();
        $final_file=file_get_contents($pathToSave.$fileName, true);
        return new Response
        (
          $final_file,
          200,
          array(
            'Content-Type'=> 'application/pdf',
            'Content-Disposition'=>'inline;filename=temp.pdf'
          )
        );
    }
    private function getPdfOutput($documentId)
    {
        $em = $this->get('doctrine')->getManager();
        $snappy=$this->get('knp_snappy.pdf');
        $document=$em->getRepository('DocumentBundle:Document')->findOneBy(array('id' => $documentId));
        $header="";
        $html = $this->renderView('template/header.html.twig',array('header'=>$header));
        $htmlFooter="";
        $options = [
                   'header-html' => $html,
                   'footer-html' => $htmlFooter,
               ];
         $content=html_entity_decode($document->getContent());
         $htmlBody= $this->renderView('template/body.html.twig',array('body'=>$content));
         $final_file=$snappy->getOutputFromHtml($htmlBody,$options);

         return $final_file;
    }
    private function createPdfToDisk($documentId)
    {
       $final_file=$this->getPdfOutput($documentId);
       $pathToSave= $this->get('kernel')->getRootDir() . '/../documents/';
       $documentName='document'. time() . '.pdf';
       file_put_contents($pathToSave.$documentName, $final_file);
       return $documentName;

    }
    public function sendDocumentAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $documentId=$request->request->get('document_id');
        $document=$em->getRepository('DocumentBundle:Document')->findOneBy(array('id' => $documentId));
        $savedDocumentName= $this->createPdfToDisk($documentId);
        try{
            $fromUserId=unserialize($document->getFromUser())[0];
            $fromUser=$em->getRepository('SiteConfigurationBundle:User')->findOneBy(array('id' => $fromUserId));
            $toUserIds=unserialize($document->getToUser());
            $document->setPath($savedDocumentName);
            $destinataries="";
            foreach($toUserIds as $userId)
            {
                $toUser=$em->getRepository('SiteConfigurationBundle:User')->findOneBy(array('id' => $userId));
                $inboxRecord=new Inbox();
                $inboxRecord->setUser($toUser);
                $inboxRecord->setDocument($document);
                $inboxRecord->setGeneratedDay(new \DateTime());
                $em->persist($inboxRecord);

                $destinataries.= $toUser->getName()." ".$toUser->getLastName().", ";
            }
            $historyRecord=new History();
            $historyRecord->setUser($fromUser);
            $historyRecord->setDocument($document);
            $historyRecord->setActionDate(new \DateTime());
            $historyRecord->setComment("Documento Creado.");
            $em->persist($historyRecord);

            $document->setSent(1);
            $em->persist($document);
            $em->flush();
        } catch (\Doctrine\ORM\EntityNotFoundException $ex) {
              return new JsonResponse(array("event"=>"error", "msg"=>"Error al borrar... Por favor intente nuevamente.."));
        }
        return new JsonResponse(array("event"=>"success", "msg"=> "Documento enviado de forma exitosa"));
    }
    public function verifyAndConfirmAction($documentId)
    {
        return $this->render('DocumentBundle:Document:verify_and_confirm.html.twig',array("documentId"=>$documentId));
    }
    public function removeMultipleDocumentsAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $documentIds=$request->request->get('document_ids');
        $documents=json_decode($documentIds);
        $tray=$request->request->get('tray');
        $table=$this->getTableBasedInTray($tray);
        $documentsToDelete = $em->createQueryBuilder()
                ->select('o')
                ->from($table,'o')
                ->where('o.id in (:document_ids)')
                ->setParameter('document_ids',$documents)
                ->getQuery()
                ->getResult();
        try{
            foreach($documentsToDelete as $row)
            {
              if($tray!="trash"){
                  $trashRecord=new Trash();
                  $trashRecord->setDocument($row->getDocument());
                  $trashRecord->setUser($row->getUser());
                  $trashRecord->setGeneratedDay($row->getGeneratedDay());
                  $trashRecord->setTray($tray);
                  $em->persist($trashRecord);
              }
              $em->remove($row);
            }
            $em->flush();
        } catch (\Doctrine\ORM\EntityNotFoundException $ex) {
              return new JsonResponse(array("event"=>"error", "msg"=>"Error al borrar... Por favor intente nuevamente.."));
        }

        return new JsonResponse(array("event"=>"success","msg"=>"Documentos eliminados correctamente","ids"=>$documents));
    }
    private function getTableBasedInTray($tray)
    {
        $table="";
        switch($tray)
        {
          case 'ellaboration':
          {
            $table="DocumentBundle\Entity\Document";
            break;
          }

          case 'sent_documents':
          {
            $table="DocumentBundle\Entity\DocumentSent";
            break;
          }

          case 'inbox':
          {
            $table="DocumentBundle\Entity\Inbox";
            break;
          }

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

          default: break;

        }
        return $table;
    }
    public function removeDocumentAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $documentId=$request->request->get('document_id');
        $tray=$request->request->get('tray');
        $table=$this->getTableBasedInTray($tray);

        $document = $em->createQueryBuilder()
                ->select('o')
                ->from($table,'o')
                ->where('o=:document_id')
                ->setParameter('document_id',$documentId)
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();

        try{
            $em->remove($document);
            $em->flush();
        } catch (\Doctrine\ORM\EntityNotFoundException $ex) {
              return new JsonResponse(array("event"=>"error", "msg"=>"Error al borrar... Por favor intente nuevamente.."));
        }

        return new JsonResponse(array("event"=>"success","msg"=>"Documento eliminado correctamente"));
    }
    public function showEllaborationAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $documents = $em->createQueryBuilder()
                ->select('o')
                ->from('DocumentBundle\Entity\Document','o')
                ->where('o.sent=0')
                ->getQuery()
                ->getResult();
        return $this->render('DocumentBundle:Document:ellaboration.html.twig',array("documents"=>$documents));
    }
    public function showTrayAction($tray)
    {
        $template="";
        $table="";
        switch($tray)
        {
          case 'inbox':{
            $template="inbox.html.twig";
            $table="DocumentBundle\Entity\Inbox";
            break;
          }
          case 'trash':{
            $template="trash.html.twig";
            $table="DocumentBundle\Entity\Trash";
            break;
          }
          case 'ellaboration':{
            $template="ellaboration_tray.html.twig";
            $table="DocumentBundle\Entity\Document";
            break;
          }
          case 'inform':{
            $template="inform_tray.html.twig";
            $table="DocumentBundle\Entity\Inform";
            break;
          }
          case 'archive':{
            $template="archive_tray.html.twig";
            $table="DocumentBundle\Entity\Archive";
            break;
          }
          default: break;
        }
        $em = $this->get('doctrine')->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $documents = $em->createQueryBuilder()
                ->select('o')
                ->from($table,'o')
                ->where('o.user=:user_id')
                ->setParameter('user_id',$user->getId())
                ->getQuery()
                ->getResult();

        return $this->render('DocumentBundle:Document:'.$template,array("documents"=>$documents));
    }
    public function showInboxAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $documents = $em->createQueryBuilder()
                ->select('o')
                ->from('DocumentBundle\Entity\Inbox','o')
                ->where('o.user=:user_id')
                ->setParameter('user_id',$user->getId())
                ->getQuery()
                ->getResult();
        return $this->render('DocumentBundle:Document:inbox.html.twig',array("documents"=>$documents));
    }

    public function showSentDocumentAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $documents = $em->createQueryBuilder()
                ->select('o')
                ->from('DocumentBundle\Entity\DocumentSent','o')
                ->where('o.user=:user_id')
                ->setParameter('user_id',$user->getId())
                ->getQuery()
                ->getResult();
        return $this->render('DocumentBundle:Document:document_sent.html.twig',array("documents"=>$documents));
    }

    public function getDocumentAction($documentId,$copyFrom,$respond)
    {
        $em = $this->get('doctrine')->getManager();
        $document=$em->getRepository('DocumentBundle:Document')->findOneBy(array('id' => $documentId));
        $copyFrom=$em->getRepository('DocumentBundle:Document')->findOneBy(array('id' => $copyFrom));
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if(empty($document))
        {
          $document= new Document();
          $document->setUser($user);
          if(!empty($copyFrom))
          {
             $document->setContent($copyFrom->getContent());
             $document->setSubject($copyFrom->getSubject());
             if($respond==1)$document->setResponseTo($copyFrom->getId());
          }
          $em->persist($document);
          $em->flush();
          return $this->redirectToRoute('get_document', array('documentId' => $document->getId()));
        }
        $from=array();
        $doc=null;
        $activeUsers=$em->createQueryBuilder()
                ->select('o')
                ->from('SiteConfigurationBundle\Entity\User','o')
                ->orderBy('o.lastname','desc')
                ->getQuery()
                ->getResult();
        return $this->render('DocumentBundle:Document:new.html.twig',array("document"=>$document,"active_users"=>$activeUsers,"from"=>$from,"doc"=>$doc));
    }

    public function showDocumentPreviewAction($documentId)
    {
        $em = $this->get('doctrine')->getManager();
        $snappy=$this->get('knp_snappy.pdf');
        $document=$em->getRepository('DocumentBundle:Document')->findOneBy(array('id' => $documentId));
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if(!empty($document))
        if($document->getUser()->getId()==$user->getId())
        {
            $final_file=$this->getPdfOutput($documentId);
            return new Response
            (
              $final_file,
              200,
              array(
                'Content-Type'=> 'application/pdf',
                'Content-Disposition'=>'inline;filename=temp.pdf'
              )
            );
        }
        return new Response("Usted no tiene privilegios para abrir este documento");
    }
    public function reasignDocumentAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $documentIds=$request->request->get('document_ids');
        $toReasign=$request->request->get('user_id');
        $tray=$request->request->get('tray');
        $action=$request->request->get('action');
        $toUser=$em->getRepository('SiteConfigurationBundle:User')->findOneBy(array('id' => $toReasign));
        $loggedUser = $this->get('security.token_storage')->getToken()->getUser();
        foreach($documentIds as $documentId){
          $document=null;
          if($tray=="inbox")
          {
              $inboxRecord=$em->getRepository('DocumentBundle:Inbox')->findOneBy(array('id' => $documentId));
              $historyRecord=new History();
              $historyRecord->setUser($loggedUser);
              $historyRecord->setDocument($document);
              $historyRecord->setActionDate(new \DateTime());
              switch($action){
                case "archive_btn":
                {
                  $archiveRecord=new Archive();
                  $archiveRecord->setUser($loggedUser);
                  $archiveRecord->setDocument($inboxRecord->getDocument());
                  $archiveRecord->setGeneratedDay(new \DateTime());
                  $em->persist($archiveRecord);
                  $em->remove($inboxRecord);
                  $historyRecord->setComment("Documento Archivado..");
                  break;
                }
                case "reasign_btn":
                {
                  $inboxRecord->setUser($toUser);
                  $em->persist($inboxRecord);
                  $historyRecord->setComment("Documento Reasignado a ".$toUser->getName()." ".$toUser->getLastName());
                  break;
                }
                case "inform_btn":
                {
                  $informRecord=new Inform();
                  $informRecord->setUser($toUser);
                  $informRecord->setDocument($inboxRecord->getDocument());
                  $informRecord->setGeneratedDay(new \DateTime());
                  $em->persist($informRecord);
                  $historyRecord->setComment("Se informa de documento a ".$toUser->getName()." ".$toUser->getLastName());
                  break;
                }
                default: break;
              }
              $em->persist($historyRecord);
          }
          else {
              $document=$em->getRepository('DocumentBundle:Document')->findOneBy(array('id' => $documentId));
              $document->setUser($toUser);
              $em->persist($document);
          }

        }
        $em->flush();
        return new JsonResponse(["event"=>"success","msg"=>"Procesado de manera Exitosa"]);
    }
    public function saveDocumentAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $owner=0;
        $from=$request->request->get('from');
        $to=$request->request->get('to');
        $copy=$request->request->get('copy');
        $content=$request->request->get('raw_content');
        $documentId=$request->request->get('document_id');
        $subject=$request->request->get('subject');
        $document=$em->getRepository('DocumentBundle:Document')->findOneBy(array('id' => $documentId));
        if(!empty($document))
        {
            try{
              $loggedUser = $this->get('security.token_storage')->getToken()->getUser();
              $loggedUserId=$loggedUser->getId();
              $document->setFromUser(serialize($from));
              $document->setToUser(serialize($to));
              $document->setCopyTo(serialize($copy));
              $document->setContent(htmlentities($content, ENT_QUOTES));
              $document->setSubject($subject);
              $em->persist($document);
              $em->flush();
              if($loggedUserId == (int)$from)$owner=1;
            } catch (\Doctrine\ORM\EntityNotFoundException $ex) {
                  return new JsonResponse(array("event"=>"error", "msg"=>"Error al guardar... Por favor intente nuevamente.."));
            }
        }
        else
        {
          return new JsonResponse(array("event"=>"error","msg"=>"Error al Guardar el Documento.."));
        }


        return new JsonResponse(array("event"=>"success","msg"=>"Documento Guardado exitosamente..","owner"=>$owner));
    }
}
