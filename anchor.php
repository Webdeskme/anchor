<?php
//tokans
$tt = array();
$tt['int'] = 'int';
$tt['float'] = 'float';
$tt['plus'] = 'plus';
$tt['minus'] = 'minus';
$tt['mul'] = 'mul';
$tt['div'] = 'div';
$tt['expo'] = 'expo';
$tt['lparen'] = 'lparen';
$tt['rparen'] = 'rparen';
$tt['var'] = 'var';
$tt['func'] = 'func';
$tt['set'] = 'set';
$tt['lbrack'] = 'lbrack';
$tt['rbrack'] = 'rbrack';
//vars
$g = array();
$g['const']['pi'] = M_PI;
$g['const']['e'] = M_E;
$g['const']['y'] = M_EULER;
$g['const']['rand'] = mt_rand(1,9);
$g['const']['date'] = date("Y-m-d");
$g['const']['time'] = date("h:i:sa");
$g['const']['stamp'] = time();
// Parser Functions
function vars($token, $tt, $line){
	$token = array_values($token);
	$error = "";
	$next = 1;
foreach($token as $key => $value){
	if(isset($token[$key])){
		//print_r($token);
	if($value == $tt["var"]){
		if(isset($token[$next])){
			if(isset($token[$next+1]) && isset($token[$next+2]) && isset($token[$next+3]) && $token[$next+1] == "lbrack" && $token[$next+3] == "rbrack"){
				$token[$key] = $GLOBALS['g'][$token[$next]][$token[$next+2]][0];
				unset($token[$next]);
				unset($token[$next+1]);
				unset($token[$next+2]);
				unset($token[$next+3]);
			}
			else{
				if(isset($GLOBALS['g'][$token[$next]][0][0])){
					$token[$key] = $GLOBALS['g'][$token[$next]][0][0];
				}
				else{
					$token[$key] = 0;
					$GLOBALS['g'][$token[$next]][0][0] = 0;
				}
				unset($token[$next]);
			}
		}
		else{
			$error = $error . "\033[43m\033[31m Error[" . $line . ":" . $key . "]:\033[30m Improper use, expected content the right side of the operator.\033[0m\n";
		}
	}
}
$next = $next + 1;
}
$token = array($token, $error);
return $token;
}
function par($token, $tt, $line){
	$token = array_values($token);
	$error = "";
	$ptcap = "no";
	$plkey = "";
	$prkey = "";
	$pakey = array();
	$ptoken = array();
	$pl = "yes";
	$pair = "yes";
	while ($pl == "yes") {
		$pl = "no";
		$continue = "yes";
		$pair = "no";
		foreach($token as $key => $value){
			if(isset($token[$key]) && $continue == "yes"){
				if($value == $tt["rparen"]){
					$pair = "yes";
					$prkey = $key;
					$ptcap = "no";
					//print_r($token);
					foreach($pakey as $pa => $pavalue){
						//echo $token[$pavalue] . "\n";
						unset($token[$pavalue]);
					}
					unset($token[$prkey]);
					$ptoken = expo($ptoken, $tt, $line);
					if(isset($ptoken[1])){
						$error = $error . $ptoken[1];
					}
					$ptoken = $ptoken[0];
					$ptoken = muldiv($ptoken, $tt, $line);
					if(isset($ptoken[1])){
						$error = $error . $ptoken[1];
					}
					$ptoken = $ptoken[0];
					$ptoken = addSub($ptoken, $tt, $line);
					if(isset($ptoken[1])){
						$error = $error . $ptoken[1];
					}
					$ptoken = $ptoken[0];
					$pval = "";
					foreach($ptoken as $pkey => $pvalue){
						$pval = $pval . $pvalue;
					}
					$token[$plkey] = $pval;
					$token = array_values($token);
					$continue = "no";
					$pl = "yes";
					$pakey = array();
				}
				if($ptcap == "yes"){
					array_push($pakey,$key);
					array_push($ptoken,$token[$key]);
				}
				if($value == $tt["lparen"]){
					$ptoken = array();
					$pakey = array();
					$plkey = $key;
					$ptcap = "yes";
					$pl = "yes";
				}
			}
		}
	}
	//print_r($token);
	$token = array($token, $error);
	return $token;
}
function expo($token, $tt, $line){
	$token = array_values($token);
	$error = "";
	$prev = "";
	$next = 1;
foreach($token as $key => $value){
	if(isset($token[$key])){
	if($value == $tt["expo"]){
		if(isset($token[$prev]) && isset($token[$next]) && is_numeric($token[$prev]) && is_numeric($token[$next])){
			$token[$prev] = pow($token[$prev], $token[$next]);
			unset($token[$key]);
			unset($token[$next]);
		}
		else{
			$error = $error . "\033[43m\033[31m Error[" . $line . ":" . $key . "]:\033[30m Improper use, expected a number on either side of operator.\033[0m\n";
		}
	}
	else{
		$prev = $key;
	}
}
$next = $next + 1;
}
$token = array($token, $error);
return $token;
}

