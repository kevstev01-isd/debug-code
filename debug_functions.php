<?php
// DEBUG FUNCTIONS
// kevin.stevens@idealshoppingdirect.co.uk
// last modified 15-Aug-2013

if(defined('STDIN') ) {
  define("CRLF", "\r\n");
} else {
//  echo("Not Running from CLI"); 
  define("CRLF", "<br/>\r\n");
}

$fbWriter = NULL;
$fbLogger = NULL;

function initFirebug() {
global $fbWriter, $fbLogger;
	if($fbWriter == NULL) {
		$fbWriter = new Zend_Log_Writer_Firebug();
		$fbLogger = new Zend_Log($fbWriter);
	}
}

function firebug( $data ) {
global $fbLogger;
	$var = (is_string($data)?$data:dump($data));
	if($fbLogger) { $fbLogger->log( $var, Zend_Log::INFO ); }
	return($var);
}

function logToFileStackTrace() {
    $strTrace = '';
    $btrace = debug_backtrace();
    $count = 0;
    foreach($btrace as $bt) {
        if( $count > 0 ) { // ignore 1st item which is this function.
            $strTrace .= $count . ' ' . $bt['file'] . ':' . $bt['line'] . ' ' . $bt['function'].PHP_EOL;
        }
        $count++;
    }
    logToFile( 'STACK TRACE:' .PHP_EOL. $strTrace);
}

// simple file logging - time stamped
function logToFile( $data ) {
    $filename = (strlen($_SERVER['DOCUMENT_ROOT'])>0 ? $_SERVER['DOCUMENT_ROOT'] . '/var/debug.log' : 'debug.log');
    $retval = 0;
    if($fd = @fopen($filename, 'a')) { // open file in append mode
        $strTrace = '';
        $btrace = debug_backtrace();
        $count = 0;
        foreach($btrace as $bt) {
            if( $count == 0 ) { // get file called from
                $strTrace = $bt['file'] . ':' . $bt['line']; // $bt['function'].PHP_EOL;
            } elseif( $count == 1 ) { // get function called from
                $strTrace .= ' ' . $bt['function'];
            } else {
                break;
            }
            $count++;
        }
        $retval = fwrite($fd, date("Y-m-d H:i:s").' '.$strTrace.PHP_EOL.(is_string($data)?$data:dump($data)).PHP_EOL); // write string
        fclose($fd); // close file
    }
    return($retval);
}

function dump_html( $subject ) {
    return ( '<pre>'. dump($subject).'</pre>' );
}

// variable dump (like print_r but handles recursion better and has a nest limit)
// adapted from user contributed php.net version
function dump($subject, $ignore = array(), $depth = 1, $refChain = array()) {
    $txtOutput = '';
//    if ( is_object($subject) && $depth > 6) {
    if ( $depth > 6 ) {
        //$txtOutput .= str_repeat(' ', $depth * 4);
        if(is_object($subject)) {
            $txtOutput .= get_class($subject) .' Object...'.PHP_EOL;
        } elseif(is_array($subject)) {
            $txtOutput .=  'Array...'.PHP_EOL;
        } else {
            $txtOutput .=  '...'.PHP_EOL;
        }
    } else {
        if (is_object($subject)) {
            foreach ($refChain as $refVal)
                if ($refVal === $subject) {
                    $txtOutput .= '*RECURSION*'.PHP_EOL;
                    return $txtOutput;
                }
            array_push($refChain, $subject);
            $txtOutput .= get_class($subject) . ' Object ('.PHP_EOL;
            $subject = (array) $subject;
            foreach ($subject as $key => $val)
                if (is_array($ignore) && !in_array($key, $ignore, 1)) {
                    $txtOutput .= str_repeat(' ', $depth * 4) . '[';
                    if ($key{0} == "\0") {
                        $keyParts = explode("\0", $key);
                        $txtOutput .=  $keyParts[2] . (($keyParts[1] == '*')  ? ':protected' : ':private');
                    } else
                        $txtOutput .=  $key;
                    $txtOutput .=  '] => ';
                    $txtOutput .= dump($val, $ignore, $depth + 1, $refChain);
                }
            $txtOutput .=  str_repeat(' ', ($depth - 1) * 4) . ')'.PHP_EOL;
            array_pop($refChain);
        } elseif (is_array($subject)) {
            $txtOutput .=  'Array ('.PHP_EOL;
            foreach ($subject as $key => $val)
                if (is_array($ignore) && !in_array($key, $ignore, 1)) {
                    $txtOutput .= str_repeat(' ', $depth * 4) . '[' . $key . '] => ';
                    $txtOutput .= dump($val, $ignore, $depth + 1, $refChain);
                }
            $txtOutput .=  str_repeat(' ', ($depth - 1) * 4) . ')'.PHP_EOL;
        } else {
            $txtOutput .=  $subject.PHP_EOL;
        }
    }
    return $txtOutput;
 }

// Prints the file name, function name, and line number which called your function 
// (not this function, the one that  called it to begin with) 
function debugPrintCallingFunction () { 
    $file = 'n/a'; 
    $func = 'n/a'; 
    $line = 'n/a'; 
    $debugTrace = debug_backtrace(); 
    if (isset($debugTrace[1])) { 
        $file = $debugTrace[1]['file'] ? $debugTrace[1]['file'] : 'n/a'; 
        $line = $debugTrace[1]['line'] ? $debugTrace[1]['line'] : 'n/a'; 
    } 
    if (isset($debugTrace[2])) $func = $debugTrace[2]['function'] ? $debugTrace[2]['function'] : 'n/a'; 
    echo "$file, $func, $line".PHP_EOL; 
} 
// END DEBUG FUNCTIONS.
?>