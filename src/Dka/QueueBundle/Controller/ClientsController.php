<?php

namespace Dka\QueueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientsController extends Controller
{
    /**
     * @Route("/clients", name="clients")
     * @Template()
     */
    public function indexAction()
    {
		$conn = $this->get('database_connection');
		$users = $conn->fetchAll("SELECT users.id, users.name, users.username, users.email, users.phone, users.business, user_types.title FROM users INNER JOIN user_types ON users.type=user_types.id WHERE user_types.title = 'Client'");
	
		return array('users'=>$users);
    }

	/**
     * @Route("/clients/add", name="add_client")
     * @Template()
     */
    public function AddAction(Request $request)
    {
		$login = array();
		$error = array();
		$types = array();
		$redirect = false;
		$conn = $this->get('database_connection');
		$states = array('AL', 'AK', 'AS', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'DC', 'FM', 'FL', 'GA', 'GU', 'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MH', 'MD', 'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ', 'NM', 'NY', 'NC', 'ND', 'MP', 'OH', 'OK', 'OR', 'PW', 'PA', 'PR', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VT', 'VI', 'VA', 'WA', 'WV', 'WI', 'WY', 'AE', 'AA', 'AP');

		$form = $this->createFormBuilder($login)
				 ->add('name', 'text')
				 ->add('business', 'text')
		         ->add('username', 'text')
				 ->add('password', 'password')
				 ->add('email', 'email')
				 ->add('phone', 'text', array(
					'required'=>false
				 ))
				 ->add('address1', 'text', array(
					'required'=>false
				 ))
				 ->add('address2', 'text', array(
					'required'=>false
				 ))
				 ->add('city', 'text', array(
					'required'=>false
				 ))
				 ->add('state', 'choice', array(
					'choices'=>$states,
					'required'=>false
				 ))
				 ->add('zip', 'text', array(
					'required'=>false
				 ))
				 ->add('Submit', 'submit')
		         ->getForm();

		$form->handleRequest($request);

		if($form->isValid()){
			$data = $form->getData();
			
			$password = md5($data['password']);
			
			$insert = array('name'=>$data['name'], 'username'=>$data['username'], 'email'=>$data['email'], 'type'=>6, 'password'=>$password, 'address1'=>$data['address1'], 'address2'=>$data['address2'], 'city'=>$data['city'], 'state'=>$data['state'], 'zip'=>$data['zip'], 'phone'=>$data['phone'], 'business'=>$data['business']);
			
			$conn->insert('users', $insert);
			
			$redirect = true;
		}
		
		if($redirect){
			return $this->redirect($this->generateUrl('clients'));
		}else{
			return array('form'=>$form->createView(), 'error'=>$error);
		}
    }
	

	/**
     * @Route("/clients/edit/{id}", name="edit_client")
     * @Template()
     */
	public function UpdateAction(Request $request, $id){
		$login = array();
		$error = array();
		$types = array();
		$redirect = false;
		$conn = $this->get('database_connection');
		
		$user = $conn->fetchAll("SELECT * FROM users WHERE `id`='$id'");
		$states = array('AL', 'AK', 'AS', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'DC', 'FM', 'FL', 'GA', 'GU', 'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MH', 'MD', 'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ', 'NM', 'NY', 'NC', 'ND', 'MP', 'OH', 'OK', 'OR', 'PW', 'PA', 'PR', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VT', 'VI', 'VA', 'WA', 'WV', 'WI', 'WY', 'AE', 'AA', 'AP');
		
		$form = $this->createFormBuilder($login)
				 ->add('name', 'text', array(
				 	'attr'=>array(
				 		'value'=>$user[0]['name']
				 	)
				 ))
				 ->add('business', 'text', array(
				 	'attr'=>array(
				 		'value'=>$user[0]['business']
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
				 ->add('phone', 'text', array(
				 	'attr'=>array(
				 		'value'=>$user[0]['phone']
					)
				 ))
				 ->add('address1', 'text', array(
				 	'attr'=>array(
				 		'value'=>$user[0]['address1']
					),
					'required'=>false
				 ))
				 ->add('address2', 'text', array(
				 	'attr'=>array(
				 		'value'=>$user[0]['address2']
					),
					'required'=>false
				 ))
				 ->add('city', 'text', array(
				 	'attr'=>array(
				 		'value'=>$user[0]['phone']
					),
					'required'=>false
				 ))
				 ->add('state', 'choice', array(
					'choices'=>$states,
					'data'=>$user[0]['state'],
					'required'=>false
				 ))
				 ->add('zip', 'text', array(
				 	'attr'=>array(
				 		'value'=>$user[0]['zip']
					),
					'required'=>false
				 ))
				 ->add('Submit', 'submit')
		         ->getForm();

		$form->handleRequest($request);

		if($form->isValid()){
			$data = $form->getData();
			
			$update = array('name'=>$data['name'], 'username'=>$data['username'], 'email'=>$data['email'], 'address1'=>$data['address1'], 'address2'=>$data['address2'], 'city'=>$data['city'], 'state'=>$data['state'], 'zip'=>$data['zip'], 'phone'=>$data['phone'], 'business'=>$data['business']);
			
			$conn->update('users', $update, array('id'=>$id));
			
			$redirect = true;
		}
		
		if($redirect){
			return $this->redirect($this->generateUrl('clients'));
		}else{
			return array('form'=>$form->createView(), 'error'=>$error);
		}
	}
	
	/**
     * @Route("/clients/delete/{id}", name="delete_client")
     * @Template()
     */
	public function DeleteAction(Request $request, $id){	
		$conn = $this->get('database_connection');
		$conn->delete('users', array('id'=>$id));
		
		return $this->redirect($this->generateUrl('users'));
	}
}