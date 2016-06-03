<?php

class ctrl_CheckReferer
{
    function Check($options)
    {
        return (strpos($_SERVER["HTTP_REFERER"],
            $options->GetOption('basepath')) === 0);
    }
}
?>
