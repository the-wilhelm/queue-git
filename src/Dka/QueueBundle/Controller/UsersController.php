<?php

namespace Dka\QueueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UsersController extends Controller
{
    /**
     * @Route("/users", name="users")
     * @Template()
     */
    public function indexAction()
    {
		$conn = $this->get('database_connection');
		$users = $conn->fetchAll("SELECT users.id, users.name, users.username, users.email, user_types.title FROM users INNER JOIN user_types ON users.type=user_types.id");
	
		return array('users'=>$users);
    }

	/**
     * @Route("/users/add", name="add_user")
     * @Template()
     */
    public function AddAction(Request $request)
    {
		$login = array();
		$error = array();
		$types = array();
		$redirect = false;
		$conn = $this->get('database_connection');
		
		$user_types = $conn->fetchAll("SELECT title FROM user_types");
		
		for ($i=0; $i < count($user_types); $i++) { 
			$types[] = $user_types[$i]['title'];
		}

		$form = $this->createFormBuilder($login)
				 ->add('name', 'text')
		         ->add('username', 'text')
				 ->add('password', 'password')
				 ->add('email', 'email')
				 ->add('type', 'choice', array(
					'choices'=>$types
				 ))
				 ->add('Submit', 'submit')
		         ->getForm();

		$form->handleRequest($request);

		if($form->isValid()){
			$data = $form->getData();
			
			$password = md5($data['password']);
			$type = ($data['type']+1);
			
			$insert = array('name'=>$data['name'], 'username'=>$data['username'], 'email'=>$data['email'], 'type'=>$type, 'password'=>$password);
			
			$conn->insert('users', $insert);
			
			$redirect = true;
		}
		
		if($redirect){
			return $this->redirect($this->generateUrl('users'));
		}else{
			return array('form'=>$form->createView(), 'error'=>$error);
		}
    }
	

	/**
     * @Route("/users/edit/{id}", name="edit_user")
     * @Template()
     */
	public function UpdateAction(Request $request, $id){
		$login = array();
		$error = array();
		$types = array();
		$redirect = false;
		$conn = $this->get('database_connection');
		
		$user = $conn->fetchAll("SELECT * FROM users WHERE `id`='$id'");
		$user_types = $conn->fetchAll("SELECT title FROM user_types");
		$chose = ($user[0]['type']-1);
		
		for ($i=0; $i < count($user_types); $i++) { 
			$types[] = $user_types[$i]['title'];
		}

		$form = $this->createFormBuilder($login)
				 ->add('name', 'text', array(
				 	'attr'=>array(
				 		'value'=>$user[0]['name']
				 	)
				 ))
		         ->add('username', 'text', array(
				 	'attr'=>array(
				 		'value'=>$user[0]['username']
					)
				 ))
				 ->add('email', 'email', array(
				 	'attr'=>array(
				 		'value'=>$user[0]['email']
					)
				 ))
				 ->add('type', 'choice', array(
					'choices'=>$types,
					'data'=>$chose
				 ))
				 ->add('Submit', 'submit')
		         ->getForm();

		$form->handleRequest($request);

		if($form->isValid()){
			$data = $form->getData();
			$type = ($data['type']+1);
			
			$update = array('name'=>$data['name'], 'username'=>$data['username'], 'email'=>$data['email'], 'type'=>$type);
			
			$conn->update('users', $update, array('id'=>$id));
			
			$redirect = true;
		}
		
		if($redirect){
			return $this->redirect($this->generateUrl('users'));
		}else{
			return array('form'=>$form->createView(), 'error'=>$error);
		}
	}
	
	/**
     * @Route("/users/delete/{id}", name="delete_user")
     * @Template()
     */
	public function DeleteAction(Request $request, $id){	
		$conn = $this->get('database_connection');
		$conn->delete('users', array('id'=>$id));
		
		return $this->redirect($this->generateUrl('users'));
	}
}