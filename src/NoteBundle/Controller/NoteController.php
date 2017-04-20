<?php

namespace NoteBundle\Controller;

use NoteBundle\Entity\Note;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Note controller.
 *
 */
class NoteController extends Controller
{
    /**
     * Lists all note entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $notes = $em->getRepository('NoteBundle:Note')->findBy(array(), array('sequence' => 'DESC'));
        

        return $this->render('note/index.html.twig', array(
            'notes' => $notes,
        ));
    }

    /**
     * Creates a new note entity.
     *
     */
    public function newAction(Request $request)
    {
        $note = new Note();
        $form = $this->createForm('NoteBundle\Form\NoteType', $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($note);
            $em->flush($note);

            return $this->redirectToRoute('_show', array('id' => $note->getId()));
        }

        return $this->render('note/new.html.twig', array(
            'note' => $note,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a note entity.
     *
     */
    public function showAction(Note $note)
    {
        $deleteForm = $this->createDeleteForm($note);

        return $this->render('note/show.html.twig', array(
            'note' => $note,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing note entity.
     *
     */
    public function editAction(Request $request, Note $note)
    {
        $deleteForm = $this->createDeleteForm($note);
        $editForm = $this->createForm('NoteBundle\Form\NoteType', $note);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('_edit', array('id' => $note->getId()));
        }

        return $this->render('note/edit.html.twig', array(
            'note' => $note,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a note entity.
     *
     */
    public function deleteAction(Request $request, Note $note)
    {
        $form = $this->createDeleteForm($note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($note);
            $em->flush($note);
        }

        return $this->redirectToRoute('_index');
    }

    /**
     * Creates a form to delete a note entity.
     *
     * @param Note $note The note entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Note $note)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('_delete', array('id' => $note->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    public function ajaxAjoutAction(Request $request) {
        // Le $_POST est dans $request->request
        $post = $request->request->all();
        $em = $this->getDoctrine()->getManager();

        // On a besoin d'un objet Note
        $note = new Note();
        $note->setTitle($post['title']);
        $note->setContent($post['content']);
        $note->setDate(new \DateTime());

        $listeNotes = $em->getRepository('NoteBundle:Note')->findAll();
        $newSequence = count($listeNotes);
        $note->setSequence($newSequence+1);
        $note->setUrgency(FALSE);

        $em->persist($note);
        $em->flush();

        return new Response('{
            "id" : "'.$note->getId().'",
            "date" : "'.$note->getDate()->format('Y-m-d H:i:s').'"
        }');
    }

    public function ajaxUpdateAction(Request $request) {
        $post = $request->request->all();

        $em = $this->getDoctrine()->getManager();

        $note = $em->getRepository('NoteBundle:Note')->find($post['id']);

        $note->setTitle($post['title']);
        $note->setContent($post['content']);
        $note->setDate(new \DateTime());

        $em->persist($note);
        $em->flush();
        return new Response('{
            "date" : "'.$note->getDate()->format('Y-m-d H:i:s').'"
        }');           
    }

    public function ajaxSupprAction(Request $request) {
        $post = $request->request->all();

        $em = $this->getDoctrine()->getManager();

        $note = $em->getRepository('NoteBundle:Note')->find($post['id']);
        $em->remove($note);
        $em->flush();

        return new Response('ok');
    }
    
    public function ajaxResequenceAction(Request $request) {
        $post = $request->request->all();
        $this->reorganiseSequence($post['depart'], $post['arrive']);   
    
        return new Response('ok');
    }
    
    
    

    private function reorganiseSequence($depart, $arrive) {

        $em = $this->getDoctrine()->getManager();

        if($depart > $arrive) {
            for($i=$arrive; $i<$depart;$i++) {
                $notes = $em->getRepository('NoteBundle:Note')->findBy(array("sequence" => $i), array('sequence' => 'ASC'));
                $note = $notes[0];
                $note->setSequence($i+1);
                $em->persist($note);
            }
        }
        else {
            for($i=$arrive; $i>$depart;$i=$i-1) {
                $notes = $em->getRepository('NoteBundle:Note')->findBy(array("sequence" => $i), array('sequence' => 'ASC'));
                $note = $notes[0];
                $note->setSequence($i-1);
                $em->persist($note);
            }
        }
        $notes = $em->getRepository('NoteBundle:Note')->findBy(array("sequence" => $depart), array('sequence' => 'ASC'));
        $note = $notes[0];
        $note->setSequence($arrive);
        $em->persist($note);
        $em->flush();
    }
}
