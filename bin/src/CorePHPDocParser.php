<?php
/**
 *=============================================================================
 * AUTHOR:  Mun Mun Das <m2mdas at gmail.com>
 * FILE: CorePHPDocParser.php
 * Last Modified: September 10, 2013
 * License: MIT license  {{{
 *     Permission is hereby granted, free of charge, to any person obtaining
 *     a copy of this software and associated documentation files (the
 *     "Software"), to deal in the Software without restriction, including
 *     without limitation the rights to use, copy, modify, merge, publish,
 *     distribute, sublicense, and/or sell copies of the Software, and to
 *     permit persons to whom the Software is furnished to do so, subject to
 *     the following conditions:
 *
 *     The above copyright notice and this permission notice shall be included
 *     in all copies or substantial portions of the Software.
 *
 *     THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 *     OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 *     MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 *     IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 *     CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 *     TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 *     SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * }}}
 *=============================================================================
 */

/**
 * generated index from offline php core documentation
 * It can be downloaded from http://www.php.net/download-docs.php
 * Download and unter the Many html files tar file
 *
 * Usage:
 *  new CorePHPDocParser("/your/php/doc/location", "/filename/to/save/index")->parsePhpdoc();
 */
class CorePhpDocParser
{
    /**
     * undocumented class variable
     *
     * @var array
     */
    private $coreIndex;

    /**
     * location of php doc folder
     * @var string
     */
    private $docLocation;

    /**
     * processed function docs
     *
     * @var array
     */
    public $functionDocs;

    /**
     * processed class docs
     *
     * @var array
     */
    public $classDocs;

    /**
     * constants
     *
     * @var array 
     */
    public $constants;

    private $indexFileName;

    private $xmlParser;

    public function __construct($docLocation, $indexFileName)
    {
        $this->coreIndex = array();
        $this->docLocation = $docLocation;
        $this->indexFileName = $indexFileName;
        $this->functionDocs = array();
        $this->classDocs = array();
        $this->constants = array();
        $this->xmlParser = new XMLParser();
    }

    /**
     * @return string
     */
    public function setDocLocation($docLocation)
    {
        $this->docLocation = $docLocation;
    }

    /**
     * returns book links
     *
     * @return returns array of book links
     */
    public function getBookLinks()
    {
        $books = array();
        $dom = DomDocument::loadHTMLFile($this->docLocation . "/extensions.membership.html");
        $query = '//*[@id="extensions.membership"]//a[@class="xref"]';

        $xpath = new DOMXPath($dom);
        $entries = $xpath->query($query);

        foreach ($entries as $entry) {
            $books[] = $entry->attributes->getNamedItem('href')->nodeValue;
        }
        return $books;
    }

    public function parsePhpDoc() 
    {
        $bookLinks = $this->getBookLinks();
        foreach($bookLinks as $bookLink) {
            $this->parseSectionDocFile($bookLink);
        }

        //$extraPredefinedConstants = array('reserved.constants.php');
        //foreach ($extraPredefinedConstants as $ep) {
            //$constants = $this->parsePredefinedConstants($ep);
            //$this->constants = array_merge($this->constants, $constants);
        //}

        $this->parsePredefinedClasses ("reserved.interfaces.html");
        $this->parsePredefinedClasses ("reserved.exceptions.html");

        $funcList = array_keys($this->functionDocs);
        sort($funcList);
        $classList = array_keys($this->classDocs);
        sort($classList);

        //ldd($indexedFuncList);

        sort($this->constants);
        $outData = array(
            'functions' => $this->functionDocs,
            'classes' => $this->classDocs,
            'function_list' => $funcList,
            'class_list' => $classList,
            'predefined_constants' => $this->constants
        );

        file_put_contents($this->indexFileName, json_encode($outData));
    }

