<?php
namespace Mosaika\RadBundle\Model\Controller;


use Mosaika\RadBundle\Model\RadController;
use Mosaika\RadBundle\Model\RadEntity;
use Mosaika\RadBundle\Model\RadControllerAction;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Mosaika\RadBundle\Model\Controller\Action\SaveActionConfig;
use Mosaika\RadBundle\Model\Controller\Action\ListActionConfig;
use Nette\PhpGenerator\Method;
use Symfony\Component\HttpFoundation\Request;

class RestController extends RadController{
	/**
	 * @var string json|html
	 */
    protected $format = "html";
	/**
	 * 
	 * @var RadEntity
	 */
	protected $entity;
	
	/**
	 * @var Container
	 */
	protected $container;
	
	public function __construct($name, $namespace, $bundle){
		parent::__construct($name, $namespace, $bundle);
		$this->addUse("FOS\\RestBundle\\Controller\\FOSRestController");
		$this->addUse("FOS\\RestBundle\\View\\View");
		$this->addUse("FOS\\RestBundle\\Controller\\Annotations","Rest");
		$this->addUse("Symfony\\Component\\HttpFoundation\\Response");
		$this->setExtends("FOS\\RestBundle\\Controller\\FOSRestController");
	}
	
    /**
     * @return \Mosaika\RadBundle\Model\RadEntity
     */
	public function getEntity() {
		return $this->entity;
	}
	
	public function getMethods(){
		$methods = parent::getMethods();
		$onFill = new Method("_onFill" . $this->entity->getName());
		$onFill->addParameter("item")->setTypeHint($this->entity->getFullClass());
		$onFill->addParameter("input")->setTypeHint("array");
		$onFill->addParameter("request")->setTypeHint(Request::class);
		$methods[] = $onFill;
		return $methods;
	}
	
	
	/**
	 * @param string $name Action name
	 * @param string $repositoryQuery Method to use for this action
	 * @return \Mosaika\RadBundle\Model\Controller\RestController
	 */
	public function addListAction($name="restList", $config=null){
		if(!$config){
			$config = ListActionConfig::create();
		}
		
		/**
		 * @var EngineInterface $twig
		 */
		$twig = $this->container->get("templating");
		$body = $twig->render("MosaikaRadBundle::templates/controller/rest/list.php.twig", array(
				"config" => $config,
				"format" => $this->format,
				"entity" => $this->entity
		));
		$method = new Method("_onListRest");
		$method->addComment("@param " . $this->entity->getFullClass() . "[] \$items");
		$method->addComment("@return " . $this->entity->getFullClass() . "[]");
		$method->addParameter("items");
		$method->setBody("return \$items;");
		$this->methods[] = $method;
		
		$this->actions[] = $action = RadControllerAction::create($name,$this,"");
		$action
		->setAddRoute(false)
		->addAnnotation(sprintf('@Rest\Get("%s")',$action->getFullUrl()))
		->setBody($body)
		;
		return $this;
	}
	/**
	 * @param string $name Action name
	 * @param string $repositoryQuery Method to use for this action
	 * @return \Mosaika\RadBundle\Model\Controller\RestController
	 */
	public function addGetAction($name="restGet", $config=null){
		if(!$config){
			//			$config = ListActionConfig::create();
		}
		
		/**
		 * @var EngineInterface $twig
		 */
		$twig = $this->container->get("templating");
		$body = $twig->render("MosaikaRadBundle::templates/controller/rest/get.php.twig", array(
				"config" => $config,
				"format" => $this->format,
				"entity" => $this->entity
		));
		
		$method = new Method("_onGetRest");
		$method->addComment("@param " . $this->entity->getFullClass() . " \$item");
		$method->addComment("@return " . $this->entity->getFullClass());
		$method->addParameter("item")->setTypeHint($this->entity->getFullClass());
		//$method->setReturnType($this->entity->getFullClass());
		$method->setBody("return \$item;");
		$this->methods[] = $method;
		
		$this->actions[] = $action = RadControllerAction::create($name,$this,"");
		$action
		->setAddRoute(false)
		->setUrl("/{id}")
		->addArgument("id")
		->addAnnotation(sprintf('@Rest\Get("%s")',$action->getFullUrl()))
		->setBody($body)
		;
		return $this;
	}
	/**
	 * Add an action for insert new Entity
	 * @param string $name Action name
	 * @return \Mosaika\RadBundle\Model\Controller\RestController
	 */
	public function addPostAction($name="restPost", $config=null){
		if(!$config){
			$config = SaveActionConfig::create();
		}
		
		/**
		 * @var EngineInterface $twig
		 */
		$twig = $this->container->get("templating");
		$body = $twig->render("MosaikaRadBundle::templates/controller/rest/post.php.twig", array(
				"config" => $config,
				"format" => $this->format,
				"entity" => $this->entity
		));
		
		$this->actions[] = $action = RadControllerAction::create($name,$this,"");
		$action
		->setAddRoute(false)
		->addAnnotation(sprintf('@Rest\Post("%s")',$action->getFullUrl()))
		->setBody($body)
		;
		return $this;
	}
	/**
	 * Add an action for insert new Entity
	 * @param string $name Action name
	 * @return \Mosaika\RadBundle\Model\Controller\RestController
	 */
	public function addPutAction($name="restPut", $config=null){
		if(!$config){
			$config = SaveActionConfig::create();
		}
		
		/**
		 * @var EngineInterface $twig
		 */
		$twig = $this->container->get("templating");
		$body = $twig->render("MosaikaRadBundle::templates/controller/rest/put.php.twig", array(
				"config" => $config,
				"format" => $this->format,
				"entity" => $this->entity
		));
		
		$this->actions[] = $action = RadControllerAction::create($name,$this,"");
		$action
		->setAddRoute(false)
		->setUrl("/{id}")
		->addArgument("id")
		->addAnnotation(sprintf('@Rest\Put("%s")',$action->getFullUrl()))
		->setBody($body)
		;
		return $this;
	}
	
    /**
     * @return \Mosaika\RadBundle\Model\Controller\CrudController
     */
	public function setContainer($c) {
		$this->container = $c;
		return $this;
	}
	
	/**
	 * 
	 * @param RadEntity $entity
     * @return \Mosaika\RadBundle\Model\Controller\CrudController
	 */
	public function setEntity(RadEntity $entity) {
		$this->entity = $entity;
		$this->setName($entity->getName());
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getFormat() {
		return $this->format;
	}

	/**
	 * @param string $format
	 * @return CrudController
	 */
	public function setFormat($format) {
		$this->format = $format;
		return $this;
	}
	
}

