<?php

class tpl_CookieBox extends mod_Container
{
    public function GetOutput()
    {
        $output = '';
        if (!mod_ClientStorage::instance()->GetOption('CookiesDismissed')) {
            $output .= '<script>'
                    . 'function ajaxSetCookies() {'
                    . 'var xmlHttp = new XMLHttpRequest();'
                    . 'xmlHttp.open("GET", "index.php?c=page&a=AjaxSetClientStorage&CookiesDismissed=1&GoogleCookies=1", false );'
                    . 'xmlHttp.send();'
                    . 'location.reload();'
                    . '}'
                    . '</script>'
                    . '<p><span class="cookiebox"><span class="cookieboxtext">'
                    . tr('cookiewarning')
                    . '</span>'
                    . '<span class="cookieboxbuttonarea"><span class="cookieboxbutton"><span class="cookieboxbuttontext">'
                    . '<a href="javascript:;" onclick="ajaxSetCookies()">'.tr('accept').'</a>'
                    . '</span></span></span></p>';
        }
        return $output;
    }
}

?>