    public function getBookProperties($bookFile) 
    {
        $excludedTypes = array(
            'Requirements',
            'Installation',
            'Runtime Configuration',
            'Resource Types',
            'Introduction',
            'Installing/Configuring',
        );
        $ss = explode(".", $bookFile);
        $section = $ss[1];
        $propertyLinks = array();

        $dom = @DomDocument::loadHTMLFile($this->docLocation . "/" . $bookFile);
        $xpath = new DOMXPath($dom);

        $query = '//*[@class="chunklist chunklist_book"]/li/a';
        $entries = $xpath->query($query);

        $subSectionQuery = '//*[@class="chunklist chunklist_book chunklist_children"]/li/a';
        $subSectionEntries = $xpath->query($subSectionQuery);

        $validLocations = array('class', 'ref', 'constants');
        foreach ($entries as $entry) {
            $location = $entry->attributes->getNamedItem('href')->nodeValue;
            if(in_array(substr($location, 0, strpos($location, ".")), $validLocations) || strpos($location, 'constants') !== false) {
                $propertyValue = $entry->nodeValue;
                $propertyLinks[$propertyValue] = $location;
            }
        }

        foreach ($subSectionEntries as $subSectionEntry) {
            $location = $subSectionEntry->attributes->getNamedItem('href')->nodeValue;
            if(!in_array(substr($location, 0, strpos($location, ".")), $validLocations)) {
                continue;
            }
            $propertyValue = (string) $subSectionEntry->nodeValue;
            $propertyLinks[$propertyValue] = $location;
        }

        return $propertyLinks;
    }

    public function parseMethodDocFile($methodFile) 
    {
        $methodInfo = array(
            'params' => array(),
            'docComment' => "",
            'signature' => "",
            'inheritdoc' => 0,
            'modifier' => array(),
            'return'=> ""
        );
        $excludedFiles = array("swfbutton.setdown.html", 
            "function.mysqli-report.html",
            "swfbutton.sethit.html",
            "swfbutton.setover.html",
            "swfbutton.setup.html",
            "function.xdiff-file-diff-binary.html",
            "function.xdiff-file-patch-binary.html",
            "function.xdiff-string-patch-binary.html",
            "function.xdiff-string-diff-binary.html",
        );

        if(in_array($methodFile, $excludedFiles)) {
            return $methodInfo;
        }

        $dom = @DomDocument::loadHTMLFile($this->docLocation . "/" . $methodFile); 
        $descQuery = '//*[@class="refpurpose"]/span[@class="dc-title"]';

        $fullDoc = utf8_encode(trim($this->parseFullDoc($methodFile)));
        $methodInfo['docComment'] = $fullDoc;

        $xpath = new DOMXPath($dom);
        $descEntries = $xpath->query($descQuery);

        foreach ($descEntries as $descEntry) {
            $desc = simplexml_import_dom($descEntry);
            if(strpos((string)$desc, "Alias") !== false && strpos((string)$desc, "Alias") == 0) {
                //ld($methodFile);
                $aliasFilaName = (string)$desc->span->a['href'];
                return $this->parseMethodDocFile($aliasFilaName);
            }
        }

        $signatureQuery = '//*[@class="refsect1 description"]/div[@class="methodsynopsis dc-description"]';
        $signatureEntries = $xpath->query($signatureQuery);
        foreach ($signatureEntries as $signatureEntry) {
            $sxml =  simplexml_import_dom($signatureEntry);
            $paramStrings = array();
            //ld($sxml->asXML());
            $modifier = "";
            foreach ($sxml->children() as $children) {
                switch ($children['class']) {
                    case 'type':
                        $returns = isset($children->a)? (string) $children->a : (string) $children;
                        $methodInfo['return'] = $this->isScalar($returns)? "" : $returns;
                        break;
                    case 'modifier':
                        $modifier = (string) $children;
                        $methodInfo['modifier'][] = $modifier;
                        break;
                    case 'methodname':
                        $methodName = (string) $children->strong;
                        break;
                    case 'methodparam':
                        if((string) $children == "void") {
                            continue;
                        }
                        $paramType = isset($children->span->a)? (string) $children->span->a: (string) $children->span;
                        $paramVar = (string) $children->code;
                        $paramString = $paramType . " " . $paramVar;
                        if(isset($children->span[1])) {
                            $paramString = "[" . $paramString . "]";
                        }
                        $paramStrings[] = $paramString;

                        $methodInfo['params'][$paramVar] = $this->isScalar($paramType)? "" : $paramType;
                    default:
                        break;
                }
            }
            $methodInfo['signature'] = "(" . join(" ,", $paramStrings) . ") : ". (string)$returns;
        }

        return $methodInfo;
    }

