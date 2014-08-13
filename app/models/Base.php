<?php

use LaravelBook\Ardent\Ardent;

class Base extends Ardent
{

    //Ardent / Modification for validation.
    public function validate(array $rules = array(), array $customMessages = array())
    {
        //If custome rules are not being applied, then use default rules in class
        if (empty($rules)) {
            if ($this->exists && isset($this->update_rules)) {
                static::$rules = $this->update_rules;
            } elseif (!$this->exists && isset($this->create_rules)) {
                static::$rules = $this->create_rules;
            }
            $rules = $this->ignoreCurrentRecordForUnique();
        }
        return parent::validate($rules, $customMessages);
    }

    //Ardent / Modification for validation.
    //If an item is marked as unique, the rule should change to the format: 'unique:users,email_address,10'  where 10 is the id of the record in quesion.
    protected function ignoreCurrentRecordForUnique()
    {
        $rules = static::$rules;
        if ($this->exists && array_key_exists('id', $this->attributes)) {
            foreach ($rules as $field => &$rls) {
                if (is_string($rls)) {
                    $rlsreplace = '';
                    $rlsarray = explode('|', $rls);
                    foreach ($rlsarray as $onerl) {
                        if (strpos($onerl, ':') !== false) {
                            list($rule, $parameter) = explode(':', $onerl, 2);
                            $parameters = str_getcsv($parameter);
                            // if rule is 'unique' and table is same as field table being validated  and ignore parameter is not already set
                            if ($rule == 'unique' && (count($parameters) == 2 || count($parameters) == 1 ) && $parameters[0] == $this->getTable()) {
                                if (count($parameters) == 1)
                                    $onerl .= ',' . $field . ',' . $this->attributes['id'];
                                else
                                    $onerl .= ',' . $this->attributes['id'];
                            }
                        }
                        if ($rlsreplace != '')
                            $rlsreplace .= '|';
                        $rlsreplace .= $onerl;
                    }
                    $rls = $rlsreplace;
                }
            }
        }
        return $rules;
    }

}
