<?php

namespace Parser;

use Utils\ClassUtils;

class ClassParser{
    private $parsedClasses;
    private $utils;

    public function __construct(ClassUtils $utils){
        $this->parsedClasses    = [];
        $this->utils            = $utils;
    }
    public function parseClass($fileName)
    {
        if(array_key_exists($fileName, $this->parsedClasses)) {
            return $this->parsedClasses[$fileName];
        }
        $namespace = "";
        $uses = array();
        $aliases = array();
        $className = "";
        $extends = array();
        $implements = array();
        $constructorArguments = array();
        $classNameregex = "/^\s*(abstract|final)?(\s+)?(class)\s+([\\\\,\w]+)(\s+extends\s+([\\\\\w]+))?(\s+implements\s+([^{]*))?/";
        $interfaceRegex = "/^\s*interface\s+([\\\\,\w]+)(\s+extends(.*))?/";
        $constructorRegex = "/^\s*public\s+function\s+__construct\((.*)\)/";
        $classContent = file($fileName);
        $formattedClassContent = $this->formatClassContent($classContent);
        $parseEnded = false;
        foreach ($formattedClassContent as $line) {
            if(preg_match("/\s*namespace\s+(.*)(;)?/", $line, $matches)) {
                $namespace = trim($matches[1], ";");
            }
            if(preg_match("/\s*use\s+(.*)/", $line, $matches)) {
                $classUses = explode(",", $matches[1]);
                foreach($classUses as $use) { // use statement
                    $alias = "";
                    if(empty($use)) {
                        continue;
                    }
                    if(strpos($use, " as ")) {
                        $ases = explode(" as ", $use);
                        $use = trim($ases[0]);
                        $alias = trim($ases[1], ";");
                    }
                    $useTokens = explode("\\", $use);
                    if(count($useTokens) == 1) {
                        $lastToken = trim($useTokens[0], "; ");
                        $uses[$useTokens[0]] = $useTokens[0];
                    } else {
                        $lastToken = trim(array_pop($useTokens), "; ");
                        if(array_key_exists($lastToken, $uses)) {
                            $lastToken = $use;
                            $uses[$lastToken] = $use;
                        } else {
                            $uses[$lastToken] = join("\\", $useTokens);
                        }
                    }

                    if(!empty($alias)) {
                        $aliases[$alias] = $lastToken;
                    }
                }
            }
            if(preg_match($classNameregex, $line, $matches)) {
                if(strlen($className) > 0){
                    continue;
                }
                $className = $matches[4];
                if(!empty($matches[6])) { //extends
                    $extends = trim($matches[6], " \n\r");
                    if(strpos($extends, "\\")) {
                        $useTokens = explode("\\", $extends);
                        $firstToken = $useTokens[0];
                        $lastToken = array_pop($useTokens);
                        $extends = $lastToken;
                        if(array_key_exists($firstToken, $uses)) {
                            $uses[$lastToken] = $uses[$firstToken]. "\\" . join("\\", $useTokens);
                        } else {
                            $uses[$lastToken] = $namespace . "\\" . join("\\", $useTokens);
                            //$uses[$lastToken] = join("\\", $useTokens);
                        }
                    }
                }
                if(!empty($matches[8])) {
                    $classImplements = explode(",", $matches[8]);
                    foreach($classImplements as $implement) { //implements
                        if(empty($implement)) {
                            continue;
                        }
                        $implement = trim($implement, " \n\r{");
                        if(strpos($implement, "\\")) {
                            $useTokens = explode("\\", $implement);
                            $firstToken = $useTokens[0];
                            $lastToken = array_pop($useTokens);
                            $implements[] = $lastToken;
                            if(array_key_exists($firstToken, $uses)) {
                                $uses[$lastToken] = $uses[$firstToken]. "\\" . join("\\", $useTokens);
                            } else {
                                $uses[$lastToken] = $namespace . "\\" . join("\\", $useTokens);
                                //print_r($useTokens);
                                //$uses[$lastToken] = join("\\", $useTokens);
                            }
                        } else {
                            $implements[] = $implement;
                        }
                    }
                }
            }
            if(preg_match($constructorRegex, $line, $matches)) {
                $arguments = explode(",", $matches[1]);
                foreach ($arguments as $argument) {
                    $argument = trim($argument);
                    if(isset($argument[0]) && $argument[0] == '$') {
                        continue;
                    }
                    $segments = preg_split("/\s+/", $argument);
                    if(count($segments) == 1) {
                        continue;
                    }
                    if($this->isScalar($segments[0])) {
                        continue;
                    }
                    $argumentFQCN = $segments[0];
                    if(strpos($segments[0], "\\")) {
                        $useTokens = explode("\\", $segments[0]);
                        $firstToken = $useTokens[0];
                        $lastToken = array_pop($useTokens);
                        $constructorArguments[] = $lastToken;
                        if(array_key_exists($firstToken, $uses)) {
                            $uses[$lastToken] = $uses[$firstToken]. "\\" . join("\\", $useTokens);
                        } else {
                            $uses[$lastToken] = $namespace . "\\" . join("\\", $useTokens);
                        }
                    } else {
                        $constructorArguments[] = $argumentFQCN;
                    }
                }
            }
            if(preg_match($interfaceRegex, $line, $matches)) {
                $className = $matches[1];
                if(!empty($matches[3])) {
                    foreach (explode(",", $matches[3]) as $implement) {
                        //triplicate, will extract later
                        $implement = trim($implement, " \n\r{");
                        if(strpos($implement, "\\")) {
                            $useTokens = explode("\\", $implement);
                            $firstToken = $useTokens[0];
                            $lastToken = array_pop($useTokens);
                            $implements[] = $lastToken;
                            if(array_key_exists($firstToken, $uses)) {
                                $uses[$lastToken] = $uses[$firstToken]. "\\" . join("\\", $useTokens);
                            } else {
                                $uses[$lastToken] = $namespace . "\\" . join("\\", $useTokens);
                                //print_r($useTokens);
                                //$uses[$lastToken] = join("\\", $useTokens);
                            }
                        } else {
                            $implements[] = $implement;
                        }
                    }
                }
                $parseEnded = true;
            }
        }

        $parsedData = array(
            'namespaces' => array(
                'uses' => $uses,
                'alias' => $aliases,
                'file' => $namespace,
            ),
            'class_line_data' => array(
                'implements' => $implements,
                'extends' => $extends,
                'classname' => $className
            ),
            'constructor_arguments' =>  $constructorArguments
        );
        //ldd($parsedData);
        $this->parsedClasses[$fileName] = $parsedData;
        return $parsedData;
    }

