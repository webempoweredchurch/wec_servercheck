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
	// relativePath: The relative path to the TYPO3 installation. If TYPO3 is installed in a 
	//		subfolder, put '/subfolder/', if it's installed in the website root, leave empty.
	$GLOBALS['dbHost'] = 'localhost';
	$GLOBALS['dbUser'] = 'root';
	$GLOBALS['dbPass'] = '';
	$GLOBALS['relativePath'] = '/wec/';
	
	// !!!! DON'T EDIT ANYTHING BEYOND THIS LINE !!!!





	//-----------------------------------
	//|			Misc Options			|
	//-----------------------------------
	
	// Path to this script from a web browser, without the name of the script. 
	$GLOBALS['scriptPath'] = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . 
		substr($_SERVER['REQUEST_URI'], 0, -19);
	
	// TYPO3 path in the file system.
	$GLOBALS['TYPO3Path'] = $_SERVER['DOCUMENT_ROOT'] . $GLOBALS['relativePath'];
	
	// TYPO3 path from a browser.
	$GLOBALS['TYPO3WebPath'] = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] .
		$GLOBALS['relativePath'];
		
	// fix relative path in case it's empty
	if(empty($GLOBALS['relativePath'])) $GLOBALS['relativePath'] = '/';
 	
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
			print_r($this->results);
			
		}
		
		/**
		 * Returns the value for a particular test
		 * 
		 * @param $test The name of the main module
		 * @param $subtest The name of the subtest, like 'Version' etc.
		 * @return String
		 **/
		function getTestValue($test, $subtest) {
		
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
		 * Returns the status for a particular test
		 * 
		 * @param $test The name of the main module
		 * @param $subtest The name of the subtest, like 'Version' etc.
		 * @return String
		 **/
		function getTestStatus($test, $subtest) {
		
			// get name of the test in lower case to compare with array key
			$testName = strtolower($test);
			
			// if the array key already exists, the test has already run, so just return the result.
			// if not, run the test first.
			if(!isset($results[$testName][$subtest])) {
				$this->run($test);
			}
			return $this->results[$testName]['tests'][$subtest]['status'];
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
		
		// plain row
		var $PROW = "%s \t\t %s \t\t %s\n";
		
		// Recommendation Rows
		var $PRROW = "%s\n";
		var $RROWW = '<tr class="recomrowwarn"><td colspan="3">%s</td></tr>';
		var $RROWF = '<tr class="recomrowfail"><td colspan="3">%s</td></tr>';
				
		// Table title
		var $PTITLE = "\n-= %s =-\n";
		var $TITLE = '<th colspan="3">%s</th>';
		
		
		/**
		 * Renders all the modules from given results array
		 *
		 * @param $results Array that contains all the data for our tests.
		 * @return String
		 **/
		function renderAll($results) {
			$plain = null;
			$output = '<html><head><title>WEC Server Checker</title></head><body>
			
			<style type="text/css">
				.tablefield {
					width: 230px;
					font-weight: bold;
					border-bottom: 1px solid black;
				}
				
				th {
					border-bottom: 1px black solid;
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

			
			foreach($results as $module) {
				$output .= $this->render($module['tests'], $module['title']);
				$plain .= $this->renderPlain($module['tests'], $module['title']);
			}
			
			
			$note = '<strong>Note:</strong> If you know that any of these test results are wrong, please post your test results and corrections in the <a href="http://webempoweredchurch.com/support/community/">"Installing" forum on the WEC website</a>. Thank you!';
			$output .= '<div style="width: 600px;">' . $note . '</div>';
			
			$output .= '<p>Copy and paste the contents of the textarea below into emails or forum posts.<br />';
			$output .= '<textarea cols="100" rows="20">';
			$output .= $plain;
			$output .= '</textarea></p>';
			$output .= '</body></head>';
			
			return $output;
		}
		
		/**
		 * Renders the module results in plain text.
		 *
		 * @return void
		 **/
		function renderPlain($testData, $title) {
			$show = sprintf($this->PTITLE, $title);

			foreach($testData as $key => $value) {
				$status = $this->getPlainStatus($value['status']);
				$length1 = strlen($key);
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

				$show .= $key . $pad1 . $value['value'] . $pad2 .  $status . "\n";
				if(isset($value['recommendation'])) {
					$show .= sprintf($this->PROW, $value['recommendation']);
				}
			}
			
			return $show;
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
		 * Translates the status integer codes into plain text Strings.
		 *
		 * @return String
		 **/
		function getPlainStatus($status) {
			if($status == 1) {
				return 'Passed!';
			} else if ($status == 0) {
				return 'Warning!';
			} else if ($status == -1) {
				return 'Failed!';
			}
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
				return '<span style="color: orange;">Warning!</span>';
			} else if ($status == -1) {
				return '<span style="color: red;">Failed!</span>';
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
			$this->evaluate();
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
		 * Fills in the result array.
		 *
		 * @return void
		 **/
		function message($name, $value, $status, $recommendation = null) {
			$this->output[$name]['value'] = $value;
			$this->output[$name]['status'] = $status;
			(empty($recommendation)) ? null : $this->output[$name]['recommendation'] = $recommendation;
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

			$this->title = "PHP Info";
		}
		
		
		function check() {
			$this->checkVersion();
			$this->checkServerAPI();
			$this->checkOS();
			$this->checkMemoryLimit();
			$this->checkUploadLimit();
			$this->checkExec();
		}
		
		function evaluate() {
			$allgood = ($this->output['Version'] && $this->output['Memory Limit'] && $this->output['Max Upload Filesize']);

			if ( $allgood ) {
				
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
			if($api == 'cgi' || $api == 'apache' || $api == 'apache2handler') {
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
			if($os == 'FreeBSD' || $os == 'Linux' || $os == 'Darwin' || strtoupper(substr($os, 0, 3)) === 'WIN') {
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

			// set the good limit, which is 32M
			$glimit = '32M';
			
			// convert both to bytes
			$mlimitBytes = $this->returnBytes($mlimit);
			$glimitBytes = $this->returnBytes($glimit);
			
			
			// if ours is more than recommended...
			if($mlimitBytes >= $glimitBytes) {
				$this->message('Memory Limit', $mlimit, 1);
				
			// else if no memory limit in place, so that's good, too...
			} else if (empty($mlimit)) {
				$this->message('Memory Limit', "No memory limit in effect!", 1);

			// else it's too low :()
			} else {
				$recom = 'The memory limit is too low. You can try putting this line in the .htaccess file of your
					TYPO3 root directory:<br />php_value memory_limit 8M<br />
					If that doesn\'t work and you have access to your php.ini, please set it to at least 32M or ask your
					host to do so if you don\'t have access to it.';
				$this->message('Memory Limit', $mlimit, -1, $recom);
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
		
		/**
		 * Makes sure that exec() is working.
		 *
		 * @return void
		 **/
		function checkExec() {
			exec('ls -al', $output);
			if($output) {
				$this->message('exec()', 'success', 1);
			} else {
				$recom = 'Could not use exec() on this server. Please check with your host and make sure that the
					use of exec() is allowed.';
				$this->message('exec()', 'failed', -1, $recom);
			}
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
			$this->checkClient();			

			if($this->running) {				
				$this->checkNormalConnection();
				$this->checkPersistentConnection();
				$this->checkServer();
				$this->checkHost();
				
			}
		}
		
		/**
		 * Checks the MySQL host connection
		 *
		 * @return void
		 **/
		function checkHost() {
			$host = mysql_get_host_info();
			$this->message('Host Info', $host, 1);
		}
		
		
		/**
		 * Checks the MySQL client version
		 *
		 * @return void
		 **/
		function checkClient() {
			$version = mysql_get_client_info();
			$this->message('Client Version', $version, 1);
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
				$this->message('Server Version', $version, 1);
			} else {
				$this->message('Server Version', $version, -1, "MySQL Version is not compatible!");
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
				$this->message('Status', 'Running', 1);
				$this->running = true;			
			} else {
				$this->message('Status', 'Running', -1, mysql_error());
			}

		}
		
		/**
		 * Trys to establish a non-persistent connection to the MySQL server.
		 *
		 * @return void
		 **/
		function checkNormalConnection() {
			$link = mysql_connect($GLOBALS['dbHost'], $GLOBALS['dbUser'], $GLOBALS['dbPass']);
			if($link) {
				$this->message('Non-persistent connection', 'Success', 1);
			} else {
				$recom = 'For some reason a normal connection to the MySQL database cannot be established.';
				$this->message('Non-persistent connection', 'Failed', -1, $recom);
			}
			mysql_close($link);
		}
		
		/**
		 * Trys to establish a persistent connection to the MySQL server.
		 *
		 * @return void
		 **/
		function checkPersistentConnection() {
			$link = mysql_pconnect($GLOBALS['dbHost'], $GLOBALS['dbUser'], $GLOBALS['dbPass']);
			if($link) {
				$this->message('Persistent connection', 'Success', 1);
			} else {
				$recom = 'Your setup doesn\'t allow persistent connections. In the Install Tool, make
					sure you configure TYPO3 to not	use persistent connections.';
				$this->message('Persistent connection', 'Failed', 0, $recom);
			}
			mysql_close($link);
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
					$this->message('Minimum write permissions', "N/A", -1, 'Could not create temporary folder; check permissions.');
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
				if(function_exists('symlink')) {
					$sym = symlink('test.php', 'tmp/symtest.php');	
				} else {
					$sym = false;
				}
						
				// get headers for the file and symlink we just created
				$sHeaders = $this->getHeaders($GLOBALS['scriptPath'] . "tmp/symtest.php");
				$headers = $this->getHeaders($GLOBALS['scriptPath'] . "tmp/test.php");

				// check for good headers from file, if they are, output, if not, it's bad!
				if(strpos($headers[0], "200 OK") !== false) {
					$this->message('Minimum read permissions', $perm, 1);
				} else {
					$this->message('Minimum read permissions', $perm, -1, "Reading file failed.");
				}


				// check symlink:
				// if no symlink was created and this is windows show warning.
				if(!$sym && strpos('win', strtolower($this->mc->getTestValue('PHP', 'OS')))) {
					$recom = 'Symlinks couldn\'t be created. This is probably okay since you are using Windows.';
					$this->message('Symlinks', 'Problem', 0, $recom);
				
				// no symlink was created, but we aren't using Windows; that's not good.
				} else if(!$sym && !strpos('win', strtolower($this->mc->getTestValue('PHP', 'OS')))) {
					$recom = 'Symlinks couldn\'t be created. The reason for this might be PHPsuExec, so please download
						the .zip package instead.';
					$this->message('Symlinks', 'Problem', -1, $recom);

				// symlink is there and header is good
				} else if ($sym && strpos($sHeaders[0], "200 OK") !== false) {
					$this->message('Symlinks', 'success', 1);
				
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
			
			$this->title = 'Apache Tests';
		}
		
		function check() {
			$this->checkVersion();
			$this->checkModRewrite();
			$this->checkHtaccess();
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

				// if we find mod_rewrite, all is good.
				if(in_array('mod_rewrite', apache_get_modules())) {
					$this->message('mod_rewrite', 'present', 1);				
		
				// if we don't find it, and the server api is apache, it's not there and we kind of have a problem.
				} else if($this->mc->getTestValue('PHP', 'Server API') == 'apache' || $this->mc->getTestValue('PHP', 'Server API') == 'apache2handler'){
					$recom = "mod_rewrite could not be found. It's necessary for the RealURL extension, so if you are
						having problems with your TYPO3 site, try uninstalling the extension in the extension manager.";
					$this->message('mod_rewrite', 'not found', -1, $recom);	
			
				// if we don't find it, and the server api is not apache, we can't really say whether it's there or not.
				} else {
					$recom = 'mod_rewrite could not be found, but that is because PHP is not running under Apache, which 
						is perfectly fine. Check to make sure that the \' Rewrite \' test was successful.';
					$this->message('mod_rewrite', 'not found', 0, $recom);	
				}
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
						$this->message('Version', 'not found', 0, $recom);
					} else {
                    	$this->message('Version', $version, 1);
					}
                }
            }
		}
		
		/**
		 * Checks whether .htaccess files are allowed.
		 *
		 * @return void
		 **/
		function checkHtaccess() {
			
			// get minimum file permissions from earlier test
			$perms = $this->mc->getTestValue('FilePermissions', 'Minimum write permissions');
			
			// create temp folder to create .htaccess file in.
			mkdir('test123', octdec($perms));
			
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
				$this->message('Rewrite URLs', 'success', 1);
			
			// if we get a 404 not found on the virtual file and mod_rewrite was there, overriding with .htaccess
			// is probably not allowed.
			} else if(strpos(strtolower($vheaders[0]), '404 not found') !== false && $this->output['mod_rewrite']['status'] == 1) {
				$recom = "Rewriting URLs failed. Your host doesn't allow overriding settings with .htaccess files.
					Make sure that 'AllowOverride All' is set for your virtual host in your Apache config.";
				$this->message('Rewrite URLs', 'Failed', -1, $recom);
			}
			// implicit here is that if the mod_rewrite test failed, rewriting obviously cannot work, so we don't show
			// any results since the user should already know that it won't work.
			
			
			// clean up
			unlink('test123/.htaccess');
			unlink('test123/rewrite_test.php');
			rmdir('test123');
			
			
		}
	}
	$mc->register('Apache');
	
	
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
			
			$this->title = 'TYPO3 tests';
		}
		
		function check() {

			$this->checkBaseTag();
			$this->checkHtaccess();
			$this->checkRealURL();
			$this->checkDirs('fileadmin', 0);
			$this->checkDirs('uploads', -1);
			$this->checkDirs('typo3temp', -1);
			$this->checkDirs('typo3conf', -1);
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
				$this->message('Base Tag', $output[1], -1, $recom);
			} else {
				$this->message('Base Tag', $output[1], 1);				
			}
		}
		
		/**
		 * Checks whether the .htaccess file is present.
		 *
		 * @return void
		 **/
		function checkHtaccess() {
			if(file_exists($GLOBALS['TYPO3Path'] . '.htaccess' )) {
				$this->message('.htaccess file', 'found', 1);
			} else {
				$recom = 'The .htaccess file could not be found in your TYPO3 root directory. Please make sure
					you copied it correctly from the WEC Starter Package to your web host.';
				$this->message('.htaccess file', 'not found', -1, $recom);
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
				$this->message('RealURL', 'success', 1);
			
			// if we don't get a 200 OK (i.e. 302 or 404), show a warning
			} else if (strpos($rheaders[0], '200 OK') === false) {
				$this->message('RealURL', 'failed', 0, 'Test couldn\'t be run. Wrong pid.');
			
			// see if general rewriting worked and the .htaccess file is present. That means the rewrite
			// stuff is not in this .htaccess file.
			} else if($this->mc->getTestStatus('Apache', 'Rewrite URLs') == 1 && $this->output['.htaccess file']['status'] == 1){
				$recom = "RealURL didn't work because the wrong .htaccess file is being used. Make sure you
					copied the correct .htaccess file from the WEC Starter Package to your TYPO3 root directory.";
				$this->message('RealURL', 'Failed', -1, $recom);
			
			// if the general rewriting worked but .htaccess is missing, it obviously won't work.
			} else if ($this->mc->getTestStatus('Apache', 'Rewrite URLs') == 1 && $this->output['.htaccess file']['status'] == -1) {
				$recom = "RealURL didn't work because the .htaccess file is missing. Make sure you
					copied it from the WEC Starter Package to your TYPO3 root directory.";
				$this->message('RealURL', 'Failed', -1, $recom);
		
			// just a fail safe.
			} else {
				$this->message('RealURL', 'Failed', -1, 'Unknown error.');
			}
		}
		
		/**
		 * Checks read and write for some directories inside TYPO3
		 *
		 * @param $directory Directory name as String.
		 * @param $status Status to be used on failed test. Either 0 for warning or -1 for fail.
		 * @return void
		 **/
		function checkDirs($directory, $status) {
			
			// create paths
			$path = $GLOBALS['TYPO3Path'] . $directory . '/';
			$webPath = $GLOBALS['TYPO3WebPath'] . $directory . '/';
			
			// file content
			$file = '<?php echo "Hello World!"; ?>';
			
			// open file
			$link = fopen($path . 'test.php', 'w+');
			
			$write = fwrite($link, $file);
			
			// if it couldn't be written, display a warning
			if($write === false) {
				$recom = 'Could not write to the ' . $directory . ' directory.';
				$this->message($directory, 'not writable', $status, $recom);
				return null;
			}
			
			fclose($link);
					
			// now check headers on the just created file
			$headers = $this->getHeaders($webPath . 'test.php');
			if(strpos($headers[0], '200 OK')) {
				$this->message($directory, 'readable and writable', 1);
			} else {
				$recom = 'File couldn\'t be read, check file permissions.';
				$this->message($directory, 'Could not access file over HTTP.', 0, $recom);
			}
			
			unlink($path . 'test.php');
			
		}
	}
	$mc->register('TYPO3');
	
	
	//-----------------------------------
	//|			Nitty Gritty			|
	//-----------------------------------
	
	// turn off error reporting. After all, that's what we're doing here.
	error_reporting(0);

	$mc->runAll();
	echo $renderer->renderAll($mc->getResults());
?>