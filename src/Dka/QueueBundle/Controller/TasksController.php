<?php

namespace Dka\QueueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class TasksController extends Controller
{
    /**
     * @Route("/tasks", name="tasks")
     * @Template()
     */
    public function indexAction()
    {
		$conn = $this->get('database_connection');
		$entries = $conn->fetchAll("SELECT entries.id, entries.created_at, entries.title, work_types.title work_title, users.name, queues.title queue_title FROM entries LEFT JOIN work_types ON entries.workId = work_types.id LEFT JOIN users ON entries.creatorId = users.id LEFT JOIN queues ON entries.queueId = queues.id");
		
		return array('tasks'=>$entries);
    }

	/**
     * @Route("/tasks/add", name="add_task")
     * @Template()
     */
    public function AddAction(Request $request)
    {
			$login = array();
			$error = array();
			$works = array();
			$queues = array();

			$redirect = false;
			$conn = $this->get('database_connection');

			$work_types = $conn->fetchAll("SELECT id, title FROM work_types");
			for ($i=0; $i < count($work_types); $i++) { 
				$works[$work_types[$i]['id']] = $work_types[$i]['title'];
			}

			$queues_arr = $conn->fetchAll("SELECT id, title FROM queues");
			for ($i=0; $i < count($queues_arr); $i++) { 
				$queues[$queues_arr[$i]['id']] = $queues_arr[$i]['title'];
			}

			$form = $this->createFormBuilder($login)
					 ->add('title', 'text')
					 ->add('queues', 'choice', array(
						'choices'=>$queues
					 ))
					 ->add('work_types', 'choice', array(
						'choices'=>$works
					 ))
					 ->add('Submit', 'submit')
			         ->getForm();

			$form->handleRequest($request);

			if($form->isValid()){
				$data = $form->getData();
				$work_id = $data['work_types'];
				$queueId = $data['queues'];
				$created_at = date("Y-m-d H:i:s");
				
				$session = $this->getRequest()->getSession();
				
				$creatorId = $session->get('user_id');

				$insert = array('title'=>$data['title'], 'queueId'=>$queueId, 'workId'=>$work_id, 'created_at'=>$created_at, 'creatorId'=>$creatorId, 'current_step'=>0);

				$conn->insert('entries', $insert);
				$task_id = $conn->lastInsertId();

				$redirect = true;
			}

			if($redirect){
				return $this->redirect($this->generateUrl('add_questions', array('work_id'=>$work_id, 'task_id'=>$task_id)));
			}else{
				return array('form'=>$form->createView(), 'error'=>$error);
			}
    }
	
	/**
     * @Route("/tasks/add/{work_id}/{task_id}", name="add_questions")
     * @Template()
     */
    public function QuestionsAction(Request $request, $work_id, $task_id)
    {
		$login = array();
		$error = array();
		$works = array();
		$queues = array();
		
		$redirect = false;
		$conn = $this->get('database_connection');
		
		$questions = $conn->fetchAll("SELECT * FROM work_questions WHERE `workId`='$work_id'");

		$form = $this->createFormBuilder($login);
		
		for ($i=0; $i < count($questions); $i++) {
			switch($questions[$i]['type']){
				case 0:
					$form->add($questions[$i]['id'], 'text', array(
						'label'=>$questions[$i]['question']
					));
					
					break;
				
				case 1:
					$form->add($questions[$i]['id'], 'textarea', array(
						'label'=>$questions[$i]['question']
					));

					break;
					
				case 2:
					$form->add($questions[$i]['id'], 'choice', array(
						'choices'=>array(
							0=>'Yes',
							1=>'No'
						),
						'label'=>$questions[$i]['question']
					));

					break;
					
				case 3:
					$choices = array();
					
					$clients = $conn->fetchAll("SELECT id, business FROM users WHERE `type`='6'");
					
					for ($j=0; $j < count($clients); $j++) { 
						$choices[$clients[$j]['id']] = $clients[$j]['business'];
					}
					
					$form->add($questions[$i]['id'], 'choice', array(
						'choices'=>$choices,
						'label'=>$questions[$i]['question']
					));

					break;
			}
		}
				 
		$form->add('Submit', 'submit');
		$form = $form->getForm();

		$form->handleRequest($request);

		if($form->isValid()){
			$data = $form->getData();
			
			foreach ($data as $key => $value) {
				$insert = array('entryId'=>$task_id, 'questionId'=>$key, 'answer'=>$value);
				
				$conn->insert('questions_assoc', $insert);
			}
			
			$redirect = true;
		}
		
		if($redirect){
			return $this->redirect($this->generateUrl('tasks'));
		}else{
			return array('form'=>$form->createView(), 'error'=>$error);
		}
    }
	
