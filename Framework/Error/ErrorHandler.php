<?php class ErrorHandler {

public static function error($errNo, $errStr, $errFile, $errLine, $errContext) {
	$data = array(
		"Number"  => $errNo,
		"Message" => $errStr,
		"File"    => $errFile,
		"Line"    => $errLine,
		"Context" => $errContext,
	);
	throw new HttpError(500, $data);
}

}#