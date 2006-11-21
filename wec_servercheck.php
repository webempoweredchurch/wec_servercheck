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
	// dbHost: it's probably okay to leave at localhost
	// dbUser: the database user that typo3 uses
	// dbPass: the password for dbUser
	$GLOBALS['dbHost'] = 'localhost';
	$GLOBALS['dbUser'] = 'root';
	$GLOBALS['dbPass'] = '';
	
	// Path to this script from a web browser. 
	// Don't forget the trailing slash.
	$GLOBALS['scriptPath'] = 'http://localhost/typo3/wec_servercheck/';


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
		
		/**
		 * Runs the given module if it hasn't run before and saves the results in an array.
		 *
		 * @return void
		 **/
		function run($module) {
			$moduleName = strtolower($module);
			if(!array_key_exists($moduleName, $this->results)) {
				$cur = new $module;
				$this->results[$moduleName]['tests'] = $cur->getOutput();
				$this->results[$moduleName]['title'] = $cur->getTitle();
			}
			// print_r($this->results);
		}
		
		/**
		 * Runs all modules and keeps track of dependencies.
		 *
		 * @return void
		 **/
		function runAll() {
			foreach($this->modules as $module) {
				$this->run($module);
			}
			
		}
		
		/**
		 * Returns the test result for a particular test
		 * 
		 * @param $test The name of the main module
		 * @param $subtest The name of the subtest, like 'Version' etc.
		 * @return String
		 **/
		function getTestResult($test, $subtest) {
		
			// get name of the test in lower case to compare with array key
			$testName = strtolower($test);
			
			// if the array key already exists, the test has already run, so just return the result.
			// if not, run the test first.
			if(!isset($results[$testName][$subtest])) {
				$this->run($test);
			}
			return $this->results[$testName]['tests'][$subtest]['value'];
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
	$GLOBALS['MC'] = $mc;


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
	
		// Table Row
		var $ROW = '<tr><td>%s</td><td><pre>%s</pre></td><td>%s</td></tr>';

		// Recommendation Row
		var $RROW = '<tr class="recomrow"><td colspan="3">%s</td></tr>';
		
		// Table title
		var $TITLE = '<th colspan="3">%s</th>';
		
		
		/**
		 * Renders all the modules from given results array
		 *
		 * @param $results Array that contains all the data for our tests.
		 * @return String
		 **/
		function renderAll($results) {
			$output = '<html><head><title>WEC Server Checker</title></head><body>
			
			<style type="text/css">
				.tablefield {
					width: 200px;
					font-weight: bold;
					border-bottom: 1px solid black;
				}
				
				th {
					border-bottom: 1px black solid;
				}
				.recomrow td{
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

			
			foreach($results as $module) {
				$output .= $this->render($module['tests'], $module['title']);
			}
			
			$note = '<strong>Note:</strong> If you know that any of these test results are wrong, please post your test results and corrections in the Install forum on www.webempoweredchurch.com. Thank you!';
			$output .= '<div style="width: 600px;">' . $note . '</div>';
			
			
			$output .= '</body></head>';
			
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
			foreach($testData as $key => $value) {
				$status = $this->getStatus($value['status']);
				$show .= sprintf($this->ROW, $key, $value['value'], $status);
				if(isset($value['recommendation'])) {
					$show .= sprintf($this->RROW, $value['recommendation']);	
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
		var $mc;

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
			$this->mc = $GLOBALS['MC'];
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
		
		/**
		 * Fills in the result array.
		 *
		 * @return void
		 **/
		function message($name, $value, $status, $recommendation = null) {
			$this->output[$name]['value'] = $value;
			$this->output[$name]['status'] = $status;
			(empty($recommendation)) ? null : $this->output[$name]['recommendation'] = $recommendation;
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
			$this->checkUploadLimit();
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
			
			// if it's PHP 4 or 5, we should be good, otherwise display error.
			if($majorVersion == 4 || $majorVersion == 5) {
				$this->message('Version', $version, 1);
			} else {
				$this->message('Version',$version, -1, "PHP Version is too low!");				
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
			if($api == 'cgi' || $api == 'apache') {
				$this->message('Server API', $api, 1);
			} else {
				$this->message('Server API', $api, 0, 'Unknown Server API');
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
			if($os == 'Linux' || $os == 'Darwin' || strtoupper(substr($os, 0, 3)) === 'WIN') {
				$this->message('OS', $os, 1);
			} else {
				$this->message('OS', $os, 0, 'Unknown Operating System');
			}
		}
		
		/**
		 * Checks the memory limit.
		 * TODO: compare memory limit in bytes
		 * @return void
		 **/
		function checkMemoryLimit() {
	
			// get memory limit
			$mlimit = ini_get('memory_limit');
			
			// if no memory limit is returned (as might be the case in Darwin), add our 
			// own value and show a warning.
			if(empty($mlimit)) {
				$recom = 'The memory limit could not be determined. This is okay if you are on Mac OS X,
					but please make sure the memory limit is at least 32M.';
				$this->message('Memory Limit', "N/A", 0, $recom);
			} else {
				
				$this->message('Memory Limit', $mlimit, 1);
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
			
			// good value = default in Typo3 is 10M
			$good = '10M';
			$bad = '2M';
			
			// convert both to bytes
			$bytes = $this->returnBytes($ulimit);
			$gbytes = $this->returnBytes($good);
			$bbytes = $this->returnBytes($bad);
			
			// if more than good, all good.
			// else if lower than min, fail.
			// else it could still be okay, warning.
			if($bytes >= $gbytes) {
				$this->message("Max Upload Filesize", $ulimit, 1);
			} else if ($bytes < $bbytes) {
				$recom = 'Max upload file size is much lower than the TYPO3 default. Please raise it to at least 10M.';
				$this->message("Max Upload Filesize", $ulimit, -1, $recom);
			} else {
				$recom = 'Max upload file size is lower than the TYPO3 default. Consider raising it to 10M.';
				$this->message("Max Upload Filesize", $ulimit, 0, $recom);
			}
			
		}
		
		/**
		 * Takes PHP style number and converts to byte.
		 *
		 * @return int
		 **/
		function returnBytes($val) {
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
			
			$this->title = 'File Permissions';
		}
		
		function check() {
			$this->checkW();
			$this->checkR();
		}
		
		/**
		 * Checks which permissions are needed to read, write, and execute files.
		 *
		 * @return void
		 **/
		function checkW() {
			
			// define permissions we want to test
			$perms = array('0777', '0775', '0755', '0744');

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
					$this->message('Minimum write permissions', "N/A", -1, 'Could not create temporary folder;check permissions.');
					return;
				}
				
				// if the previous did work, create a temp file and get the return value
				$test = touch('tmp/test.php');

				// if write was successful, save the file permissions in the results. It will 
				// overwrite this until one of them doesn't work, and leave it at the minimum
				// that did work which is exactly what we want.
				if($test) $this->message('Minimum write permissions', $perm, 1);

				// remove the temporary file and folder
				unlink('tmp/test.php');
				rmdir('tmp');
			}

		}
		
		/**
		 * Creates a temporary file and tries to read it over HTTP.
		 *
		 * @return void
		 **/
		function checkR() {
			  // define permissions we want to test
			$perms = array('0777', '0775', '0755', '0744');

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
			
			foreach($perms as $perm) {
			
				$out = mkdir('tmp', octdec($perm));			

				// if that didn't work we have a problem
				if(!$out) {
					$this->message('Minimum write permissions', "N/A", -1, 'Could not create temporary folder;check permissions.');
					return;
				}

				// if the previous did work, create a temp file that we can read over http.
				$fileHandle = fopen('tmp/test.php', 'w+');
				$bla = fwrite($fileHandle, '<?php echo "Hello World"; ?>');
				fclose($fileHandle);
				
				// now create a symlink to the file to check whether that works
				$sym = symlink('test.php', 'tmp/symtest.php');
			
				// get headers for the file and symlink we just created
				$sHeaders = get_headers($GLOBALS['scriptPath'] . "tmp/symtest.php");
				$headers = get_headers($GLOBALS['scriptPath'] . "tmp/test.php");

				// check for good headers from file, if they are, output, if not, it's bad!
				if(stripos($headers[0], "200 OK") !== false) {
					$this->message('Minimum read permissions', $perm, 1);
				} else {
					$this->message('Minimum read permissions', $perm, -1, "Reading file failed.");
				}


				// check symlink:
				// if no symlink was created and this is windows show warning.
				if(!$sym && stripos('win', $this->mc->getTestResult('PHP', 'OS'))) {
					$recom = 'Symlinks couldn\'t be created. This is probably okay since you are using Windows.';
					$this->message('Symlinks', 'Problem', 0, $recom);
				
				// no symlink was created, but we aren't using Windows; that's not good.
				} else if(!$sym && !stripos('win', $this->mc->getTestResult('PHP', 'OS'))) {
					$recom = 'Symlinks couldn\'t be created.';
					$this->message('Symlinks', 'Problem', -1, $recom);

				// symlink is there and header is good
				} else if ($sym && stripos($sHeaders[0], "200 OK") !== false) {
					$this->message('Symlinks', 'Success', 1);
				
				// symlink is there but couldn't be read
				} else {
					$this->message('Symlinks', 'Problem', -1, "Reading symlink failed.");
				}
				
				// remove the temporary file and folder
				unlink('tmp/test.php');
				unlink('tmp/symtest.php');
				rmdir('tmp');
			}
		}
		
	}
	$mc->register('FilePermissions');
	
	//-----------------------------------
	//|			Nitty Gritty			|
	//-----------------------------------
	
	// turn off error reporting. After all, that's what we're doing here.
	error_reporting(0);
	
	$mc->runAll();
	echo $renderer->renderAll($mc->getResults());
?>