	/**
     * @Route("/tasks/edit/{id}", name="edit_task")
     * @Template()
     */
	public function UpdateAction(Request $request, $id){
		$login = array();
		$error = array();
		$works = array();
		$queues = array();

		$redirect = false;
		$conn = $this->get('database_connection');
		
		$tasks = $conn->fetchAll("SELECT * FROM entries WHERE `id`='$id'");
		
		$work_types = $conn->fetchAll("SELECT id, title FROM work_types");
		for ($i=0; $i < count($work_types); $i++) { 
			$works[$work_types[$i]['id']] = $work_types[$i]['title'];
		}

		$queues_arr = $conn->fetchAll("SELECT id, title FROM queues");
		for ($i=0; $i < count($queues_arr); $i++) { 
			$queues[$queues_arr[$i]['id']] = $queues_arr[$i]['title'];
		}

		$form = $this->createFormBuilder($login)
				 ->add('title', 'text', array(
				 	'attr'=>array(
				 		'value'=>$tasks[0]['title']
				 	)
				 ))
				 ->add('queues', 'choice', array(
					'choices'=>$queues,
					'data'=>$tasks[0]['queueId']
				 ))
				 ->add('work_types', 'choice', array(
					'choices'=>$works,
					'data'=>$tasks[0]['workId']
				 ))
				 ->add('Submit', 'submit')
		         ->getForm();

		$form->handleRequest($request);

		if($form->isValid()){
			$data = $form->getData();
			$work_id = $data['work_types'];
			$queueId = $data['queues'];
			
			$session = $this->getRequest()->getSession();

			$update = array('title'=>$data['title'], 'queueId'=>$queueId, 'workId'=>$work_id);

			$conn->update('entries', $update, array('id'=>$id));
			$task_id = $conn->lastInsertId();

			$redirect = true;
		}

		if($redirect){
			return $this->redirect($this->generateUrl('edit_questions', array('work_id'=>$work_id, 'task_id'=>$task_id)));
		}else{
			return array('form'=>$form->createView(), 'error'=>$error);
		}
	}
	
	/**
     * @Route("/tasks/edit/{work_id}/{task_id}", name="edit_questions")
     * @Template()
     */
    public function EditQuestionsAction(Request $request, $work_id, $task_id)
    {
		$login = array();
		$error = array();
		$works = array();
		$queues = array();
		
		$redirect = false;
		$conn = $this->get('database_connection');
		
		$questions = $conn->fetchAll("SELECT work_questions.id, work_questions.workId, work_questions.question, work_questions.type, questions_assoc.answer, questions_assoc.id question_id FROM work_questions LEFT JOIN questions_assoc ON questions_assoc.questionId = work_questions.id AND questions_assoc.entryId='$task_id' WHERE `workId`='$work_id'");

		$form = $this->createFormBuilder($login);
		
		for ($i=0; $i < count($questions); $i++) {
			switch($questions[$i]['type']){
				case 0:
					$form->add($questions[$i]['question_id'], 'text', array(
						'label'=>$questions[$i]['question'],
						'attr'=>array(
							'value'=>$questions[$i]['answer']
						)
					));
					
					break;
				
				case 1:
					$form->add($questions[$i]['question_id'], 'textarea', array(
						'label'=>$questions[$i]['question'],
						'attr'=>array(
							'value'=>$questions[$i]['answer']
						)
					));

					break;
					
				case 2:
					$form->add($questions[$i]['question_id'], 'choice', array(
						'choices'=>array(
							0=>'Yes',
							1=>'No'
						),
						'data'=>$questions[$i]['answer'],
						'label'=>$questions[$i]['question']
					));

					break;
					
				case 3:
					$choices = array();
					
					$clients = $conn->fetchAll("SELECT id, business FROM users WHERE `type`='6'");
					
					for ($j=0; $j < count($clients); $j++) { 
						$choices[$clients[$j]['id']] = $clients[$j]['business'];
					}
					
					$form->add($questions[$i]['question_id'], 'choice', array(
						'choices'=>$choices,
						'data'=>$questions[$i]['answer'],
						'label'=>$questions[$i]['question']
					));

					break;
			}
		}
				 
		$form->add('Submit', 'submit');
		$form = $form->getForm();

		$form->handleRequest($request);

		if($form->isValid()){
			$data = $form->getData();
			
			foreach ($data as $key => $value) {
				$update = array('answer'=>$value);
				
				$conn->update('questions_assoc', $update, array('id'=>$key));
			}
			$task_id = $conn->lastInsertId();
			
			$redirect = true;
		}
		
		if($redirect){
			return $this->redirect($this->generateUrl('tasks'));
		}else{
			return array('form'=>$form->createView(), 'error'=>$error);
		}
    }
	
	/**
     * @Route("/tasks/delete/{id}", name="delete_task")
     * @Template()
     */
	public function DeleteAction(Request $request, $id){	
		$conn = $this->get('database_connection');
		$conn->delete('queues', array('id'=>$id));
		
		return $this->redirect($this->generateUrl('queues'));
	}
}