    /**
     * Splits multple use, declarations in one line
     * Easier for parsing later
     * @param array $classContent
     */
    public function formatClassContent($classContent)
    {
        $namespace  = "";
        $uses       = array();
        $aliases    = array();
        $className  = "";
        $extends    = array();
        $implements = array();

        $isMultiLine           = false;
        $multiLine             = "";
        $currentSection        = "";
        $formattedClassContent = array();

        $fullLine = "";
        $isClassLine = false;
        $classLine = "";
        $useEnded = false;

        $isConstructorLine = false;
        $constructorLine = "";

        foreach ($classContent as $line) {

            $line = trim(str_replace("<?php", "", $line), "\t\n ");
            $line = str_replace('/* final */', "", $line);
            if(!$isMultiLine && !$isClassLine && !$isConstructorLine && !$useEnded && count(explode(";", $line)) > 2) {
                $formattedClassContent = array_merge($formattedClassContent, explode(';', $line));
                continue;
            }

            if($isClassLine) {
                $classLine .= " " . $line;
                if(strpos($classLine, "{")) {
                    $isClassLine = false;
                    $useEnded = true;
                    $formattedClassContent[] = trim($classLine, "{}");
                }
            }

            if($isConstructorLine) {
                $constructorLine .= " " . $line;
                if(strpos($constructorLine, "{")) {
                    $isConstructorLine = false;
                    $formattedClassContent[] = trim($constructorLine, "{}");
                    break;
                }
            }

            if(!$isMultiLine && !$isClassLine && !$isConstructorLine &&
                (
                    preg_match("/^\s*namespace/", $line)
                    || preg_match("/^\s*use/", $line)
                    || preg_match("/^\s*(abstract|final)?\s*(class|interface)/", $line)
                    || preg_match("/^\s*interface/", $line)
                    || preg_match("/^\s*public\s+function\s+__construct/", $line)
                )
            ){
                if(preg_match("/^\s*(abstract|final)?\s*(class|interface)/", $line )) {
                    if(strpos($line, "{") === false) {
                        $isClassLine = true;
                        $classLine .= trim($line, ";");
                        continue;
                    } else {
                        $isClassLine = false;
                        $useEnded = false;
                        $formattedClassContent[] = trim($line, ";{}");
                        continue;
                    }
                }

                if(preg_match("/^\s*public\s+function\s+__construct/", $line)) {
                    if(strpos($line, ")") === false) {
                        $isConstructorLine = true;
                        $constructorLine .= trim($line, ";");
                        continue;
                    } else {
                        $isConstructorLine = false;
                        $formattedClassContent[] = trim($line, ";{}");
                        break;
                    }
                }

                if(strpos($line, ";") === false && strpos($line, ",") >= 0) {
                    $isMultiLine = true;
                    $multiLine .= $line;
                } else {
                    $formattedClassContent[] = trim($line, ";{");
                }
                continue;
            }

            if($isMultiLine) {
                $multiLine .= $line;
                if(strpos($multiLine, ";") !== false) {
                    $isMultiLine = false;
                    if(explode(";", $multiLine) >2) {
                        $formattedClassContent = array_merge($formattedClassContent, explode(";", $multiLine));
                    } else {
                        $formattedClassContent[] = trim($multiLine, ";{");
                    }
                }
            }
        }
        array_walk($formattedClassContent, function(&$item){
            $item = trim($item);
        });
        return $formattedClassContent;
    }

    public function getClassContent($fileLocation, $className)
    {
        $fp = fopen($fileLocation, 'r');
        if(!$fp) {
            throw new \Exception("read file of  class ". $className." failed");
        }

        $classContent = array();
        while (($line = fgets($fp, 4096)) !== false) {
            $classContent[] = $line;
        }
        if (!feof($fp)) {
            echo "Error: unexpected fgets() fail\n";
        }
        fclose($fp);
        return $classContent;
    }
}
