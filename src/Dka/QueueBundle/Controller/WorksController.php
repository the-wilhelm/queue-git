<?php

namespace Dka\QueueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WorksController extends Controller
{
    /**
     * @Route("/works", name="work_types")
     * @Template()
     */
    public function indexAction()
    {
		$conn = $this->get('database_connection');
		//$works = $conn->fetchAll("SELECT work_types.title, work_steps.title step_title, user_types.title user_title, work_questions.question, work_questions.type FROM work_types RIGHT JOIN work_steps ON work_steps.workId = work_types.id LEFT JOIN user_types ON work_steps.typeId = user_types.id RIGHT JOIN work_questions ON work_questions.workId = work_types.id");
		$works = $conn->fetchAll("SELECT work_types.title, user_types.title FROM work_types INNER JOIN user_types ON work_types.typeId = user_types.id");
	
		return array('works'=>$works);
    }

	/**
     * @Route("/works/add", name="add_work")
     * @Template()
     */
    public function AddAction(Request $request)
    {
		$login = array();
		$error = array();
		
		$redirect = false;
		$conn = $this->get('database_connection');
		
		$questions = $conn->fetchAll("SELECT * FROM work_questions");
		$choices = array();
		
		for ($i=0; $i < count($questions); $i++) { 
			$choices[] = $questions[$i];
		}

		$form = $this->createFormBuilder($login)
					->add('title', 'text')
					->add('questions', 'choice', array(
						'choices'=>$questions,
						'multiple'=>true
					 ))
					->add('Submit', 'submit')
					->getForm();

		$form->handleRequest($request);

		if($form->isValid()){
			$data = $form->getData();
			
			$insert = array('title'=>$data['title']);
			
			print_r($data);
			
			$conn->insert('work_types', $insert);
			
			$redirect = true;
		}
		
		if($redirect){
			return $this->redirect($this->generateUrl('works'));
		}else{
			return array('form'=>$form->createView(), 'error'=>$error);
		}
    }
	

	/**
     * @Route("/works/edit/{id}", name="edit_work")
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
     * @Route("/works/delete/{id}", name="delete_work")
     * @Template()
     */
	public function DeleteAction(Request $request, $id){	
		$conn = $this->get('database_connection');
		$conn->delete('work_type', array('id'=>$id));
		$conn->delete('work_steps', array('workId'=>$id));
		$conn->delete('work_questions', array('workId'=>$id));
		
		return $this->redirect($this->generateUrl('queues'));
	}
}