<?php

$sqlFile = file_get_contents(dirname(__FILE__) . '/sql/2018031701-c1typo3.sql');


/**
 * Define table fields to obfuscate
 */
$tablesToFind = [
    'be_users' => ['username','password'],
    'tt_content' => ['title','bodytext'],
    //'backend_layout' => ['description','name']
];

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

//  print_r($tableFieldArray);

foreach ($tableFieldArray as $table => $fieldInsert) {
    /**
     * Match all $table
     */
    preg_match_all("/INSERT\sINTO\s\`$table\`\sVALUES\s\((.*?)\);/ms", $sqlFile, $tableInsert);
    foreach ($tableInsert[1] as $key => $insert) {
        $singleInsert = explode('),', $insert);
        foreach ($singleInsert as $key => $single) {
            $singleField = explode(',', preg_replace("/^\((.*?)/ms", '', $single));
            /**
             * Modify fields
             */
            foreach ($tableFieldArray[$table] as $key => $field) {
                //$singleField[$key] = "'XXXXXXX'";
            }
            /**
             * Put back together single fields
             */
            $singleFieldBack[] = implode(',', $singleField);
        }
        /**
         * Put back together single inserts
         */
        $singleInsertBack = implode('),(', $singleFieldBack);

        /**
         * Complete INSERT statement
         */
        echo "INSERT INTO `" . $table . "` VALUES (" . $singleInsertBack . ");" . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;

    }
}

// print_r($fields);

// Working queries

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