    public function parseFullDoc($file)
    {
        $dom = @DomDocument::loadHTMLFile($this->docLocation . "/" . $file);
        $descQuery = '//*[@class="refentry"]';
        $descQuery2 = '//*[@class="reference"]'; //don't know the syntax of OR property text
        $xpath = new DOMXPath($dom);
        $descEntries = $xpath->query($descQuery);
        if($descEntries->length == 0) {
            $xpath = new DOMXPath($dom);
            $descEntries = $xpath->query($descQuery2);
        }
        foreach ($descEntries as $descEntry) {
            $simpleXML = simplexml_import_dom($descEntry);
            $xml = $simpleXML->asXML();
            $this->xmlParser->reset();

            $xml_parser = xml_parser_create();
            xml_set_element_handler($xml_parser, array($this->xmlParser, 'startElementHandler'), array($this->xmlParser, 'endElementHandler'));
            xml_set_character_data_handler($xml_parser, array($this->xmlParser, 'dataHandler'));

            if (!xml_parse($xml_parser, $xml, true)) {
                die(sprintf("XML error: %s at line %d",
                    xml_error_string(xml_get_error_code($xml_parser)),
                    xml_get_current_line_number($xml_parser)));
            }
        }
        $content = (string)$this->xmlParser->getContent();
        return $content;
    }

    public function parseRefDocoFile($refFile) 
    {
        //ld($refFile);
        $functionDocs = array();
        $dom = @DomDocument::loadHTMLFile($this->docLocation . "/" . $refFile);
        $query = '//*[@class="chunklist chunklist_reference"]//a';
        $xpath = new DOMXPath($dom);
        $entries = $xpath->query($query);
        foreach($entries as $methodEntry) {
            $methodLocation = $methodEntry->attributes->getNamedItem('href')->nodeValue;
            $methodName = $methodEntry->nodeValue;
            $functionDocs[$methodName] = $this->parseMethodDocFile($methodLocation);
        }
        return $functionDocs;
    }

    public function parsePredefinedConstants($constFile)
    {
        //ld($constFile);
        $constants = array();
        $dom = @DomDocument::loadHTMLFile($this->docLocation . "/" . $constFile);
        $dtQuery = '//*[@class="appendix"]//span[@class="term"]/strong/code';
        $trQuery = '//*[@class="chapter"]//td/strong/code';
        $xpath = new DOMXPath($dom);
        $entries = $xpath->query($dtQuery);
        if($entries->length == 0) {
            $entries = $xpath->query($trQuery);
        }

        foreach($entries as $constantEntry) {
            $constants[] = $constantEntry->nodeValue;
        }
        return $constants;
    }

    public function parseClassDocFile($classFile) 
    {
        //ld($classFile);
        $className= "";

        $dom = @DomDocument::loadHTMLFile($this->docLocation . "/" . $classFile);
        $propertyQuery = '//*[@class="fieldsynopsis"]';
        $methodQuery = '//*[@class="methodsynopsis dc-description"]//a[@class="methodname"]';
        $classNameQuery = '//*[@class="classsynopsisinfo"]//strong[@class="classname"]';
        $xpath = new DOMXPath($dom);
        $propertyEntries = $xpath->query($propertyQuery);
        $methodEntries = $xpath->query($methodQuery);
        $classNameEntries = $xpath->query($classNameQuery);
        foreach ($classNameEntries as $cq) {
            $className = $cq->nodeValue;
        }

        $classData = $this->getEmptyClassData($className);
        $fullDoc = utf8_encode(trim($this->parseFullDoc($classFile)));
        $classData['docComment'] = $fullDoc;



        foreach ($propertyEntries as $propertyEntry) {
            $propertyData = array(
                'type' => "",
                'Inheritdoc' => 0,
                'docComment' => $fullDoc,
            );
            $propertyModifiers = array();
            $propertyName = "";
            $propertyType = "";
            $propertyEntryXML = simplexml_import_dom($propertyEntry);
            //ld($propertyEntryXML->asXML());
            foreach ($propertyEntryXML->children() as $propertyChild) {
                switch ($propertyChild['class']) {
                case 'modifier':
                    if((string)$propertyChild == "readonly") {
                        continue;
                    }
                    if((string) $propertyChild == "") {
                        continue;
                    }
                    $propertyModifiers[] = (string) $propertyChild;
                    break;
                case 'varname':
                case 'fieldsynopsis_varname':
                    //ld($propertyEntryXML->asXML());
                    $propertyName = isset($propertyChild->a->var)?(string) $propertyChild->a->var : (string)$propertyChild->var;
                    break;
                case 'type':
                    $propertyType = isset($propertyChild->a)? (string) $propertyChild->a : (string) $propertyChild;
                default:

                    break;
                }

            }

            $propertyData['type'] = $this->isScalar($propertyType)? "" : $propertyType;
            $propertyData['modifier'] = $propertyModifiers;

            foreach ($propertyModifiers as $propertyModifier) {
                $classData['properties']['modifier'][$propertyModifier][] = $propertyName;
            }
            if($propertyName != "") {
                $classData['properties']['all'][$propertyName] = $propertyData;
            }
        }

        foreach ($methodEntries as $methodEntry) {
            $methodData = array(
                'params' => array(),
                'docComment' => "",
                'signature' => "",
                'Inheritdoc' => 0
            );
            $methodXml = simplexml_import_dom($methodEntry);
            $methodDocFile = (string) $methodXml['href'];
            $methodName = (string) $methodXml;
            if(strpos($methodName, "::")) {
                $methodName = substr($methodName, strpos($methodName, "::")+2);
            }
            $methodData = $this->parseMethodDocFile($methodDocFile);
            $methodModifiers = $methodData['modifier'];

            if(empty($methodModifiers)) {
                $classData['methods']['modifier']['public'][] = $methodName;
            }

            foreach ($methodModifiers as $methodModifier) {
                $classData['methods']['modifier'][$methodModifier][] = $methodName;
            }
            $classData['methods']['all'][$methodName] = $methodData;
        }
        return $classData;
    }

