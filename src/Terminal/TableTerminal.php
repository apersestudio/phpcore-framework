<?php

namespace PC\Terminal;

class TableTerminal {

    /**
     * tabulated($data, [
     *     "order"=>[SORT_ASC, SORT_STRING]
     *     "signature"=>[SORT_ASC, SORT_STRING]
     * ])
     * @param array $data 
     * @param array $orderBy 
     * @return array 
     */
    public static function tabulated(array $data, array $orderByInfo):void {

        $lengths = [];
        $multisort_args = [];

        foreach ($orderByInfo as $orderBy=>$info) {
            $column = array_column($data, $orderBy);
            $order = $info[0];
            $dataType = $info[1];
            // Multisort uses the arrange column data, order by, data type
            // And this arrange can repeat on each iteration
            $multisort_args[] = $column;
            $multisort_args[] = $order;
            $multisort_args[] = $dataType;
            // The lengths are used for printf to format the output
            $lengths[$orderBy."_length"] = max(array_map("strlen", $column)) + 2;
        }

        // Add the data to be ordered at the end of the multisort as a reference
        $multisort_args[] = &$data;

        // After calling multisort, the results will be applied to the reference
        call_user_func_array("array_multisort", $multisort_args);
        
        foreach ($data as $index=>$row) {
            
            $formats = [];
            $arguments = [];
            foreach ($row as $column=>$value) {
                $formats[] = "%-".$lengths[$column."_length"]."s";
                $arguments[] = $value;
            }

            $line = implode(" ", $formats)."\n";
            array_unshift($arguments, $line);
            call_user_func_array("printf", $arguments);
            
        }
    }

}

?>