function muldiv($token, $tt, $line){
	$token = array_values($token);
	$error = "";
	$prev = "";
	$next = 1;
foreach($token as $key => $value){
	if(isset($token[$key])){
	if($value == $tt["mul"] || $value == $tt["div"]){
		if(isset($token[$prev]) && isset($token[$next]) && is_numeric($token[$prev]) && is_numeric($token[$next])){
			if($value == $tt["mul"]){
				$token[$prev] = $token[$prev]*$token[$next];
			}
			else{
				$token[$prev] = $token[$prev]/$token[$next];
			}
			unset($token[$key]);
			unset($token[$next]);
		}
		else{
			$error = $error . "\033[43m\033[31m Error[" . $line . ":" . $key . "]:\033[30m Improper use, expected a number on either side of operator.\033[0m\n";
		}
	}
	else{
		$prev = $key;
	}
}
$next = $next + 1;
}
$token = array($token, $error);
return $token;
}

function addSub($token, $tt, $line){
	$token = array_values($token);
	$error = "";
	$prev = "";
	$next = 1;
	foreach($token as $key => $value){
		if(isset($token[$key])){
			if($value == $tt["plus"] || $value == $tt["minus"]){
				if(isset($token[$prev]) && isset($token[$next]) && is_numeric($token[$prev]) && is_numeric($token[$next])){
					if($value == $tt["plus"]){
						$token[$prev] = $token[$prev]+$token[$next];
					}
					else{
						$token[$prev] = $token[$prev]-$token[$next];
					}
					unset($token[$key]);
					unset($token[$next]);
				}
				else{
					$error = $error . "\033[43m\033[31m Error[" . $line . ":" . $key . "]:\033[30m Improper use, expected a number on either side of operator.\033[0m\n";
				}
			}
			else{
				$prev = $key;
			}
		}
		$next = $next + 1;
	}
	$token = array($token, $error);
	return $token;
}
function set($token, $tt, $line){
	$token = array_values($token);
	$error = "";
	$prev = "";
	$next = 1;
	foreach($token as $key => $value){
		if(isset($token[$key])){
			if($value == $tt["set"]){
				if(isset($token[$prev])){
					if($token[$prev] == "rbrack"){
						//echo $token[$prev] . "\n";
						//print_r($token);
						$fu = $token[$prev-3];
						$fu1 = $token[$prev-1];
						if(isset($token[$next])){
							$token[$prev-3] = $token[$next];
							unset($token[$next]);
						}
						else{
							$token[$prev-3] = (string)"0";
						}
						unset($token[$prev-2]);
						unset($token[$prev-1]);
						unset($token[$prev]);
						unset($token[$key]);
						unset($token[$next]);
						$token = array_values($token);
						$GLOBALS['g'][$fu][$fu1] = $token;
					}
					else{
						$fu = $token[$prev];
						$token[$prev] = $token[$next];
						unset($token[$key]);
						unset($token[$next]);
						$token = array_values($token);
						if(isset($GLOBALS['g'][$fu])){
							unset($GLOBALS['g'][$fu]);
						}
						$GLOBALS['g'][$fu][0] = $token;
					}
				}
				else{
					$error = $error . "\033[43m\033[31m Error[" . $line . ":" . $key . "]:\033[30m Improper use, expected a content on the left side of operator.\033[0m\n";
				}
			}
			else{
				$prev = $key;
			}
		}
		$next = $next + 1;
	}
	$token = array($token, $error);
	return $token;
}
//interpretor
function run($input, $x){
	//const
	$digits = '0123456789';
	$char = 'abcdefghijklmnopqrstuzwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	//var
	$tt = $GLOBALS['tt'];
	$result = "";
	$error = "";
	$line = 1;
	$let = "no";
	$letter = "";
	$num = "no";
	$dotc = "no";
	$number = 0;
	$token = array();
	//lexer
	$input = str_split($input);
	foreach($input as $key => $value){
		$value = (string)$value;
		if($input !== "" && $input !== " " && $input !== "\t" && $value !==""){
		if(strpos($char, $value) !== false){
			$letter = $letter . $value;
			$let = "yes";
		}
		else{
			if($let == "yes"){
				array_push($token,$letter);
				$let = "no";
				$letter = "";
				$letter = (string)$letter;
			}
		}
		if(strpos($digits, $value) !== false || $value == "."){
			$number = $number . $value;
			$num = "yes";
			if($value == "."){
				$dotc = "yes";
			}
		}
		else{
			if($num == "yes"){
				if($dotc == "yes"){
					$number = (float)$number;
					if($number == ""){
						$number = (string)"0";
					}
					array_push($token,$number);
					$num = "no";
					$number = "0";
					$number = (string)$number;
					$dotc = "no";
				}
				else{
					$number = (int)$number;
					if($number == ""){
						$number = (string)"0";
					}
					array_push($token,$number);
					$num = "no";
					$number = "0";
					$number = (string)$number;
					$dotc = "no";
				}
			}
		}

		if(strpos($char, $value) !== false || $value == "." || strpos($digits, $value) !== false || $value == " "){

		}
		elseif($value == "+"){
			array_push($token,$tt["plus"]);
		}
		elseif($value == "-"){
			array_push($token,$tt["minus"]);
		}
		elseif($value == "*"){
			array_push($token,$tt["mul"]);
		}
		elseif($value == "/"){
			array_push($token,$tt["div"]);
		}
		elseif($value == "^"){
			array_push($token,$tt["expo"]);
		}
		elseif($value == "("){
			array_push($token,$tt["lparen"]);
		}
		elseif($value == ")"){
			array_push($token,$tt["rparen"]);
		}
		elseif($value == "$"){
			array_push($token,$tt["var"]);
		}
		elseif($value == "_"){
			array_push($token,$tt["func"]);
		}
		elseif($value == ":"){
			array_push($token,$tt["set"]);
		}
		elseif($value == "["){
			array_push($token,$tt["lbrack"]);
		}
		elseif($value == "]"){
			array_push($token,$tt["rbrack"]);
		}
		else{
			$error = $error . "\033[43m\033[31m Error[" . $line . ":" . $key . "]:\033[30m Not a known charecter.\033[0m\n";
		}
}
	}
	if($num == "yes"){
		if($dotc == "yes"){
			$number = (float)$number;
			array_push($token,$number);
			$num = "no";
			$number = "";
			$number = (string)$number;
			$dotc = "no";
		}
		else{
			$number = (int)$number;
			array_push($token,$number);
			$num = "no";
			$number = "";
			$number = (string)$number;
			$dotc = "no";
		}
	}
	if($let == "yes"){
		//echo $letter . " Done\n";
		array_push($token,$letter);
		$let = "no";
		$letter = "";
		$letter = (string)$letter;
	}
	// Parser
	$token = vars($token, $tt, $line);
	if(isset($token[1])){
		$error = $error . $token[1];
	}
	$token = $token[0];
	$token = par($token, $tt, $line);
	if(isset($token[1])){
		$error = $error . $token[1];
	}
	$token = $token[0];
	$token = expo($token, $tt, $line);
	if(isset($token[1])){
		$error = $error . $token[1];
	}
	$token = $token[0];
	$token = muldiv($token, $tt, $line);
	if(isset($token[1])){
		$error = $error . $token[1];
	}
	$token = $token[0];
	$token = addSub($token, $tt, $line);
	if(isset($token[1])){
		$error = $error . $token[1];
	}
	$token = $token[0];
	$token = set($token, $tt, $line);
	if(isset($token[1])){
		$error = $error . $token[1];
	}
	$token = $token[0];
	//renderer
	//print_r($token);
	foreach($token as $key => $value){
		$result = $result . $value;
	}
	if($error != ""){
		echo "\033[35m >> Errors:\n" . $error . "\033[0m\n";
	}
	$GLOBALS['g']['line'][$x] = $token;
	echo "\033[35m $" . "line[" . $x . "]> \033[33m" . $result . "\n";
	print_r($GLOBALS['g']);
}
?>
