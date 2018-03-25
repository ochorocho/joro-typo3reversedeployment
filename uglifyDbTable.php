<?php

$sqlFile = file_get_contents(dirname(__FILE__) . '/sql/2018031703-c1typo3.sql');

/**
 * Define table fields to obfuscate
 */
$tablesToFind = [
    'be_users' => ['username','password','pid','category_perms'],
    'tt_content' => ['title','bodytext'],
];

$tablesFieldReplace = [
    'be_users' => [
        'pid' => -666,
        'category_perms' => null,
        'username' => "NiceUsername",
        'password' => "ReplacePasswordField"
    ],
    'tt_content' => [
        'title' => "tt_content title",
        'bodytext' => "tt_content bodytext tt_content bodytext tt_content bodytext tt_content bodytext tt_content bodytext"
    ],
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

$replace = [];
$pattern = [];

foreach ($tableFieldArray as $table => $fieldInsert) {
    /**
     * Match all $table
     */
    preg_match_all("/INSERT\sINTO\s\`$table\`\sVALUES\s\((.*?)\);/ms", $sqlFile, $tableInsert);

    foreach ($tableInsert[1] as $key => $insert) {

        $singleInsert = explode('),(', $insert);
        foreach ($singleInsert as $key => $single) {

            $singleField = explode(',', preg_replace("/^\((.*?)/ms", '', $single));
            /**
             * Modify fields
             */
            $singleFieldBack = [];
            foreach ($tableFieldArray[$table] as $key => $field) {

                if(is_numeric($singleField[$key]) || is_null($singleField[$key])) {
                    $replaceValue = $tablesFieldReplace[$table][$field];
                } else {
                    $replaceValue = "'" . $tablesFieldReplace[$table][$field] . "'";
                }
                $singleField[$key] = $replaceValue;
            }
            /**
             * Put back together single fields
             */
            $singleFieldBack[] = implode(',', $singleField);
        }
        /**
         * Complete INSERT statement
         */
        $newInsert = "INSERT INTO `" . $table . "` VALUES (" . implode('),(',$singleFieldBack) . ");" . PHP_EOL;
        $originInsert = "INSERT INTO `" . $table . "` VALUES (" . $insert . ");" . PHP_EOL;

        $pattern[] = '/' . preg_quote($originInsert, '/') . '/';
        $replace[] = $newInsert;
    }
}

$resultSql = preg_replace(
    $pattern,
    $replace,
    $sqlFile
);
file_put_contents("hooray.sql", $resultSql);