    public function parsePredefinedClasses($fileLocation)
    {
        $dom = DomDocument::loadHTMLFile($this->docLocation . "/" . $fileLocation);
        $query = '//*[@class="chunklist chunklist_part"]/li/a';
        $xpath = new DOMXPath($dom);
        $entries = $xpath->query($query);
        foreach ($entries as $entry) {
            $sxml = simplexml_import_dom($entry);
            $className = (string) $sxml;
            $classLocation = (string) $sxml['href'];
            $this->classDocs[$className] = $this->parseClassDocFile($classLocation);
        }
    }


    public function parseSectionDocFile($sectionFile)
    {
        $classes = array();
        $functionDocs = array();
        $bookPropertyLinks = $this->getBookProperties($sectionFile);
        foreach ($bookPropertyLinks as $key => $location) {
            $locationType = substr($location, 0, strpos($location, "."));
            if($locationType == "class") {
                $this->classDocs[$key] = $this->parseClassDocFile($location);
            } else if($locationType == "ref") {
                $this->functionDocs = array_merge($this->functionDocs, $this->parseRefDocoFile($location));
            } elseif(strpos($location, 'constants') !== false) {
                $this->constants = array_merge($this->constants,  $this->parsePredefinedConstants($location));
            }
        }
    }

    private function isScalar($type) 
    {
        $scalarsTypes = array('boolean', 'integer','float', 'string', 'array', 'object', 
            'resource', 'mixed', 'number', 'callback', 'null', 'void', 'bool', 'self', 'int', 'callable');
        return in_array(strtolower($type), $scalarsTypes);
    }

    public function createDictFile()
    {
        $signatures = array();
        foreach ($this->functionDocs as $function) {
            $signatures[] = $function['signature'];
        }
        file_put_contents('php.dict', join("\n", $signatures));
    }

    private function getEmptyClassData($className) 
    {
        $classData = array();
        $classData['classname'] = $className;
        $classData['docComment'] = "";
        $classData['methods'] = array(
            'modifier' => array(
                'public' => array(),
                'private' => array(),
                'protected' => array(),
                'final' => array(),
                'static' => array(),
                'interface' => array(),
                'abstract' => array(),
            ),
            'all' => array("nnnnnnnn" => "nnnnnnnnnnnn")
        );
        $classData['properties'] = array(
            'modifier' => array(
                'public' => array(),
                'private' => array(),
                'protected' => array(),
                'final' => array(),
                'static' => array(),
                'interface' => array(),
                'abstract' => array(),
            ),
            'all' => array("nnnnnnnn" => "nnnnnnnnnnnn")
        );
        $classData['parentclass'] = "";
        $classData['parentclasses'] = array();
        $classData['interfaces'] = array();
        $classData['file'] = "";
        $classData['namespaces'] = array(
            'uses' => array()
        );

        $classData['constants'] = array();

        return $classData;
    }

}
