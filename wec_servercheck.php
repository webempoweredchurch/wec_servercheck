<?php

/***************************************************************
* Copyright notice
*
* (c) 2006 Foundation for Evangelism (info@evangelize.org)
* All rights reserved
*
* This file is part of the Web-Empowered Church (WEC) ministry of the
* Foundation for Evangelism (http://evangelize.org). The WEC is developing 
* TYPO3-based free software for churches around the world. Our desire 
* use the Internet to help offer new life through Jesus Christ. Please
* see http://WebEmpoweredChurch.org/Jesus.
*
* You can redistribute this file and/or modify it under the terms of the 
* GNU General Public License as published by the Free Software Foundation; 
* either version 2 of the License, or (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This file is distributed in the hope that it will be useful for ministry,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the file!
***************************************************************/

	//-----------------------------------
	//|			User Options			|
	//-----------------------------------

	$GLOBALS['dbHost'] = 'localhost';
	$GLOBALS['dbUser'] = 'root';
	$GLOBALS['dbPass'] = '';


	//-----------------------------------
	//|			Controllers				|
	//-----------------------------------
		
	
	/**
	 * ModuleController, simply keeps a list of all the test modules.
	 *
	 * 
	 * @author Web-Empowered Church Team <developer@webempoweredchurch.org>
	 **/
	class ModuleController {
	
		var $modules;
	
		/**
		 * PHP4 constructor.
		 **/
		function ModuleController() {
			$this->__construct();
		}

		/**
		 * PHP5 constructor for this class.
		 *
		 **/
		function __construct() {
			$this->modules = array();
		}
			
		/**
		 * Registers all the test modules inside the controller for easy access.
		 *
		 * @param $module The name of the Module we want to register. Same as the class name.
		 * @return void
		 **/
	 	function register($module) {
			$this->modules[] = $module;
		}
		
		/**
		 * Returns all the registered modules.
		 *
		 * @return Array
		 **/
		function getModules() {
			return $this->modules;
		}
	}
	
	$mc = new ModuleController();



	//-----------------------------------
	//|			Output Renderers		|
	//-----------------------------------
	
	/**
	 * Renders the output of the test results. Can be replaced to change output format.
	 *
	 * 
	 * @author Web-Empowered Church Team <developer@webempoweredchurch.org>
	 **/
	class RenderOutput {
	
		/**
		 * Table row.
		 **/
		var $ROW = '<tr><td>%s</td><td><pre>%s</pre></td><td>%s</td></tr>';

		/**
		 * Table title.
		 **/
		var $TITLE = "<th>%s</th>";
		
		/**
		 * Renders the module results.
		 * 
		 * @param $module The module that we want to display.
		 * @return String
		 **/
		function render($module) {
			$output = $module->getOutput();
			$title = $module->getTitle();
			
			$show = '<style type="text/css">
				.tablefield {
					width: 150px;
					font-weight: bold;
				}
			</style>';
			$show .= '<table>';
			$show .= sprintf($this->TITLE, $title);
			$show .= '<tr><td class="tablefield">Name</td><td class="tablefield">Value</td><td class="tablefield">Status</td></tr>';
			foreach($output as $key => $value) {
				$status = $this->getStatus($value['status']);
				isset($value['recommendation']) ? $recom = $value['recommendation'] : $recom = null;
				$show .= sprintf($this->ROW, $key, $value['value'], $status . "<br />" . $recom);
			}
			$show .= '</table>';
			
			return $show;
		}
		
		/**
		 * Translate the status integer codes into a String or even image to display.
		 *
		 * @param $status Integer value of the status.
		 * @return String
		 **/
		function getStatus($status) {
			if($status == 1) {
				return '<span style="color: green;">Passed!</span>';
			} else if ($status == 0) {
				return '<span style="color: orange;">Warning!';
			} else if ($status == -1) {
				return '<span style="color: red;">Failed!';
			}
		}
	} 
	
	$renderer = new RenderOutput();
	
	
	//-----------------------------------
	//|			Test Modules			|
	//-----------------------------------
	
	
	/**
	 * Abstract class Module, blueprint for test modules.
	 *
	 * 
	 * @author Web-Empowered Church Team <developer@webempoweredchurch.org>
	 **/
	class Module {

		var $output;
		var $title;

		/**
		 * PHP4 constructor.
		 *
		 * @return void
		 **/
		function Module() {
			$this->__construct();
		}

		/**
		 * PHP5 constructor for this class.
		 *
		 **/
		function __construct() {
			$this->output = array();
			$this->check();
		}

		/**
		 * Does the actual checking and saves the ouput in the global variable.
		 *
		 * @return void
		 **/
		function check() {
			die("Please override this function in your class.");
		}
		
		/**
		 * Gets the title of the test.
		 *
		 * @return String
		 **/
		function getTitle() {
			return $this->title;
		}
		
		/**
		 * Returns the output generated by this test.
		 *
		 * @return Array
		 **/
		function getOutput() {
			return $this->output;
		}

		/**
		 * Adds a status to the individual test. 1 means pass, 0 means warning, -1 means fail.
		 * 
		 * @param $name Name of the sub test. For example 'Version'.
		 * @param $status Status integer for this sub test. 1 means pass, 0 means warning, -1 means fail.
		 * @return void
		 **/
		function addStatus($name, $status) {
			$this->output[$name]['status'] = $status;
		}
		
		/**
		 * Adds a recommendation to the individual test
		 *
		 * @param $name Name of the sub test, e.g. 'Version'.
		 * @param $recom Recommendation in case this test fails or a warning appears.
		 * @return void
		 **/
		function addRecommendation($name, $recom) {
			$this->output[$name]['recommendation'] = $recom;
		}
		
		/**
		 * Adds a value to a test. This is pretty much the resulting value of the test.
		 *
		 * @param $name Name of the sub test, e.g. 'Version'.
		 * @param $value Value of the test, e.g. the version number.
		 * @return void
		 **/
		function addValue($name, $value) {
			$this->output[$name]['value'] = $value;
		}
	}
	
	
	/**
	 * Does some basic PHP checks.
	 *
	 * @author Web-Empowered Church Team <developer@webempoweredchurch.org>
	 **/
	class PHP extends Module {
		
		function __construct() {
			parent::__construct();
			
			$this->title = "PHP Info";
		}
		
		
		function check() {
			$this->checkVersion();
			$this->checkServerAPI();
			$this->checkOS();
			$this->checkMemoryLimit();
		}
		
		/**
		 * Evaluates the PHP version.
		 *
		 * @return void
		 **/
		function checkVersion() {
			
			// get PHP version and add it as value
			$version = phpversion();
			$this->addValue('Version', $version);
						
			// get major PHP version
			$version = explode('.', $version);
			$majorVersion = $version[0];

			// if it's PHP 4 or 5, we should be good, otherwise display error.
			if($majorVersion == 4 || $majorVersion == 5) {
				$this->addStatus('Version', 1);
			} else {
				$this->addStatus('Version', -1);				
				$this->addRecommendation('Version', "PHP Version is too low!");
			}
		}
		
		/**
		 * Checks whether PHP runs in Apache or CGI
		 *
		 * @return void
		 **/
		function checkServerAPI() {
			
			// get Server API and add it as value
			$api = php_sapi_name();
			$this->addValue('Server API', $api);
					
			// cgi and apache is fine. In fact, everything should be fine, we just need this info for later.		
			if($api == 'cgi' || $api == 'apache') {
				$this->addStatus('Server API', 1);
			} else {
				$this->addStatus('Server API', 0);
				$this->addRecommendation('Server API', 'Unknown Server API');
			}
		}
		
		/**
		 * Checks the OS the server is running.
		 *
		 * @return void
		 **/
		function checkOS() {
			
			// get OS the server is running.
			$os = php_uname('s');
			$this->addValue('OS', $os);
			
			// these three OS are known, display warning if an unknown one is shown.
			if($os == 'Linux' || $os == 'Darwin' || strtoupper(substr($os, 0, 3)) === 'WIN') {
				$this->addStatus('OS', 1);
			} else {
				$this->addStatus('OS', 0);
				$this->addRecommendation('OS', 'Unknown Operating System');	
			}
		}
		
		/**
		 * Checks the memory limit.
		 *
		 * @return void
		 **/
		function checkMemoryLimit() {
			
			// get memory limit
			$mlimit = ini_get('memory_limit');
			// print_r(ini_get_all());
			$this->addValue('Memory Limit', $mlimit);
			
		}
	}
	$mc->register('PHP');
	
	
	/**
	 * Does some basic MySQL checks.
	 *
	 * @author Web-Empowered Church Team <developer@webempoweredchurch.org>
	 **/
	class MySQL extends Module {
		
		var $running;
		
		function __construct() {
			parent::__construct();
			
			$this->title = "MySQL Info";
			$this->running = false;
		}
		
		
		function check() {
			$this->checkStatus();
			
			if($this->running) {
				$this->checkVersion();
			}
		}
		
		/**
		 * Evaluates the MySQL version.
		 *
		 * @return void
		 **/
		function checkVersion() {
			
			// Establish MySQL connection and get server info if successful.
			$con = mysql_connect($GLOBALS['dbHost'], $GLOBALS['dbUser'], $GLOBALS['dbPass']);
			$version = mysql_get_server_info($con);
			$this->addValue('Version', $version);

			// get major MySQL version
			$version = explode('.', $version);
			$majorVersion = $version[0];

			// if it's MySQL 4 or 5, we should be good, otherwise display error.
			if($majorVersion == 4 || $majorVersion == 5) {
				$this->addStatus('Version', 1);
			} else {
				$this->addStatus('Version', -1);				
				$this->addRecommendation('Version', "MySQL Version is not compatible!");
			}				
		}
		
		/**
		 * Checks for MySQL
		 *
		 * @return void
		 **/
		function checkStatus() {
			
			$con = mysql_connect($GLOBALS['dbHost'], $GLOBALS['dbUser'], $GLOBALS['dbPass']);
			if($con != false) {
				$this->addValue('Status', 'Running');
				$this->addStatus('Status', 1);	
				$this->running = true;			
			} else {
				$this->addValue('Status', 'Problem');
				$this->addStatus('Status', -1);
				$this->addRecommendation('Status', mysql_error());	
			}

		}
	}
	$mc->register('MySQL');
	
	//-----------------------------------
	//|			Nitty Gritty			|
	//-----------------------------------
	
	error_reporting(0);
	foreach($mc->getModules() as $module) {
		$test = new $module;
		echo $renderer->render($test);
	}
?>