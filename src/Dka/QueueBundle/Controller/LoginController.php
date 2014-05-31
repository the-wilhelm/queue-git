<?php

namespace Dka\QueueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class LoginController extends Controller
{
    /**
     * @Route("/login", name="login")
     * @Template()
     */
    public function indexAction(Request $request)
	{
		$login = array();
		$error = array();
		$redirect = false;

		$form = $this->createFormBuilder($login)
		         ->add('username', 'text')
				 ->add('password', 'password')
				 ->add('Submit', 'submit')
		         ->getForm();

		$form->handleRequest($request);

		if($form->isValid()){
			$user = $form->getData();
			$username = $user['username'];
			$password = $user['password'];
			
			$conn = $this->get('database_connection');
			$cred = $conn->fetchAll("SELECT * FROM users WHERE `username`='$username'");
			
			if(md5($password) == $cred[0]['password']){
				$session = $this->getRequest()->getSession();
				
				$session->set('username', $username);
				$session->set('name', $cred[0]['name']);
				$session->set('user_id', $cred[0]['id']);
				$session->set('user_type', $cred[0]['type']);
				$session->set('user_email', $cred[0]['email']);
				
				$redirect = true;
			}
		}
		
		if($redirect){
			return $this->redirect($this->generateUrl('home'));
		}else{
			return array('form'=>$form->createView(), 'error'=>$error);
		}
    }

	/**
     * @Route("/logout", name="logout")
     * @Template()
     */
    public function LogoutAction(Request $request)
	{
		$session = $this->getRequest()->getSession();
		
		if($session->get('name')){
			$session->clear();
			
			$this->redirect($this->generateUrl('login'));
		}
	}
}
