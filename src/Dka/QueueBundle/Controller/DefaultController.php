<?php

namespace Dka\QueueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home")
     * @Template()
     */
    public function indexAction()
    {
		$session = $this->getRequest()->getSession();
	
		if(!$session->get('name')){
			echo 'NO NAME!';
			
			return $this->redirect($this->generateUrl('login'));
		}else{
	        return array();
		}
    }
}
