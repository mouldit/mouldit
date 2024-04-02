<?php

namespace components\groups;

use Exception;

class ContentInjection
{
    private array $contentInjection=[];

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $args = func_get_args();
        echo '<pre>'.print_r($args, true).'</pre>';
        foreach ($args as $arg){
            if(!is_string($arg)) throw new Exception('A content-injection property name needs to be of type string');
            $this->contentInjection[$arg]=null;
        }
    }
    public function changeContentInjection(string $key,int $componentId=NULL)
    {
        try {
                if(!in_array($key,array_keys($this->contentInjection)))throw new Exception('illegal setting of variable');
                $this->contentInjection[$key]=$componentId;
        } catch (Exception $e) {
            return $e;
        }
    }
}