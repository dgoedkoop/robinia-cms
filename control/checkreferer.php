<?php

class ctrl_CheckReferer
{
    function Check()
    {
        return (strpos($_SERVER["HTTP_REFERER"],
            mod_Options::instance()->GetOption('basepath')) === 0);
    }
}
?>
