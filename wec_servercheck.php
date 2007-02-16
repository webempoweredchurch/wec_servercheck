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

	// MySQL database options
	// dbHost: it's probably okay to leave as localhost
	// dbUser: the database user that typo3 uses
	// dbPass: the password for dbUser
	$GLOBALS['dbHost'] = 'localhost';
	$GLOBALS['dbUser'] = '';
	$GLOBALS['dbPass'] = '';
	
	// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	// !!!! PLEASE DON'T EDIT ANYTHING BEYOND THIS LINE !!!!
	// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	
	//-----------------------------------
	//|			Misc Options			|
	//-----------------------------------

	// determine relative path to typo3/this script.
	$GLOBALS['relativePath'] = substr($_SERVER['REQUEST_URI'], 0, -19);

	// Path to this script from a web browser, without the name of the script. 
	$GLOBALS['scriptPath'] = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . 
		$GLOBALS['relativePath'];
	
	// TYPO3 path in the file system.
	$GLOBALS['TYPO3Path'] = $_SERVER['DOCUMENT_ROOT'] . $GLOBALS['relativePath'];
	
	// TYPO3 path from a browser.
	$GLOBALS['TYPO3WebPath'] = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] .
		$GLOBALS['relativePath'];

	//-----------------------------------
	//|		Check for TYPO3 Install		|
	//-----------------------------------
	
	// check for typo3, typo3conf, fileadmin, uploads
	$typo3 = file_exists('typo3') && is_dir('typo3');
	$t3conf = file_exists('typo3conf') && is_dir('typo3conf');
	$file = file_exists('fileadmin') && is_dir('fileadmin');
	$uploads = file_exists('uploads') && is_dir('uploads');
	
	// define global variable to hold that information
	($typo3 && $t3conf && $file && $uploads) ? $GLOBALS['t3installed'] = true : $GLOBALS['t3installed'] = false ;

	//-----------------------------------
	//|			Controllers				|
	//-----------------------------------
	
	/**
	 * ModuleController, keeps a list of all the test modules and manages their
	 * execution.
	 * 
	 * @author Web-Empowered Church Team <developer@webempoweredchurch.org>
	 **/
	class ModuleController {
		
	
		var $modules;
		var $results;
	
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
			$this->results = array();
		}
			
		/**
		 * Registers all the test modules inside the controller for easy access.
		 *
		 * @param $module The name of the Module we want to register. Same as the class name.
		 * @return void
		 **/
	 	function register($module) {
			$cur = new $module;
			$this->modules[$cur->getTitle()] = $cur;
		}
		
		/**
		 * Returns all the registered modules.
		 *
		 * @return Array
		 **/
		function getModules() {
			return $this->modules;
		}
		
		/**
		 * Runs all modules and keeps track of dependencies.
		 *
		 * @return void
		 **/
		function runAll() {
			foreach($this->modules as $module) {
				$module->run();
				$this->results[$module->getTitle()] = $module->getResults();					
			}
		}
		
		/**
		 * Returns the value for a particular test
		 * 
		 * @param $test The title of the module
		 * @param $subtest The name of the subtest, like 'Version' etc.
		 * @return String
		 **/
		function getTestValue($test, $subtest) {
			
			// run a single test
			$this->modules[$test]->$subtest();

			// get the results object for it first, then the results
			$res = $this->modules[$test]->getResults();
			$modResults = $res->getTests();

			// return the value for the one we need
			return $modResults[$subtest]['value'];
		}
		
		/**
		 * Returns the status for a particular test
		 * 
		 * @param $test The title of the module
		 * @param $subtest The name of the subtest, like 'Version' etc.
		 * @return String
		 **/
		function getTestStatus($test, $subtest) {

			// run a single test
			$this->modules[$test]->$subtest();
			
			// get the results object for it first, then the results
			$res = $this->modules[$test]->getResults();
			$modResults = $res->getTests();

			// return the status for the one we need
			return $modResults[$subtest]['status'];
		}
		
		/**
		 * Returns all the results, ususally for use in a rendering object to display them.
		 *
		 * @return Array
		 **/
		function getResults() {
			return $this->results;
		}
	}
	$mc = new ModuleController();
	$GLOBALS['mc'] = $mc;
	
	/**
	 * Provides an object that just controls all other renderers. RenderController will simply 
	 * pull together the Renderers and Modules and present the output to the user.
	 *
	 * @author Web-Empowered Church Team <developer@webempoweredchurch.org>
	 **/
	class RenderController	{
		
		var $renderers;
		var $results;
		
		/**
		 * PHP4 compatible constructor
		 *
		 **/
		function RenderController() {
			$this->__construct();
		}
		
		/**
		 * Constructor
		 *
		 **/
		function __construct() {
			$this->results = array();
			$this->renderers = array();
		}
		
		/**
		 * Registers all the renderers inside the controller for easy access.
		 *
		 * @param $renderer The name of the Renderer we want to register. Same as the class name.
		 * @return void
		 **/
	 	function register($renderer) {
			// create a new object of class Renderer
			$obj = new $renderer;
			$this->renderers[] = $obj;
		}
		
		/**
		 * Renders all the output inside a HTML page
		 *
		 * @return String
		 **/
		function render() {
			
			$show = '<html><head>';
			$show .= $this->printHeaders();
			$show .= '<title>WEC Server Checker</title>';
			$show .= '</head><body>';
			$show .= '<h1>Web-Empowered Church Server Checker</h1>';
			
			foreach( $this->renderers as $renderer ) {
				$show .= $renderer->renderAll($this->results);
			}
			
			$note = '<strong>Note:</strong> If you know that any of these test results are wrong, please post your test results and corrections in the <a href="http://webempoweredchurch.com/support/community/">"Installing" forum on the WEC website</a>. Thank you!';
			$show .= '<div style="width: 600px;">' . $note . '</div>';
			$show .= '</body></head>';
			
			return $show;
		}
		
		/**
		 * Sets the results from all the modules.
		 *
		 * @return void
		 **/
		function setResults($results) {
			$this->results = $results;
		}
		
		/**
		 * Prints out all the headers.
		 *
		 * @return String
		 **/
		function printHeaders() {
			$headers = null;
			foreach( $this->renderers as $renderer)	{
				$headers .= $renderer->getHeaders();
			}
			return $headers;
		}
		
	} // END class RenderController
	$rc = new RenderController();

	//-----------------------------------
	//|			Output Renderers		|
	//-----------------------------------
	
	/**
	 * Abstract class that all Renderers need to inherit from.
	 *
	 * @author Web-Empowered Church Team <developer@webempoweredchurch.org>
	 **/
	class Renderer {
		
		var $headers;
		var $output;
		
		/**
		 * PHP4 compatible constructor
		 *
		 **/
		function Renderer() {
			$this->__construct();
		}
		
		/**
		 * Constructor
		 *
		 **/
		function __construct() {
			$this->output = array();
			$this->headers = $this->setHeaders();
		}
		
		/**
		 * Determines how a single test module is rendered.	
		 *
		 **/
		function render() {
			die("Please override the render() method in our class");
		}
		
		/**
		 * Handles the rendering of all the test modules.
		 *
		 **/
		function renderAll() {
			die("Please override the renderAll() method in our class");
		}
		
		/**
		 * Defines headers that need to go inside <head> tags. Can be
		 * overwridden by the child class if needed.
		 *
		 * @return null
		 **/
		function setHeaders() {

			return null;
		}
		
		/**
		 * Returns the headers for this renderer.  			
		 *
		 * @return String
		 **/
		function getHeaders() {
			return $this->headers;
		}
		
	} // END class Renderer
	
	/**
	 * Renders the detailed output of the test results. Can be replaced to change output format.
	 *
	 * 
	 * @author Web-Empowered Church Team <developer@webempoweredchurch.org>
	 **/
	class RenderDetailed extends Renderer {
	
		// Table Row
		var $ROW = '<tr><td>%s</td><td><pre>%s</pre></td><td>%s</td></tr>';

		// Recommendation Rows
		var $RROWW = '<tr class="recomrowwarn"><td colspan="3">%s</td></tr>';
		var $RROWF = '<tr class="recomrowfail"><td colspan="3">%s</td></tr>';
				
		// Table title
		var $TITLE = '<th colspan="3">%s</th>';

		/**
		 * Renders all the modules from given results array
		 *
		 * @param $results Array that contains all the data for our tests.
		 * @return String
		 **/
		function renderAll($results) {
			$output = '<a href="#" onclick="toggle()" id="showhidelink">Show Details</a>';
			$output .= '<div id="detailed">';
			foreach($results as $title => $resultsObj) {
				$output .= $this->render($resultsObj->getTests(), $title);
			}
			$output .= '</div>';
			return $output;
		}
		
		/**
		 * Renders the module results.
		 * 
		 * @param $testData Testdata array.
		 * @param $title The title for this module.
		 * @return String
		 **/
		function render($testData, $title) {
			$show = '<table cellspacing="0">';
			$show .= sprintf($this->TITLE, $title);
			$show .= '<tr><td class="tablefield">Name</td><td class="tablefield">Value</td><td class="tablefield">Status</td></tr>';

			foreach($testData as $value) {
				$status = $this->getStatus($value['status']);
				$show .= sprintf($this->ROW, $value['name'], $value['value'], $status);
				if(isset($value['recommendation']) && $value['status'] == 0) {
					$show .= sprintf($this->RROWW, $value['recommendation']);	
				} else if (isset($value['recommendation']) && $value['status'] == -1) {
					$show .= sprintf($this->RROWF, $value['recommendation']);	
				}
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
				return '<span style="color: green;">Passed</span>';
			} else if ($status == 0) {
				return '<span style="color: orange;">Warning</span>';
			} else if ($status == -1) {
				return '<span style="color: red;">Failed</span>';
			}
		}
		
		/**
		 * Defines stuff that needs to go inside the <head> tag. Is called by the constructor, so it's just
		 * a convenient way to separately define this data.
		 *
		 * @return String
		 **/
		function setHeaders() {
			$headers = 	'<style type="text/css">
				.tablefield {
					width: 230px;
					font-weight: bold;
					border-bottom: 1px solid black;
				}

				th {
					border-bottom: 1px black solid;
				}
				
				#detailed {
					display: none;
				}
				
				.recomrowfail td{
					border-top: 1px solid black;
					border-bottom: 1px solid black;
					text-align: center;
					font-size: 10pt;
					background: red;
				}

				.recomrowwarn td{
					border-top: 1px solid black;
					border-bottom: 1px solid black;
					text-align: center;
					font-size: 10pt;
					background: orange;
				}

				table {
					border: 1px solid black;
					margin-bottom: 10px;
				}

			</style>';
			
			$headers .= '<script type="text/javascript">
				function toggle() {
					var a = document.getElementById(\'showhidelink\');
					if(a.innerHTML==\'Hide Details\') {
						a.innerHTML = \'Show Details\';
						hide()
					} else {
						a.innerHTML = \'Hide Details\';
						show()
		
					}
				}
				
				function hide() {
					var element = document.getElementById("detailed");
					element.style.display = "none";
				}
				
				function show() {
					var element = document.getElementById("detailed");
					element.style.display = "block";
				}
			</script>';
				
			return $headers;
		}
	} 

	/**
	 * Renders the output as plain text inside a textarea for easy copying and pasting.
	 *
	 * @author Web-Empowered Church Team <developer@webempoweredchurch.org>
	 **/
	class RenderPlain extends Renderer {

		// plain row
		var $PROW = "%s \t\t %s \t\t %s\n";
		
		// Recommendation Rows
		var $PRROW = "%s\n";

		// title
		var $PTITLE = "\n-= %s =-\n";
		
		/**
		 * Renders all the modules from given results array
		 *
		 * @param $results Array that contains all the data for our tests.
		 * @return String
		 **/
		function renderAll($results) {
			
			$plain = null;
			$output = null;
			
			foreach($results as $title => $resultsObj) {
				$plain .= $this->render($resultsObj->getTests(), $title);
			}
			
			$output .= '<p>Copy and paste the contents of the textarea below into emails or forum posts.<br />';
			$output .= '<textarea cols="100" rows="20">';
			$output .= $plain;
			$output .= '</textarea></p>';
			
			return $output;
		}
		
		/**
		 * Renders the module results in plain text.
		 *
		 * @return void
		 **/
		function render($testData, $title) {
			$show = sprintf($this->PTITLE, $title);

			foreach($testData as $value) {
				$status = $this->getStatus($value['status']);
				$length1 = strlen($value['name']);
				$length2 = strlen($value['value']);
				if($length1 < 8) {
					$pad1 = "\t\t\t";	
				} else if ($length1 > 14) {
					$pad1 = "\t";	
				} else {
					$pad1 = "\t\t";
				}
				
				if($length2 < 8) {
					$pad2 = "\t\t\t";	
				} else if ($length2 > 14) {
					$pad2 = "\t";	
				} else {
					$pad2 = "\t\t";
				}

				$show .= $value['name'] . $pad1 . $value['value'] . $pad2 .  $status . "\n";

				if(isset($value['recommendation'])) {
					$show .= sprintf($this->PRROW, $value['recommendation']);
				}
			}
			
			return $show;
		}
		
		/**
		 * Translates the status integer codes into plain text Strings.
		 *
		 * @return String
		 **/
		function getStatus($status) {
			if($status == 1) {
				return 'Passed';
			} else if ($status == 0) {
				return 'Warning';
			} else if ($status == -1) {
				return 'Failed';
			}
		}
	}
	
	/**
	 * Shows just one result for each Module, making it easier for non-technical users to
	 * get the gist of what is going on without sensory overload.
	 *
	 * @author Web-Empowered Church Team <developer@webempoweredchurch.org>
	 **/
	class OverallResults extends Renderer {
		
		/**
		 * Renders all the modules from given results array
		 *
		 * @param $results Array that contains all the data for our tests.
		 * @return String
		 **/
		function renderAll($results) {
			$output = null;
			
			foreach($results as $title => $resultsObj) {
				$output .= $this->render($title, $resultsObj);
			}

			return $output;
		}


		/**
		 * Renders the status and recommendation for a module.
		 *
		 * @return void
		 **/
		function render($title, $resultsObj) {
			
			// get all the failed test's recommendations
			$failed = $resultsObj->getFailedRecoms();
			
			// check if we want to show failed recommendations
			$showFailed = $resultsObj->showFailed();
			
			// get the overall info and put it in separate variables
			$overall = $resultsObj->getOverall();
			$status = $overall['status'];
			$recom = null;
			
			// show recommendations if there are any
			if(!empty($overall['recommendation'])) {
				
				$recom = '<ul>';
				foreach( $overall['recommendation'] as $value ) {
					$recom .= '<li>' . $value . '</li>';
				}
				$recom .= '</ul>';
			}
			
			// start the output table
			$show = '<table cellspacing="0">';
			$show .= '<tr><td class="overall-tablefield">';
			$show .= $title;
			$show .= '</td><td style="text-align: right;" class="overall-tablefield">';
			$show .= $this->getStatus($status);
			$show .= '</td></tr>';
			$show .= '<tr><td colspan="2">';
			$show .= $recom;
			
			// only if there are failed tests do we want to show the recommendations
			if(!empty($failed) && $showFailed) {
				$show .= 'Details: <br />';
				$show .= '<ul>';
				foreach( $failed as $singleR )
				{
					$show .= '<li>' . $singleR . '</li>';
				}
				$show .= '</ul>';
			}

			$show .= '</td></tr>';
			$show .= '</td></tr></table>';
			
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
				return '<span style="color: green;">Passed</span>';
			} else if ($status == 0) {
				return '<span style="color: orange;">Warning</span>';
			} else if ($status == -1) {
				return '<span style="color: red;">Failed</span>';
			}
		}
		
		/**
		 * Set headers
		 *
		 * @return String
		 **/
		function setHeaders() {
			$headers = 	'<style type="text/css">
				.overall-tablefield {
					width: 300px;
					font-weight: bold;
					border-bottom: 1px solid black;
				}			
			</style>';
			return $headers;
		}
		
	} // END class OverallResults	
	
	//-----------------------------------
	//|			Test Modules			|
	//-----------------------------------
	
	/**
	 * Defines the results for one test module.
	 *
	 * @author Web-Empowered Church Team <developer@webempoweredchurch.org>
	 **/
	class Results {
		var $overall;
		var $testResults;
		
		/**
		 * PHP4 compatible constructor
		 *
		 **/
		function Results() {
			$this->__construct();
		}
		
		/**
		 * Constructor
		 *
		 **/
		function __construct() {
			$this->overall = array();
			$this->overall['showFailed'] = false;
			$this->overall['status'] = 2;
			$this->overall['recommendation'] = array();
			$this->testResults = array();
		}
		
		/**
		 * Fills in the result array.
		 *
		 * @return void
		 **/
		function test($method, $name, $value, $status, $recommendation = null) {
			$this->testResults[$method]['value'] = $value;
			$this->testResults[$method]['status'] = $status;
			$this->testResults[$method]['name'] = $name;
			(empty($recommendation)) ? null : $this->testResults[$method]['recommendation'] = $recommendation;
		}
		
		/**
		 * Similar to message, except it works on a whole module and thus
		 * writes to different segements of the array.
		 *
		 * @return void
		 **/
		function overall($status, $recommendation = null, $showFailedRecoms = true) {
			
			// if we are at a good status right now, but want to add a worse one, we
			// want the worst one to show. Before, if a warning was added after a fail,
			// the whole test would be a warning. So we always show the worst status.
			if($this->overall['status'] > $status)	$this->overall['status'] = $status;
			$this->overall['recommendation'][] = $recommendation;
			$this->overall['showFailed'] = $showFailedRecoms;
		}
		
		/**
		 * Gets the title of the test.
		 *
		 * @return String[]
		 **/
		function getOverall() {
			return $this->overall;
		}
		
		/**
		 * Returns the test result array
		 *
		 * @return Array
		 **/
		function getTests() {
			return $this->testResults;
		}
		
		/**
		 * Gets the status for a test
		 *
		 * @return int
		 **/
		function getStatus($test) {
			return $this->testResults[$test]['status'];
		}
		
		/**
		 * Get value for a test
		 *
		 * @return mixed
		 **/
		function getValue($test) {
			return $this->testResults[$test]['value'];
		}
		
		/**
		 * Returns an array of recommendations of all those tests that failed.
		 *
		 * @return array
		 **/
		function getFailedRecoms() {
			$failed = array();
			
			foreach( $this->testResults as $test )
			{
				if ( $test['status'] !== 1) {
					$failed[] = $test['recommendation'];
				}
			}
			return $failed;
		}
		
		/**
		 * Whether or not to show the single recommendations
		 *
		 * @return boolean
		 **/
		function showFailed() {
			return $this->overall['showFailed'];
		}		
	} // END class Result

	/**
	 * Abstract class Module, blueprint for test modules.
	 *
	 * 
	 * @author Web-Empowered Church Team <developer@webempoweredchurch.org>
	 **/
	class Module {

		var $results;
		var $title;
		var $hasRun;

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
			$this->results = new Results();
			$this->hasRun = false;
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
		 * Makes a suggestion based on all the test results.
		 *
		 * @return void
		 **/
		function evaluate() {
			//die("Please override this function in your class.");
		}
		
		/**
		 * Get title for module.
		 *
		 * @return String
		 **/
		function getTitle() {
			return $this->title;
		}
		
		/**
		 * Returns the Results object of this test.
		 *
		 * @return Array
		 **/
		function getResults() {
			return $this->results;
		}
		
		/**
		 * Checks whether the test has already run.
		 *
		 * @return boolean
		 **/
		function hasRun() {
			return $this->hasRun;
		}
		
		/**
		 * Runs the given module if it hasn't run before and saves the results in an array.
		 *
		 * @return void
		 **/
		function run() {
			if(!$this->hasRun) {
				$this->check();
				$this->evaluate();
				$this->hasRun = true;
			}
		}

	   /**
	   * @return array
	   * @param string $url
	   * @param int $format
	   * @desc Fetches all the headers
	   **/
	   	function getHeaders($url,$format=0) {

			$url_info=parse_url($url);
			$port = isset($url_info['port']) ? $url_info['port'] : 80;
			$fp=fsockopen($url_info['host'], $port, $errno, $errstr, 30);
			if($fp) {
			    $head = "HEAD ".@$url_info['path']."?".@$url_info['query'];
			    $head .= " HTTP/1.0\r\nHost: ".@$url_info['host']."\r\n\r\n";
			    fputs($fp, $head);
			    while(!feof($fp)) {
			    	if($header=trim(fgets($fp, 1024))) {
			            if($format == 1) {
			                $h2 = explode(':',$header);
			                // the first element is the http header type, such as HTTP/1.1 200 OK,
			                // it doesn't have a separate name, so we have to check for it.
			                if($h2[0] == $header) {
			                    $headers['status'] = $header;
			                }
			                else {
			                    $headers[strtolower($h2[0])] = trim($h2[1]);
			                }
			            }
			            else {
			                $headers[] = $header;
			            }
			        }
			    }
			    return $headers;
			} else {
			    return false;
			}
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

			$this->title = "PHP Scripting Test";
		}
		
		
		function check() {
			$this->checkVersion();
			$this->checkServerAPI();
			$this->checkExecTime();
			$this->checkOS();
			$this->checkMemoryLimit();
			$this->checkUploadLimit();
			$this->checkFunctions();
		}
		
		function evaluate() {
			$allgood = ($this->results->getStatus('checkVersion') == 1
				&& $this->results->getStatus('checkMemoryLimit') == 1
				&& $this->results->getStatus('checkUploadLimit') == 1
				&& $this->results->getStatus('checkExecTime') == 1
				&& $this->results->getStatus('checkFunctions') == 1
			);
			
			$almostallgood = ($this->results->getStatus('checkVersion') == 1
				&& $this->results->getStatus('checkMemoryLimit') == 1
				&& $this->results->getStatus('checkUploadLimit') == 0
				&& $this->results->getStatus('checkFunctions') == 1
			);
					
			$configError = ($this->results->getStatus('checkVersion') == 1
				|| $this->results->getStatus('checkMemoryLimit') != 1 
				|| $this->results->getStatus('checkUploadLimit') != 1
				|| $this->results->getStatus('checkExecTime') != 1
				|| $this->results->getStatus('checkFunctions') != 1);
			$wrongVersion = $this->results->getStatus('checkVersion') == -1;
			$badVersion = $this->results->getStatus('checkVersion') == 0;
			
			if ( $allgood ) {
				$this->results->overall(1, 'PHP is okay!');
				return;
			} else if($almostallgood){
				$this->results->overall(0, 'PHP is nearly okay!');
				return;
			}
			
			if ( $configError ) {
				$this->results->overall(-1, 'You have the right PHP version, but there were one or more configuration error(s):');
			} elseif ($wrongVersion) {
				$this->results->overall(-1, 'You don\'t have the right PHP version. TYPO3 requires at least PHP 4.3.4', false);
			} elseif ($wrongVersion) {
				$this->results->overall(-1, 'You are running a bad PHP version. See below for details', true);
			}
		}
		
		/**
		 * Evaluates the PHP version.
		 *
		 * @return void
		 **/
		function checkVersion() {

			// get PHP version and add it as value
			$version = phpversion();

			// get major PHP version
			$versionArray = explode('.', $version);
			$majorVersion = $versionArray[0];
			$minorVersion = $versionArray[1];
			$miniVersion = $versionArray[2];
			
			// if it's PHP 4 or 5, we should be good, otherwise display error.
			if ($majorVersion == 5 && $minorVersion == 2 && $miniVersion == 0) {
				$recom = 'There are a few severe issues with PHP 5.2.0 that prevent TYPO3 from working correctly.
					Please either upgrade to a higher version or downgrade to a lower version.';
				$this->results->test('checkVersion', 'Version', $version, 0, $recom);			
			} else if ($majorVersion == 4 || $majorVersion == 5) {
				$this->results->test('checkVersion', 'Version', $version, 1);
			} else {
				$this->results->test('checkVersion', 'Version',$version, -1, "PHP Version number is too low!");				
			}
		}
		
		/**
		 * Checks whether PHP runs in Apache or CGI
		 *
		 * @return void
		 **/
		function checkServerAPI() {

			// get Server API
			$api = php_sapi_name();
					
			// cgi and apache is fine. In fact, everything should be fine, we just need this info for later.		
			if($api == 'cgi' || $api == 'apache' || $api == 'apache2handler') {
				$this->results->test('checkServerAPI', 'Server API', $api, 1);
			} else {
				$this->results->test('checkServerAPI', 'Server API', $api, 0, 'Unknown Server API');
			}
		}
		
		/**
		 * Checks for maximum execution time
		 *
		 * @return void
		 **/
		function checkExecTime() {
			$maxExecTime = ini_get('max_execution_time');

			if($maxExecTime >= 30) {
				$this->results->test('checkExecTime', 'Max Execution Time', $maxExecTime, 1);
			} else {
				$recom = 'The max_execution_time is too low. Please set it to at least 30 seconds.';
				$this->results->test('checkExecTime', 'Max Execution Time', $maxExecTime, 0, $recom);
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
			
			// these three OS are known, display warning if an unknown one is shown.
			if($os == 'FreeBSD' || $os == 'Linux' || $os == 'Darwin' || strtoupper(substr($os, 0, 3)) === 'WIN') {
				$this->results->test('checkOS', 'OS', $os, 1);
			} else {
				$this->results->test('checkOS', 'OS', $os, 0, 'Unknown Operating System');
			}
		}
		
		/**
		 * Checks the memory limit.
		 * @return void
		 **/
		function checkMemoryLimit() {

			// get memory limit
			$mlimit = ini_get('memory_limit');

			// set the good limit, which is 32M
			$glimit = '32M';
			
			// convert both to bytes
			$mlimitBytes = $this->returnBytes($mlimit);
			$glimitBytes = $this->returnBytes($glimit);
			
			
			// if ours is more than recommended...
			if($mlimitBytes >= $glimitBytes) {
				$this->results->test('checkMemoryLimit', 'Memory Limit', $mlimit, 1);
				
			// else if no memory limit in place, so that's good, too...
			} else if (empty($mlimit)) {
				$this->results->test('checkMemoryLimit', 'Memory Limit', "No memory limit in effect!", 1);

			// else it's too low :(
			} else {
				$recom = 'The memory limit is too low.';
				if($GLOBALS['mc']->getTestStatus('Apache Web Server Test','override') == 1) {
					$recom .= 'You can try putting this line in the .htaccess file of your
						TYPO3 root directory:<br />php_value memory_limit 32M<br />';
				}
				$recom .= 'If you have access to the php.ini configuration file, please set the memory_limit
					to at least 32M or ask your hosting company to do so.';
					
				$this->results->test('checkMemoryLimit', 'Memory Limit', $mlimit, -1, $recom);
			}
			
		}
	
		/**
		 * Checks the upload limit for Typo3's default.
		 *
		 * @return void
		 **/
		function checkUploadLimit() {
			
			// get max upload filesize
			$ulimit = ini_get('upload_max_filesize');
			$postLimit = ini_get('post_max_size');
			
			// good value = default in Typo3 is 10M
			$good = '10M';
			$bad = '2M';
			
			// convert both to bytes
			$postBytes = $this->returnBytes($postLimit);
			$bytes = $this->returnBytes($ulimit);
			$gbytes = $this->returnBytes($good);
			$bbytes = $this->returnBytes($bad);
			
			// if more than good, all good.
			// else if lower than min, fail.
			// else it could still be okay, warning.
			if($bytes >= $gbytes && $postBytes >= $bytes) {
				$this->results->test('checkUploadLimit', 'Max Upload Filesize', $ulimit, 1);
			} else if ($bytes < $bbytes) {
				$recom = 'Max upload file size is much lower than the TYPO3 default. Please raise 
					upload_max_filesize to at least 10M. This can be set in the php.ini configuration
					file if you can access it, or ask your hosting company to do so.';
				$this->results->test('checkUploadLimit', "Max Upload Filesize", $ulimit, -1, $recom);
			} else if ($bytes < $gbytes){
				$recom = 'Max upload file size is lower than the TYPO3 default. Consider raising 
					upload_max_filesize to at least 10M. This can be set in the php.ini configuration
					file if you can access it, or ask your hosting company to do so.';
				$this->results->test('checkUploadLimit', "Max Upload Filesize", $ulimit, 0, $recom);
			} else if ($postBytes < $bytes) {
				$recom = 'Max upload file size ('.$ulimit.') is larger than allowed POST data ('.$postLimit.'). 
					This is okay, but the upload filesize will be limited to '.$postLimit.'. Consider raising 
					post_max_size to at least '.$ulimit.' to take advantage of larger file uploads. This can be 
					set in the php.ini configuration file if you can access it, or ask your hosting company to do so.';
				$this->results->test('checkUploadLimit', "Max Upload Filesize", $ulimit, 0, $recom);
			}
		}
		
		/**
		 * Takes PHP style number and converts to byte.
		 *
		 * @return int
		 **/
		function returnBytes($val) {
			if(empty($val)) return 0;
			
			$val = trim($val);
		   	$last = strtolower($val{strlen($val)-1});
		
		   	switch($last) {
		       	// The 'G' modifier is available since PHP 5.1.0
		       	case 'g':
		           	$val *= 1024;
		       	case 'm':
		           	$val *= 1024;
		       	case 'k':
		           	$val *= 1024;
		   	}

		   	return $val;
		}
		
		/**
		 * Makes sure that exec() is working.
		 *
		 * @return void
		 **/
		function checkFunctions() {
			/* @fixme		Matches both Windows NT and Darwin */
			$win = strpos(strtolower($GLOBALS['mc']->getTestValue('PHP Scripting Test', 'checkOS')), 'win') !== false;
			if($win) {
				exec('dir', $output);
			} else {
				echo 'ls';
				exec('ls -al', $output);				
			}

			if($output) {
				$this->results->test('checkFunctions', 'Required Functions', 'success', 1);
			} else {
				$recom = 'Could not use the exec() PHP function on this server. Please check with your
				 hosting company and make sure that the use of the exec() function is allowed because
				 it is needed for some TYPO3 features.';
				$this->results->test('checkFunctions', 'Required Functions', 'failed', -1, $recom);
			}
		}
	}
	
	/**
	 * Does some basic MySQL checks.
	 *
	 * @author Web-Empowered Church Team <developer@webempoweredchurch.org>
	 **/
	class MySQL extends Module {
		
		var $running;
		
		function __construct() {
			parent::__construct();
			
			$this->title = "MySQL Database Test";
			$this->running = false;
		}
		
		
		function check() {
			$this->checkStatus();
			$this->checkClient();			
			$this->checkSafeMode();
			
			if($this->running) {				
				$this->checkPersistentConnection();
				$this->checkServer();
				$this->checkHost();		
			}
		}
		
		function evaluate() {
			$noinfo = $this->results->getStatus('checkStatus') == 0;
			$allgood = $this->running && $this->results->getStatus('checkPersistentConnection') == 1
				&& $this->results->getStatus('checkSafeMode') == 1;
			$notrunning = !$this->running;
			
			if ($noinfo) {
				$recom = 'Testing not possible.';
				$this->results->overall(0, $recom);				
			} else if ($notrunning) {
				$this->results->overall(-1, 'MySQL doesn\'t appear to be running.', false);				
			} elseif($allgood) {
				$this->results->overall(1, 'MySQL is okay');				
			} else {
				$this->results->overall(-1, 'There have been errors:');
			}

		}
		
		/**
		 * Checks the MySQL host connection
		 *
		 * @return void
		 **/
		function checkHost() {
			$host = mysql_get_host_info();
			$this->results->test('checkHost', 'Host Info', $host, 1);
		}
		
		/**
		 * Checks whether we are running SQL safe mode
		 *
		 * @return void
		 **/
		function checkSafeMode() {
			$safe = ini_get('sql.safe_mode');
			
			if(!$safe) {
				$this->results->test('checkSafeMode', 'SQL Safe Mode', $version, 1);
			} else {
				$recom = 'SQL is running in Safe Mode. Make sure that sql.safe_mode is turned to off in your php.ini.';
				$this->results->test('checkSafeMode', 'SQL Safe Mode', $version, -1, $recom);
			}
		}
		/**
		 * Checks the MySQL client version
		 *
		 * @return void
		 **/
		function checkClient() {
			$version = mysql_get_client_info();
			$this->results->test('checkClient', 'Client Version', $version, 1);
		}
		
		
		/**
		 * Evaluates the MySQL server version.
		 *
		 * @return void
		 **/
		function checkServer() {
			
			// Establish MySQL connection and get server info if successful.
			$con = mysql_connect($GLOBALS['dbHost'], $GLOBALS['dbUser'], $GLOBALS['dbPass']);
			$version = mysql_get_server_info($con);
			
			// get major MySQL version
			$xVersion = explode('.', $version);
			$majorVersion = $xVersion[0];
			$minorVersion = $xVersion[1];

			// if it's MySQL 4 or 5, we should be good, otherwise display error.
			if($majorVersion == 4 || $majorVersion == 5 || ($majorVersion == 3 && $minorVersion >= 23)) {
				$this->results->test('checkServer', 'Server Version', $version, 1);
			} else {
				$this->results->test('checkServer', 'Server Version', $version, -1, "MySQL Version is not compatible!");
			}				
		}
		
		/**
		 * Checks for MySQL
		 *
		 * @return void
		 **/
		function checkStatus() {
			
			// check if user has entered their information
			if(empty($GLOBALS['dbUser']) && empty($GLOBALS['dbPass'])) {
				$recom = 'Please edit the top of this file and enter your database information 
				as provided by your hosting company or system manager.';
				$this->results->test('checkStatus', 'Status', 'Missing Information', 0, $recom);
				return;
			}
			
			$con = mysql_connect($GLOBALS['dbHost'], $GLOBALS['dbUser'], $GLOBALS['dbPass']);
			if($con != false) {
				$this->results->test('checkStatus', 'Status', 'Running', 1);
				$this->running = true;			
			} else {
				$this->results->test('checkStatus', 'Status', 'Running', -1, mysql_error());
			}

		}
		
		/**
		 * Trys to establish a persistent connection to the MySQL server.
		 *
		 * @return void
		 **/
		function checkPersistentConnection() {
			$link = mysql_pconnect($GLOBALS['dbHost'], $GLOBALS['dbUser'], $GLOBALS['dbPass']);
			if($link) {
				$this->results->test('checkPersistentConnection', 'Persistent connection', 'Success', 1);
			} else {
				$recom = 'Your setup doesn\'t allow persistent connections. In the Install Tool, make
					sure you configure TYPO3 to not	use persistent connections.';
				$this->results->test('checkPersistentConnection', 'Persistent connection', 'Failed', 0, $recom);
			}
			mysql_close($link);
		}
	}
		
	/**
	 * Checks file permissions
	 *
	 * @author Web-Empowered Church Team <developer@webempoweredchurch.org>
	 **/
	class FilePermissions extends Module {
		/**
		 * Constructor
		 *
		 **/
		function __construct() {
			parent::__construct();
			
			$this->title = 'File Permissions Test';
		}
		
		function check() {
			$this->check777();
			$this->checkTempPermissions();
			// $this->checkR();
			$this->symlinks();
		}
		
		function evaluate() {
			
			/* @fixme		strpos matches both Windows NT and Darwin */
			$allgood = $this->results->getStatus('symlinks') == 1 && $this->results->getStatus('checkTempPermissions') == 1;
			$failwin = 	$this->results->getStatus('symlinks') != 1 && strpos(strtolower($GLOBALS['mc']->getTestValue('PHP Scripting Test', 'checkOS')), 'win') !== false;
			$failnowin = $this->results->getStatus('symlinks') == -1 && strpos(strtolower($GLOBALS['mc']->getTestValue('PHP Scripting Test', 'checkOS')), 'win') === false;
			$warningnowin = $this->results->getStatus('symlinks') == 0 && strpos(strtolower($GLOBALS['mc']->getTestValue('PHP Scripting Test', 'checkOS')), 'win') === false;
			$phpsuexec = $this->results->getValue('check777') == 'No';

			// if no symlink was created and this is windows show warning.
			if($allgood) {
				$this->results->overall(1, 'File permissions and symlinks okay!');

			// no symlink was created, we'ren ot using windows, but PHPsuExec.
			} else if($warningnowin && $phpsuexec) {
				$recom = 'Symlinks can be created, but not read. The reason for this is that PHPsuExec is installed.';
				if (!$GLOBALS['t3installed']) $recom .= ' Please download the .zip package to install TYPO3.';
				$this->results->overall(0, $recom, false);
			// no symlink was created, but we aren't using Windows or PHPsuExec; that's not good.
			} else if($warningnowin && $phpsuexec) {
				$recom = 'Symlinks can be created, but not read. The reason for this is unknown. Please post the 
				test results and server configuration, if known, to the WEC Install forum (see link below).';
				$this->results->overall(0, $recom, false);
			// using windows
			} else if ($failwin) {
				$recom = 'Symlinks couldn\'t be created. This is perfectly normal since the server is running Windows.';
				if ($GLOBALS['t3installed']) $recom .= 'Please download the .zip package to install TYPO3.';
				$this->results->overall(1, $recom, false);

			} else if ($failnowin) {
				$recom = 'Symlinks couldn\'t be created even though you are not using Windows. Please check the
					permissions on the folder this script is installed in and make sure it is allowed to write to it.';
				if (!$GLOBALS['t3installed']) $recom .= ' Please download the .zip package to install TYPO3.';
				$this->results->overall(-1, $recom, false);
			}
			
			if($this->results->getStatus('checkTempPermissions') == 0) {
				$recom = "Cannot determine file permissions needed by this script because required PHP functions aren't
					allowed. Please see the PHP Scripting Test results for more info.";
				$this->results->overall(0, $recom, false);				
			} else if ($this->results->getStatus('checkTempPermissions') == -1) {
				$recom = "This script is unable to create temporary folders. Please make sure file permissions are correct.";
				$this->results->overall(-1, $recom, false);
			}

		}
		/**
		 * Checks which permissions are needed to read, write, and execute files.
		 *
		 * @return void
		 **/
		function checkTempPermissions() {

			// define permissions we want to test
			$perms = array('0777', '0775', '0755');

			// clear file cache just to be sure
			clearstatcache();
			
			// check if tmp dir already exists for some crazy reason and delete it
			// and everything in it.
			if(file_exists('tmp')) {
				foreach(glob('tmp/*') as $file) {
					unlink($file);					
				}
				rmdir('tmp');
			}
			
			// now create a folder with each of the permissions defined above.
			foreach($perms as $perm) {
				$out = mkdir('tmp', octdec($perm));			

				// if that didn't work we have a problem
				if(!$out) {
					$this->results->test('checkTempPermissions', 'Minimum write permissions', "N/A", -1, 'Could not create a temporary test folder; please check directory permissions.');
					return;
				}
				
				// if the previous did work, create a temp file and get the return value
				$test = touch('tmp/test.php');

				// if write was successful, save the file permissions in the results. It will 
				// overwrite this until one of them doesn't work, and leave it at the minimum
				// that did work which is exactly what we want.
				if($test) $this->results->test('checkTempPermissions', 'Minimum write permissions', $perm, 1);

				// remove the temporary file and folder
				unlink('tmp/test.php');
				rmdir('tmp');
			}

		}
		
		/**
		 * Checks if 777 permissions will work.
		 *
		 * @return void
		 **/
		function check777() {
			
			// if we cannot exec, show warning
			if($GLOBALS['mc']->getTestStatus('PHP Scripting Test', 'checkFunctions') !== 1) {
				$this->results->test('check777', '777 Permissions allowed', 'Can\'t determine', 0);
				return;
			}
			
			// create temp directory
			$out = mkdir('tmp', octdec('0775'));	
							
			// create a temp file that we can read over http.
			$fileHandle = fopen('tmp/test.php', 'w+');
			$bla = fwrite($fileHandle, '<?php echo "Hello World"; ?>');
			fclose($fileHandle);
			
			// get headers for the file we just created
			$bHeaders = $this->getHeaders($GLOBALS['scriptPath'] . "tmp/test.php");
			
			// chmod the file to 777
			$ret = exec('chmod 777 tmp/test.php');
			
			// get headers for the chmodded file
			$aHeaders = $this->getHeaders($GLOBALS['scriptPath'] . "tmp/test.php");

			// compare both headers and pass test if they are the same
			if($bHeaders[0] == $aHeaders[0]) {
				$this->results->test('check777', '777 Permissions allowed', 'Yes', 1);
			} else {
				$this->results->test('check777', '777 Permissions allowed', 'No', 1);
			}
			
			// remove the temporary file and folder
			unlink('tmp/test.php');
			rmdir('tmp');
			
		}

		/**
		 * Check whether symlinks work
		 *
		 * @return void
		 **/
		function symlinks() {
			
			$perms = $GLOBALS['mc']->getTestValue('File Permissions Test', 'checkTempPermissions');

			$out = mkdir('tmp', octdec($perms));		

			// create a temp file that we can read over http.
			$fileHandle = fopen('tmp/test.php', 'w+');
			$bla = fwrite($fileHandle, '<?php echo "Hello World"; ?>');
			fclose($fileHandle);
			
			// now create a symlink to the file to check whether that works
			$funcExists = function_exists('symlink');
			if($funcExists) {
				$sym = symlink('test.php', 'tmp/symtest.php');	
			} else {
				$sym = false;
			}
					
			// get headers for the file and symlink we just created
			$sHeaders = $this->getHeaders($GLOBALS['scriptPath'] . "tmp/symtest.php");
			$headers = $this->getHeaders($GLOBALS['scriptPath'] . "tmp/test.php");
			$headers500 = strpos($sHeaders[0], '500') !== false;	
			$phpsuexec = $this->results->getStatus('check777');
			
			// check symlink
			if(!$sym && $funcExists) {
				$recom = 'Symlinks couldn\'t be created.';
				$this->results->test('symlinks', 'Symlinks', 'Problem', -1, $recom);
			} else if (!$sym && !$funcExists) {
				$recom = 'Symlinks couldn\'t be created because the PHP function \'symlink\' is not available.';
				$this->results->test('symlinks', 'Symlinks', 'Problem', -1, $recom);	
			} else if ($sym && $phpsuexec) {
				if($GLOBALS['t3installed']) {
					$this->results->test('symlinks', 'Symlinks', 'Success', 1);					
				} else {
					$recom = 'When you install TYPO3, either use the .zip package or make sure that you copy the 
					index.php file from typo3_src to the TYPO3 root.';
					$this->results->test('symlinks', 'Symlinks', 'Success', 0, $recom);
				}

			} else if ($sym && !$headers500) {
				$this->results->test('symlinks', 'Symlinks', 'Success', 1);					
			} else if ($sym && $headers500) {
				$recom = 'Symlinks can be created, but symlinked files cannot be read. The 
					reason for this might be that PHPsuExec is installed.';
				$this->results->test('symlinks', 'Symlinks', 'Problem', 0, $recom);
			}
			
			// remove the temporary file and folder
			unlink('tmp/test.php');
			unlink('tmp/symtest.php');
			rmdir('tmp');
		}
	}
	
	/**
	 * This class tests the apache environment for mod_rewrite and .htaccess stuff.
	 *
	 * @author Web-Empowered Church Team <developer@webempoweredchurch.org>
	 **/
	class Apache extends Module {
		
		/**
		 * Constructor
		 *
		 **/
		function __construct() {
			parent::__construct();
			
			$this->title = 'Apache Web Server Test';
		}
		
		function check() {
			if($GLOBALS['mc']->getTestStatus('File Permissions Test', 'checkTempPermissions') === 1) {
				$this->checkVersion();
				$this->checkModRewrite();
				$this->checkModSecurity();
				$this->override();
				$this->checkRewrite();				
			}

		}
		
		function evaluate() {
			
			// if there are file permissions errors, don't bother doing these tests
			if($GLOBALS['mc']->getTestStatus('File Permissions Test', 'checkTempPermissions') !== 1) {
				$this->results->overall(-1, 'Please fix the file permissions first.', false);
				return;
			}
			
			$isApache = ($GLOBALS['mc']->getTestValue('PHP Scripting Test', 'checkServerAPI') == 'apache' 
				|| $GLOBALS['mc']->getTestValue('PHP Scripting Test', 'checkServerAPI') == 'apache2handler'
				|| $GLOBALS['mc']->getTestValue('PHP Scripting Test', 'checkServerAPI') == 'cgi'
			);
			$allgood = ($isApache && 
				$this->results->getStatus('checkModRewrite') == 1 && 
				$this->results->getStatus('checkRewrite') == 1 && 
				$this->results->getStatus('override') == 1
			);
			$noApacheRewrite = ($this->results->getStatus('checkRewrite') == 1 && !$isApache);
			$ApacheNoRewrite = ($this->results->getStatus('checkRewrite') != 1 && $isApache);
			
			// apache but mod_rewrite not found
			if($allgood) {
				$this->results->overall(1, 'The Apache Web Server and the RealURL extension should work correctly.', false);
				return;
			}

			if ($isApache && $this->results->getStatus('checkModRewrite') == -1) {
				$recom = "The mod_rewrite module is not installed. It's necessary for the RealURL extension, so if you are
					having problems with your TYPO3 site URLs, try uninstalling the extension in the extension manager.";
				$this->results->overall(-1, $recom, false);

			} else if($isApache && $this->results->getStatus('checkModRewrite') == 0 && $this->results->getStatus('checkRewrite') != 1) {
				$recom = "The mod_rewrite module could not be found. It's necessary for the RealURL extension, so if you are
					having problems with your TYPO3 site URLs, try uninstalling the extension in the extension manager.";
				$this->results->overall(0, $recom, false);
			
			} else if($isApache && $this->results->getStatus('checkModRewrite') == 0 && $this->results->getStatus('checkRewrite') == 1) {
				$this->results->overall(1, 'All works well.', false);
					
			} else if($isApache && $this->results->getStatus('checkModRewrite') == 1) {
					$this->results->overall(1);
				
			} else if($noApacheRewrite) {
				$recom = 'Even though you are not using Apache, rewriting URLs works fine!';
				$this->results->overall(1, $recom, false);
			} else if($ApacheNoRewrite) {
				$recom = 'For some odd reason rewrite didn\'t work. Please report this to the WEC Support forum.';
				$this->results->overall(-1, $recom);
			} else {
				$recom = 'Rewriting URLs didn\'t work at all. Please report this to the WEC Support forum.';
				$this->results->overall(-1, $recom);
			}
			
			if ($isApache && $this->results->getStatus('override') != 1) {
				$recom = "Overriding settings via .htaccess files is not allowed but required for RealURL to work.
					Please check that AllowOverride All is set in your Apache config or ask your host to do so.";
				$this->results->overall(-1, $recom, false);
			}
		}
		
		/**
		 * Checks for mod_security
		 *
		 * @return void
		 **/
		function checkModSecurity() {
		
			// only do this if we can use apache php functions, i.e. PHP
			// is not running as CGI
			if(function_exists('apache_get_modules') && in_array('mod_security', apache_get_modules())) {
				$recom = 'mod_security isn\'t compatible with some of TYPO3\'s features. Try adding the line "SecFilterEngine Off"
					without quotes to the .htaccess file in your TYPO3 root.';
				$this->results->test('checkModSecurity', 'mod_security', 'present', 0, $recom);
			} else {
				$this->results->test('checkModSecurity', 'mod_security', 'not found', 1);
			}
		}
		
		/**
		 * Checks for the presence of mod_rewrite.
		 *
		 * @return void
		 **/
		function checkModRewrite() {
			
			// only do this if we can use apache php functions, i.e. PHP
			// is not running as CGI
			if(function_exists('apache_get_modules')) {
				
				if (in_array('mod_rewrite', apache_get_modules())) {
					$this->results->test('checkModRewrite', 'mod_rewrite', 'present', 1);		
				} else {
					$this->results->test('checkModRewrite', 'mod_rewrite', 'not found', -1);
				}
			} else {
				$this->results->test('checkModRewrite', 'mod_rewrite', 'unable to determine this setting due to the server configuration', 0);		
			}
		}
		
		/**
		 * Returns the Apache version.
		 *
		 * @return void
		 **/
		function checkVersion() {
            $exploded = explode(' ', $_SERVER['SERVER_SOFTWARE']);

            foreach($exploded as $single) {
                if(strpos($single, 'Apache') !== false) {
                    $apache = explode('/', $single);
                    $version = $apache[1];
					if(empty($version)) {
						$recom = 'Apache version couldn\'t be determined, probably because Apache is configured not
							to display its version information. This is usually okay.';
						$this->results->test('checkVersion', 'Version', 'not found', 0, $recom);
					} else {
                    	$this->results->test('checkVersion', 'Version', $version, 1);
					}
                }
            }
		}
		
		/**
		 * Checks whether .htaccess files are allowed.
		 *
		 * @return void
		 **/
		function checkRewrite() {

			$perms = $GLOBALS['mc']->getTestValue('File Permissions Test', 'checkTempPermissions');

			$out = mkdir('test123', octdec($perms));

			// this goes into the .htaccess file
			$htaccess = "<IfModule mod_rewrite.c> \n
			Options +FollowSymlinks \n
			RewriteEngine On \n
			RewriteRule ^test.php rewrite_test.php \n
			</IfModule>";
			
			// and this goes into our php file
			$php = '<?php echo "Hello World!"; ?>';
			
			// write our htaccess file
			$fileHandle = fopen('test123/.htaccess', 'w+');
			$bla = fwrite($fileHandle, $htaccess);
			fclose($fileHandle);
			
			// write our php file
			$fileHandle = fopen('test123/rewrite_test.php', 'w+');
			$bla = fwrite($fileHandle, $php);
			fclose($fileHandle);
			
			// Now check headers on the real file...
			$rheaders = $this->getHeaders($GLOBALS['scriptPath'] . 'test123/rewrite_test.php');
			
			// .. and the virtual file
			$vheaders = $this->getHeaders($GLOBALS['scriptPath'] . 'test123/test.php');
			
			// if we get a 200 OK and the headers are the same, it worked!
			if(strpos($rheaders[0], '200 OK') && $rheaders[0] == $vheaders[0]) {
				$this->results->test('checkRewrite', 'Rewrite URLs', 'success', 1);

			} else {
				$recom = "Rewriting URLs failed.";
				$this->results->test('checkRewrite', 'Rewrite URLs', 'Failed', -1, $recom);
			}
		
			// clean up
			unlink('test123/.htaccess');
			unlink('test123/rewrite_test.php');
			rmdir('test123');
		}
		
		/**
		 * Makes sure overriding settings with .htaccess is allowed
		 *
		 * @return void
		 **/
		function override() {
			
			$perms = $GLOBALS['mc']->getTestValue('File Permissions Test', 'checkTempPermissions');
			
			$out = mkdir('test123', octdec($perms));
			
			// write empty index.html file
			$fileHandle = fopen('test123/index.html', 'w+');
			$bla = fwrite($fileHandle, '');
			fclose($fileHandle);
			
			// Now check headers without .htaccess file
			$bHeaders = $this->getHeaders($GLOBALS['scriptPath'] . 'test123/index.html');

			// syntax error in .htaccess
			$htaccess = "SecFEng On";
			
			// write our htaccess file
			$fileHandle = fopen('test123/.htaccess', 'w+');
			$bla = fwrite($fileHandle, $htaccess);
			fclose($fileHandle);
			
			// check headers with .htaccess file
			$aHeaders = $this->getHeaders($GLOBALS['scriptPath'] . 'test123/index.html');

			// if the headers are different, overriding via .htaccess should work
			if($aHeaders[0] != $bHeaders[0]) {
				$this->results->test('override', 'Allow Override', 'Success', 1);
			} else {
				$recom = 'Overriding Apache settings with .htaccess files is not allowed.';
				$this->results->test('override', 'Allow Override', 'Failed', -1, $recom);
			}
			
			// clean up
			unlink('test123/.htaccess');
			unlink('test123/index.html');
			rmdir('test123');	
		}
	}

	/**
	 * Specific TYPO3 tests, like checking permissions in various folders and whether default configuration
	 * has been changed.
	 *
	 * @author Web-Empowered Church Team <developer@webempoweredchurch.org>
	 **/
	class TYPO3 extends Module {
		
		/**
		 * Constructor
		 *
		 **/
		function __construct() {
			parent::__construct();
			
			$this->title = 'TYPO3 Test';
		}
		
		function check() {

			$this->checkBaseTag();
			$this->checkHtaccess();
			$this->checkRealURL();
			$this->rootSym();
			$this->checkFileadmin();
			$this->checkUploads();
			$this->checkTypo3temp();
			$this->checkTypo3conf();
		}
		
		function evaluate() {
			
			$allgood = ($this->results->getStatus('checkBaseTag') == 1 && 
				$this->results->getStatus('checkHtaccess') == 1 &&
				$this->results->getStatus('checkRealURL') == 1 &&
				$this->results->getStatus('checkFileadmin') == 1 &&
				$this->results->getStatus('checkUploads') == 1 &&
				$this->results->getStatus('checkTypo3temp') == 1 &&
				$this->results->getStatus('checkTypo3conf') == 1 &&
				$this->results->getStatus('rootSym') == 1
			);
			
			$realurlhtthere = ($GLOBALS['mc']->getTestStatus('Apache Web Server Test','checkRewrite') == 1 && 
				$this->results->getStatus('checkHtaccess') == 1 &&
				$this->results->getStatus('checkRealURL') != 1
			);
			$realurlhtnotthere = ($GLOBALS['mc']->getTestStatus('Apache Web Server Test','checkRewrite') == 1 && 
				$this->results->getStatus('checkHtaccess') != 1 &&
				$this->results->getStatus('checkRealURL') != 1
			);
			$directories = ($this->results->getStatus('checkFileadmin') == 1 && 
				$this->results->getStatus('checkUploads') == 1 &&
				$this->results->getStatus('checkTypo3temp') == 1 &&
				$this->results->getStatus('checkTypo3conf') == 1
			);
			$rootSym = $this->results->getStatus('rootSym') == 1;
			$rootSymWarn = $this->results->getStatus('rootSym') == 0;
			// if everything is good, say it and quit
			if ($allgood) {
				$this->results->overall(1, 'Everything is alright');
				return;
			} 
			
			if($rootSym) {
				$this->results->overall(1);
			} else if($rootSymWarn) {
				$recom = 'Internal Server Error when trying to read the TYPO3 main page.';
				$this->results->overall(0, $recom);
			} else {
				$recom = 'The symlink to index.php could not be read, probably because PHPsuexec is installed. Follow the
				 instructions below to try and fix this.';
				$this->results->overall(-1, $recom);
			}
			
			// eval realurl results
			if ($realurlhtthere) {
				$recom = "RealURL didn't work because the wrong .htaccess file is being used. Make sure you
					copied the correct .htaccess file from the WEC Starter Package to your TYPO3 root directory.";
				$this->results->overall(-1, $recom);
			} else if ($realurlhtnotthere) {
				$recom = "RealURL didn't work because the .htaccess file is missing. Please copy
				 it from the WEC Starter Package to your TYPO3 root directory.";
				$this->results->overall(-1, $recom);
			}
			
			// eval directory results
			if(!$directories) {
				$recom = 'Some directories weren\'t writable. Please check the directory permissions.';
				$this->results->overall(-1, $recom);
			}
			
		}
		
		/**
		 * Checks for the base tag in the TYPO3 install to see if it has been changed from the default.
		 *
		 * @return void
		 **/
		function checkBaseTag() {
			
			// open the site url, as defined above, and read
			// the first few kb of it.
			$handle = fopen($GLOBALS['TYPO3WebPath'], 'r');
			$site = fread($handle, 8192);

			// search for the base tag url
			preg_match('!.*<base href="(.*)".*>!', $site, $output);
			
			// compare the base tag url with the default, which should have been changed.
			if($output[1] == 'http://demo.webempoweredchurch.org/') {
				$recom = 'The base tag is still at its default value and needs to be changed
					to the address of your TYPO3 installation.';
				$this->results->test('checkBaseTag', 'Base Tag', $output[1], -1, $recom);
			} else if (empty($output[1])) {
				$recom = 'The base tag is empty and needs to be changed
					to the address of your TYPO3 installation.';
				$this->results->test('checkBaseTag', 'Base Tag', $output[1], -1, $recom);
			} else {
				$this->results->test('checkBaseTag', 'Base Tag', $output[1], 1);				
			}
		}
		
		/**
		 * Checks whether the .htaccess file is present.
		 *
		 * @return void
		 **/
		function checkHtaccess() {
			if(file_exists($GLOBALS['TYPO3Path'] . '.htaccess' )) {
				$this->results->test('checkHtaccess', '.htaccess file', 'found', 1);
			} else {
				$recom = 'The .htaccess file could not be found in your TYPO3 root directory. Please make sure
					you copied it correctly from the WEC Starter Package to your web host.';
				$this->results->test('checkHtaccess', '.htaccess file', 'not found', -1, $recom);
			}
		}
		
		/**
		 * Checks if the root page can be read via symlink.
		 *
		 * @return void
		 **/
		function rootSym() {
						
			// get headers for TYPO3 root
			$headers = $this->getHeaders($GLOBALS['scriptPath']);
			$headers500 = strpos($headers[0], '500') !== false;	

			// check headers
 			if (!$headers500) {
				$this->results->test('rootSym', 'TYPO3 index.php', 'Success', 1);					
			} else if ($headers500 && $GLOBALS['mc']->getTestResult('File Permissions Test', 'check777') == 'No') {
				$recom = 'Copy index.php from the typo3/ directory into the TYPO3 root directory.';
				$this->results->test('rootSym', 'TYPO3 index.php', 'Problem', -1, $recom);
			} else {
				$recom = 'There was a Internal Server Error not caused by symlinks. Check the other tests
				 for details.';
				$this->results->test('rootSym', 'TYPO3 index.php', 'Problem', 0, $recom);
			}
		}
		/**
		 * Checks if rewriting in TYPO3 works.
		 *
		 * @return void
		 **/
		function checkRealURL() {

			// get the Learn & Grow page normally
			$fileHandle = fopen($GLOBALS['TYPO3WebPath'] . 'index.php?id=77', 'r');
			$norm = fread($fileHandle, 8192);
			fclose($fileHandle);
			
			// get the Learn & Grow page rewritten
			$fileHandle = fopen($GLOBALS['TYPO3WebPath'] . 'learn_grow/', 'r');
			$rewr = fread($fileHandle, 8192);
			fclose($fileHandle);
						
			// Now check headers on the normal page...
			$rheaders = $this->getHeaders($GLOBALS['TYPO3WebPath'] . 'index.php?id=77');

			// .. and the rewritten page
			$vheaders = $this->getHeaders($GLOBALS['TYPO3WebPath'] . 'learn_grow/');
						
			// if we get a 200 OK and the headers are the same plus the content of both pages is
			// identical, it worked
			if(strpos($rheaders[0], '200 OK') && $rheaders[0] == $vheaders[0] && $norm == $rewr) {
				$this->results->test('checkRealURL', 'RealURL', 'success', 1);
			
			// if we don't get a 200 OK (i.e. 302 or 404), show a warning
			} else if (strpos($rheaders[0], '200 OK') === false) {
				$recom = 'Test couldn\'t run. The wrong page identifier was used. 
					Please report this issue on the WEC Support forums.';
				$this->results->test('checkRealURL', 'RealURL', 'failed', 0, $recom);
			
			// just a fail safe.
			} else {
				$this->results->test('checkRealURL', 'RealURL', 'Failed', -1, 'Unknown error. Headers: ' . $rheaders[0] . '<br />' . $vheaders[0]);
			}
		}
		
		/**
		 * Checks read and write for fileadmin inside TYPO3
		 *
		 * @return void
		 **/
		function checkFileadmin() {
			
			// create paths
			$path = $GLOBALS['TYPO3Path'] . 'fileadmin' . '/';
			$webPath = $GLOBALS['TYPO3WebPath'] . 'fileadmin' . '/';
			
			// file content
			$file = '<?php echo "Hello World!"; ?>';
			
			// open file
			$link = fopen($path . 'test.php', 'w+');
			
			$write = fwrite($link, $file);
			
			// if it couldn't be written, display a warning
			if($write === false) {
				$recom = 'Could not write to the fileadmin directory. Please check the directory permissions.';
				$this->results->test('checkFileadmin', 'fileadmin', 'not writable', 0, $recom);
				return null;
			}
			
			fclose($link);
					
			// now check headers on the just created file
			$headers = $this->getHeaders($webPath . 'test.php');
			if(strpos($headers[0], '200 OK')) {
				$this->results->test('checkFileadmin', 'fileadmin', 'readable and writable', 1);
			} else {
				$recom = 'File couldn\'t be read. Please check file permissions.';
				$this->results->test('checkFileadmin', 'fileadmin', 'Could not access file over HTTP.', 0, $recom);
			}
			
			unlink($path . 'test.php');
			
		}
		
		/**
		 * Checks read and write for typo3conf inside TYPO3
		 *
		 * @return void
		 **/
		function checkTypo3conf() {
			
			// create paths
			$path = $GLOBALS['TYPO3Path'] . 'typo3conf' . '/';
			$webPath = $GLOBALS['TYPO3WebPath'] . 'typo3conf' . '/';
			
			// file content
			$file = '<?php echo "Hello World!"; ?>';
			
			// open file
			$link = fopen($path . 'test.php', 'w+');
			
			$write = fwrite($link, $file);
			
			// if it couldn't be written, display a warning
			if($write === false) {
				$recom = 'Could not write to the typo3conf directory. Please check the directory permissions.';
				$this->results->test('checkTypo3conf', 'typo3conf', 'not writable', 0, $recom);
				return null;
			}
			
			fclose($link);
					
			// now check headers on the just created file
			$headers = $this->getHeaders($webPath . 'test.php');
			if(strpos($headers[0], '200 OK')) {
				$this->results->test('checkTypo3conf', 'typo3conf', 'readable and writable', 1);
			} else {
				$recom = 'File couldn\'t be read. Please check file permissions.';
				$this->results->test('checkTypo3conf', 'typo3conf', 'Could not access file over HTTP.', 0, $recom);
			}
			
			unlink($path . 'test.php');
			
		}
		
		/**
		 * Checks read and write for typo3temp inside TYPO3
		 *
		 * @return void
		 **/
		function checkTypo3temp() {
			
			// create paths
			$path = $GLOBALS['TYPO3Path'] . 'typo3temp' . '/';
			$webPath = $GLOBALS['TYPO3WebPath'] . 'typo3temp' . '/';
			
			// file content
			$file = '<?php echo "Hello World!"; ?>';
			
			// open file
			$link = fopen($path . 'test.php', 'w+');
			
			$write = fwrite($link, $file);
			
			// if it couldn't be written, display a warning
			if($write === false) {
				$recom = 'Could not write to the typo3temp directory. Please check the directory permissions.';
				$this->results->test('checkTypo3temp', 'typo3temp', 'not writable', 0, $recom);
				return null;
			}
			
			fclose($link);
					
			// now check headers on the just created file
			$headers = $this->getHeaders($webPath . 'test.php');
			if(strpos($headers[0], '200 OK')) {
				$this->results->test('checkTypo3temp', 'typo3temp', 'readable and writable', 1);
			} else {
				$recom = 'File couldn\'t be read. Please check file permissions.';
				$this->results->test('checkTypo3temp', 'typo3temp', 'Could not access file over HTTP.', 0, $recom);
			}
			
			unlink($path . 'test.php');	
		}
		
		/**
		 * Checks read and write for uploads inside TYPO3
		 *
		 * @return void
		 **/
		function checkUploads() {
			
			// create paths
			$path = $GLOBALS['TYPO3Path'] . 'uploads' . '/';
			$webPath = $GLOBALS['TYPO3WebPath'] . 'uploads' . '/';
			
			// file content
			$file = '<?php echo "Hello World!"; ?>';
			
			// open file
			$link = fopen($path . 'test.php', 'w+');
			
			$write = fwrite($link, $file);
			
			// if it couldn't be written, display a warning
			if($write === false) {
				$recom = 'Could not write to the uploads directory. Please check the directory permissions.';
				$this->results->test('checkUploads', 'uploads', 'not writable', 0, $recom);
				return null;
			}
			
			fclose($link);
					
			// now check headers on the just created file
			$headers = $this->getHeaders($webPath . 'test.php');
			if(strpos($headers[0], '200 OK')) {
				$this->results->test('checkUploads', 'uploads', 'readable and writable', 1);
			} else {
				$recom = 'File couldn\'t be read. Please check file permissions.';
				$this->results->test('checkUploads', 'uploads', 'Could not access file over HTTP.', 0, $recom);
			}
			
			unlink($path . 'test.php');
			
		}
	}
	
	//-----------------------------------
	//|		Pull everything together	|
	//-----------------------------------
	
	// register renderers
	$rc->register('OverallResults');
	$rc->register('RenderDetailed');
	$rc->register('RenderPlain');

	// register modules
	$mc->register('PHP');
	$mc->register('MySQL');
	$mc->register('FilePermissions');
	$mc->register('Apache');
	if($GLOBALS['t3installed']) $mc->register('TYPO3');	
	
	// turn off error reporting. After all, that's what we're doing here.
	error_reporting(0);

	// run all the tests
	$mc->runAll();

	// pass the results to the render controller
	$rc->setResults($mc->getResults());

	// now render everything
	echo $rc->render();
?>