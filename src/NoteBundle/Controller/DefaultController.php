<?php

namespace NoteBundle\Controller;

use NoteBundle\Entity\Note;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('NoteBundle:Default:index.html.twig');
    }

    public function listeAction()
    {
    	$em = $this->getDoctrine()->getManager();

    	$notes = $em->getRepository('NoteBundle:Note')->findAll();

    	$uneNote = $em->getRepository('NoteBundle:Note')->find(5);

        return $this->render('liste_notes.html.twig',array(
        	'entete' => 'Bienvenue sur Toujoursnote',
        	'notes' => $notes,
        	'uneNote' => $uneNote
        	));
    }

    public function maNoteAction($id1, $id2)
    {
    	$em = $this->getDoctrine()->getManager();
    	$note1 = $em->getRepository('NoteBundle:Note')->find($id1);
    	$note2 = $em->getRepository('NoteBundle:Note')->find($id2);

    		return $this->render('ma_note.html.twig', array(
    			'note1' => $note1,
    			'note2' => $note2
    			));
    }

}
