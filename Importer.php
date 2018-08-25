<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Importer
 *
 * @author michael.hampton
 */
class Importer
{

    use Validation;
    
    /**
     *
     * @var type 
     */
    private $arrErrors = [];

    private $requiredHeaders = array(
        'CustomerRef',
        'Title',
        'FirstName',
        'LastName',
        'Address1',
        'Address2',
        'Address3',
        'Town',
        'County',
        'PostCode',
        'ContactNumber',
        'Email',
        'ProductCode',
        'Quantity',
        'channel'
    ); //headers we expect
    private $fields = array(
        'CustomerRef' => ['type' => 'String', 'required' => true],
        'Title' => ['type' => 'String', 'required' => true],
        'FirstName' => ['type' => 'String', 'required' => true],
        'LastName' => ['type' => 'String', 'required' => true],
        'Address1' => ['type' => 'String', 'required' => true],
        'Address2' => ['type' => 'String', 'required' => true],
        'Address3' => ['type' => 'String', 'required' => true],
        'Town' => ['type' => 'String', 'required' => true],
        'County' => ['type' => 'String', 'required' => true],
        'PostCode' => ['type' => 'String', 'required' => true],
        'ContactNumber' => ['type' => 'String', 'required' => true],
        'Email' => ['type' => 'Email', 'required' => true],
        'ProductCode' => ['type' => 'String', 'required' => true],
        'Quantity' => ['type' => 'Integer', 'required' => true],
        'channel' => ['type' => 'String', 'required' => true],
    ); //headers we expect

    /**
     * 
     * @param type $file
     */

    public function import ($file)
    {
        $arrData = $this->csv2array ($file);

        if ( !$arrData )
        {
            return false;
        }

        if ( !$this->validateHeaders ($arrData) )
        {

            return false;
        }

        if ( !$this->validate ($arrData) )
        {

            return false;
        }
        
        return true;
    }
    
    /**
     * 
     * @param type $arrData
     * @return boolean
     */
    private function validate ($arrData)
    {
        foreach ($arrData as $lineNo => $arrColumn) {
            
            $lineNo++;

            foreach ($arrColumn as $fieldName => $fieldValue) {

                if ( isset ($this->fields[$fieldName]) )
                {
                    $arrField = $this->fields[$fieldName];

                    if ( $arrField['required'] === true && trim ($fieldValue) === '' )
                    {

                        $this->arrErrors[] = "{$fieldName} is a required field - line {$lineNo}";
                    }

                    switch ($arrField['type']) {
                        case "String":
                            if ( !$this->isString ($fieldValue) )
                            {
                                $this->arrErrors[] = "{$fieldName} should be an string - line {$lineNo}";
                            }
                            break;

                        case "Integer":
                            if ( !$this->isInt ($fieldValue) )
                            {
                                $this->arrErrors[] = "{$fieldName} should be an integer - line {$lineNo}";
                            }
                            break;

                        case "Email":
                            if ( !$this->isEmail ($fieldValue) )
                            {

                                $this->arrErrors[] = "{$fieldName} should be an email - line {$lineNo}";
                            }
                            break;
                    }
                }
            }
        }
        
        return true;
    }

    /**
     * 
     * @param type $arrData
     * @return boolean
     */
    private function validateHeaders ($arrData)
    {
        $foundHeaders = array_keys ($arrData[0]);

        if ( empty ($foundHeaders) )
        {
            $this->arrErrors[] = "Headers do not match";
            return false;
        }

        if ( $foundHeaders !== $this->requiredHeaders )
        {

            return false;
        }

        return true;
    }

    /**
     * 
     * @param type $file
     * @param type $delim
     * @param type $encl
     * @param type $header
     * @return boolean
     */
    private function csv2array ($file, $delim = ',', $encl = '"', $header = true)
    {

        //File does not exist
        if ( !file_exists ($file) )
        {
            $this->arrErrors[] = "The file does not exist";
            return false;
        }

        //Read lines of file to array
        $file_lines = file ($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        //Empty file
        if ( $file_lines === array() )
        {
            $this->arrErrors[] = "The file is empty";
            return NULL;
        }

        //Read headers if you want to
        if ( $header === true )
        {
            $line_header = array_shift ($file_lines);
            $array_header = array_map ('trim', str_getcsv ($line_header, $delim, $encl));
        }

        $out = NULL;

        //Now line per line (strings)
        foreach ($file_lines as $line) {

            # Skip empty lines
            if ( trim ($line) === '' )
            {
                $this->arrErrors[] = "Empty lines found during import";
                continue;
            }

            //Convert line to array
            $array_fields = array_filter (array_map ('trim', str_getcsv ($line, $delim, $encl)));

            // check array not empty and has values
            if ( !$array_fields )
            {
                $this->arrErrors[] = "Empty lines found during import";
                continue;
            }

            // Remove any invalid or hidden characters
            $array_fields = preg_replace ('/[\x00-\x1F\x80-\xFF]/', '', $array_fields);

            if ( !$array_fields )
            {
                continue;
            }

            //If header present, combine header and fields as key => value
            if ( $header === true )
            {
                $out[] = array_combine ($array_header, $array_fields);
            }
            else
            {
                $out[] = $array_fields;
            }
        }

        return $out;
    }
    
    /**
     * 
     * @return type
     */
    public function getArrErrors ()
    {
        return $this->arrErrors;
    }

}
