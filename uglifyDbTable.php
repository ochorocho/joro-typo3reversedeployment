<?php

//namespace PHPSQLParser;
//require_once dirname(__FILE__) . '/vendor/autoload.php';

/**
 * Define tables to obfuscate
 */
$tablesToFind = ['be_users' => ['username','password'],'backend_layout' => ['description','name']];

$tableFieldArray = [];

/**
 * Build array of tables and fields to work with
 * $table => [$field1, $field2]
 */
foreach ($tablesToFind as $table => $fieldsToReplace) {

    /**
     * Find Table Create statement
     */
    preg_match_all("/CREATE\sTABLE\s\`$table\`\s\((.*?)^\)/ms", $sqlFile, $tableFields);

    /**
     * Find all fields in table
     * - Check for available fields
     * - Compare fields to replace/anonymize
     */
    foreach ($tableFields as $field) {
        preg_match_all("/\s\s\`(.*?)\`/ms", $field[0], $fieldname);
        $tableFieldArray[$table] = array_intersect($fieldname[1], $fieldsToReplace);
    }

}

print_r($tableFieldArray);



// print_r($fields);

// WOrking queries

/**
 * Get CREATE TABLE
 */
// preg_match_all("/CREATE\s+TABLE\s+\`?(\w+)/i", $input_lines, $output_array);


//
//$sql = 'SELECT 1';
//echo $sql . "\n";
//$start = microtime(true);
//$parser = new PHPSQLParser($sql, true);
//$stop = microtime(true);
//print_r($parser->parsed);
//echo "parse time simplest query:" . ($stop - $start) . "\n";
