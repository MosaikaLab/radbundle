<?php
namespace Mosaika\RadBundle\Core\Generator;

use Symfony\Component\Yaml\Yaml;
use Mosaika\RadBundle\Model\RadEntity;
use Mosaika\RadBundle\Model\RadController;
use Mosaika\RadBundle\Model\RadEntityRepository;
use Symfony\Component\DependencyInjection\Container;
use Mosaika\RadBundle\Model\Controller\RestController;
use Mosaika\RadBundle\Model\RadApiInput;

class RadGenerator{
    protected $container;

    protected $entities;
    
    protected $controllers;
    
    protected $javascriptClients;
    
    protected $javascriptClientsDirectory;
    
    /**
     * @var RadEntityRepository[]
     */
    protected $repositories;

    protected $services;
    
    protected $cruds;
    
    protected $serviceTypes;
    
    protected $tablePrefix = "";
    
	protected $bundle;
	
	protected $apiInputs;
    
    
    public function __construct($container){
        $this->container = $container;
        $this->entities = [];
        $this->controllers = [];
        $this->repositories = [];
        $this->javascriptClients = [];
        $this->apiInputs = [];
    }
    /**
     * 
     * @return \Mosaika\RadBundle\Core\Generator\RadGenerator
     */
    public function setBundle($bundle){
        $this->bundle = $bundle;
        return $this;
    }
    /**
     * Add controller to generator
     * @param RadController $e
     * @param string $key 
     * @return \Mosaika\RadBundle\Core\Generator\RadGenerator
     */
    public function addController($controller,$key){
	    	if(!$controller->getBundle()){
	    		$controller->setBundle($this->bundle);
	    	}
        $this->controllers[$key] = $controller;
        return $this;
    }

    /**
     * Add entity to generator
     * @param RadEntity $entity
     * @param string $key 
     * @return \Mosaika\RadBundle\Core\Generator\RadGenerator
     */
    public function addEntity($entity,$key){
   	 	if(!$entity->getBundle()){
			$entity->setBundle($this->bundle);
		}
		$this->entities[$key] = $entity;
		if($this->tablePrefix){
			$entity->setTablePrefix($this->tablePrefix);
			$entity->setTableName($this->tablePrefix . $entity->getTableName());
		}
		return $this;
	}
	
	/**
	 * Undocumented function
	 *
	 * @param string $slug
	 * @return RadEntity
	 */
	public function getEntity($slug){
		return $this->entities[$slug];
	}
    
    public function tableName($name){
        return $this->tablePrefix . $name;
    }
    
    public function commit(){
    		$this->_commit(RadEntityGenerator::get($this->container), $this->entities);
    		$this->_commit(RadFormGenerator::get($this->container), $this->entities);
    		$this->_commit(RadSerializerGenerator::get($this->container), $this->entities);
    		$this->_commit(RadControllerGenerator::get($this->container), $this->controllers);
    		$this->_commit(RadApiInputGenerator::get($this->container), $this->apiInputs);
	   	$this->_commit(RadEntityRepositoryGenerator::get($this->container), $this->repositories);
    }
    
    public function _commit($generator, $collection){
		foreach($collection as $c){
			$generator->setBundle($c->getBundle());
echo PHP_EOL . PHP_EOL . "Committing " . get_class($generator) . PHP_EOL;
            $generator->commit($c);
        }
    }
    
	public function getTablePrefix() {
		return $this->tablePrefix;
	}
	
	public function setTablePrefix($tablePrefix) {
		$this->tablePrefix = $tablePrefix;
		return $this;
	}
	/**
	 * @return multitype:\Mosaika\RadBundle\Model\RadEntityRepository 
	 */
	public function getRepositories() {
		return $this->repositories;
	}
	
	/**
	 * @param \Mosaika\RadBundle\Model\RadEntityRepository  $repository
	 * @param string $key
	 * @return RadGenerator
	 */
	public function addRepository($repository, $key) {
		if(!$repository->getBundle()){
			$repository->setBundle($this->bundle);
		}
		$this->repositories[$key] = $repository;
		return $this;
	}
	/**
	 * @param RestController $restController
	 * @param string $key
	 * @return RadGenerator
	 */
	public function addJavascriptClient($restController, $key) {
		$this->javascriptClients[$key] = $restController;
		return $this;
	}
	/**
	 * @return mixed
	 */
	public function getJavascriptClientsDirectory() {
		return $this->javascriptClientsDirectory;
	}

	/**
	 * @param mixed $javascriptClientsDirectory
	 * @return RadGenerator
	 */
	public function setJavascriptClientsDirectory($javascriptClientsDirectory) {
		$this->javascriptClientsDirectory = $javascriptClientsDirectory;
		return $this;
	}
	/**
	 * 
	 * @return Container
	 */
	public function getContainer(){
		return $this->container;
	}

	public function addApiInputFromYaml($filename, $name=null){
		$data = Yaml::parse(file_get_contents($filename));
		$name = $name ? $name : str_replace(array(".yaml",".yml"),"",basename($filename));
		foreach($data as $method => $config){
			$className = ucfirst($name) . ucfirst($method) . "ApiInput";
			$i = new RadApiInput($className,"App\Request\Api",$this->bundle);
			$i
			->setApiName($name)
			->setApiMethod($method)
			->setConfig($config);

			$this->apiInputs[] = $i;
		}
	}
}

?>