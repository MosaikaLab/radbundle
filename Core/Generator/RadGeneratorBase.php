<?php

namespace Mosaika\RadBundle\Core\Generator;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class RadGeneratorBase {

	protected $class;
	
	protected $namespace;
	
	protected $bundle;
	
	protected $vendor;
	
	protected $baseNamespace;
	
	protected $container;
	


	/**
	 * @return string
	 */
	public function getVendor()
	{
		return $this->vendor;
	}
	public function createDirs($dirs){
		$args = func_get_args();
		foreach($args as $a){
			if(!file_exists($a)){
				mkdir($a);
			}
		}
	}
	protected function runCommand($args){
		$kernel = $this->container->get('kernel');
		$application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
		$application->setAutoExit(false);

		$application->run(new \Symfony\Component\Console\Input\ArrayInput($args));
		
	}
	/**
	 * @param string $vendor
	 */
	public function setVendor($vendor)
	{
		$this->vendor = $vendor;
	}
	
	public function normalizeNamespace($ns){
		return str_replace("\\\\","\\","\\" . $ns);
	}
	
	public function getWorkingPath($folder=null){
		$bundlePath = $this->bundle=="AppBundle" || $this->bundle=="App" ? "src//" : $this->container->get('kernel')->locateResource('@' . $this->bundle);
	    $path = $bundlePath . ($folder ? $folder : $this->baseNamespace ). DIRECTORY_SEPARATOR ;
		if($this->namespace)
			$path .= $this->namespace . DIRECTORY_SEPARATOR;
			 
		$path = str_replace(array("\\","/"),DIRECTORY_SEPARATOR, $path);
		
		return $path;
	}
	
	public function getBundleInstance(){
		$kernel = $this->container->get('kernel');
		$bundles = $kernel->getBundles();
		return $bundles[$this->bundle];
	}
	
	public function getBundleClass(){
		return new \ReflectionClass(get_class($this->getBundleInstance()));
	}
	
	/**
	 *
	 * @param ContainerInterface $container
	 * @return \Mosaika\RadBundle\Core\Generator\RadEntityGenerator
	 */
	protected function setContainer(ContainerInterface $container){
		$this->container = $container;
		return $this;
	}
	
	/**
	 *
	 * @param string $bundle
	 * @return \Mosaika\RadBundle\Core\Generator\RadEntityGenerator
	 */
	public function setBundle($bundle, $vendor=null){
		$this->bundle = $bundle;
		if($vendor)
			$this->vendor = $vendor;
			return $this;
	}
	
	public function getBundle()
	{
		return $this->bundle;
	}
	public function findNamespace($folder=null){
		if($this->bundle=="AppBundle" || $this->bundle == "App"){
			return "App\\" . $folder;
		}
	    $bundleClass = $this->getBundleClass();
	    $res = $bundleClass->getNamespaceName();
	    if($folder){
	        $res .= "\\" . $folder;
	    }
	    return $res;
	    
	}
	public function compileTwig($template,$args){
		
		/**
		 * @var EngineInterface $twig
		 */
		$twig = $this->container->get("templating");
		return $twig->render("MosaikaRadBundle::templates/" . $template,$args);
	}
	/**
	 * @deprecated
	 * @return string
	 */
	public function getFullNameSpace(){
		//TODO Qua non va bene, il namespace � un'opzione che per� non deve necessariamente precedere la cartella "Entity"
		// Potrebbe invece essere una cosa tipo VENDOR/BUNDLE/ENTITY/NAMESPACE
		// o pi� genericamente se mi passano il namespace lo uso (Relativo o no? magari usando il punto viene relativo)
		// altrimenti lo costruisco standard
		$bundleClass = $this->getBundleClass();
		$res = $bundleClass->getNamespaceName();
		if($this->baseNamespace){
			$res .= "\\" . $this->baseNamespace;
		}
		if($this->namespace){
			$res .= "\\" . $this->namespace;
		}
		return $res;
		
		if($this->vendor){
			$res .= $this->vendor . "\\";
		}
		if($this->bundle){
			$res .= $this->bundle. "\\";
		}
		if($this->namespace){
			$res .= $this->namespace . "\\" ;
		}
		return $res . "Entity";
	}
	public function getFullClass(){
		return $this->getFullNameSpace() . "\\" . $this->getClass();
	}
	public function getClass()
	{
		return $this->class;
	}
	
	public function setClass($class)
	{
		$this->class = $class;
		return $this;
	}
	
	public function getNamespace()
	{
		return $this->namespace;
	}
	
	public function setNamespace($namespace)
	{
		$this->namespace = $namespace;
		return $this;
	}
	public function getBaseNamespace() {
		return $this->baseNamespace;
	}
	public function setBaseNamespace($baseNamespace) {
		$this->baseNamespace = $baseNamespace;
		return $this;
	}
	
	
}