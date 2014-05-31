<?php

namespace Dka\QueueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class QueuesController extends Controller
{
    /**
     * @Route("/queues", name="queues")
     * @Template()
     */
    public function indexAction()
    {
		$conn = $this->get('database_connection');
		$queues = $conn->fetchAll("SELECT * FROM queues");
	
		return array('queues'=>$queues);
    }

	/**
     * @Route("/queues/add", name="add_queue")
     * @Template()
     */
    public function AddAction(Request $request)
    {
		$login = array();
		$error = array();
		
		$redirect = false;
		$conn = $this->get('database_connection');

		$form = $this->createFormBuilder($login)
				 ->add('title', 'text')
				 ->add('Submit', 'submit')
		         ->getForm();

		$form->handleRequest($request);

		if($form->isValid()){
			$data = $form->getData();
			
			$insert = array('title'=>$data['title']);
			
			$conn->insert('queues', $insert);
			
			$redirect = true;
		}
		
		if($redirect){
			return $this->redirect($this->generateUrl('queues'));
		}else{
			return array('form'=>$form->createView(), 'error'=>$error);
		}
    }
	

	/**
     * @Route("/queues/edit/{id}", name="edit_queue")
     * @Template()
     */
	public function UpdateAction(Request $request, $id){
		$login = array();
		$error = array();
		$redirect = false;
		$conn = $this->get('database_connection');
		
		$queue = $conn->fetchAll("SELECT * FROM queues WHERE `id`='$id'");

		$form = $this->createFormBuilder($login)
				 ->add('title', 'text', array(
				 	'attr'=>array(
				 		'value'=>$queue[0]['title']
				 	)
				 ))
				 ->add('Submit', 'submit')
		         ->getForm();

		$form->handleRequest($request);

		if($form->isValid()){
			$data = $form->getData();
			
			$update = array('title'=>$data['title']);
			
			$conn->update('queues', $update, array('id'=>$id));
			
			$redirect = true;
		}
		
		if($redirect){
			return $this->redirect($this->generateUrl('queues'));
		}else{
			return array('form'=>$form->createView(), 'error'=>$error);
		}
	}
	
	/**
     * @Route("/queues/delete/{id}", name="delete_queue")
     * @Template()
     */
	public function DeleteAction(Request $request, $id){	
		$conn = $this->get('database_connection');
		$conn->delete('queues', array('id'=>$id));
		
		return $this->redirect($this->generateUrl('queues'));
	}
}