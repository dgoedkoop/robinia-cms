<?php

require_once 'model/database.php';
require_once 'checkreferer.php';

/*
 * jQuery code for ajax editor. Unfininshed.
        ?>
	<script type="text/javascript" src="../js/jquery.js"></script>
	<script type="text/javascript" src="../js/jquery.showHtml.js"></script>
	<script type="text/javascript">
        function addJQueryHandlers() {
            $("a.editlink").click( function() {
                var id = $(this).attr('id');
                var x = $(this);
                $.get("index.php?c=ajax&a=form&id=" + id, function(data) {
                    x.parent().parent().showHtml(data, 400, addJQueryHandlers);
                });
            });
            $(".editcancel").click( function() {
                var id = $(this).attr('id');
                var x = $(this);
                $.get("index.php?c=ajax&a=show&id=" + id, function(data) {
                    x.parent().parent().showHtml(data, 400, addJQueryHandlers);
                });
            });
            $(".editorsubfirst").click( function() {
                var id = $(this).attr('id');
                var x = $(this);
                $.get("index.php?c=ajax&a=newElement&parent_id=" + id, 
                    function(data) {
                    x.parent().replaceWith(data);
                    addJQueryHandlers();
                });
            });
            $(".editorsubafter").click( function() {
                var id = $(this).attr('id');
                var x = $(this);
                $.get("index.php?c=ajax&a=newElement&after_element=" + id,
                    function(data) {
                    x.parent().replaceWith(data);
                    addJQueryHandlers();
                });
            });
            $(".editnewcancel").click( function() {
                parent_id = $(this).parent().children('[name="parent_id"]').attr('value');
                after_element = $(this).parent().children('[name="after_element"]').attr('value');
                var x = $(this);
                if (typeof(parent_id) != "undefined") {
                    $.get("index.php?c=ajax&a=newCancel&parent_id=" + parent_id,
                        function(data) {
                        x.parent().parent().replaceWith(data);
                        addJQueryHandlers();
                    });
                } else if (typeof(after_element) != "undefined") {
                    $.get("index.php?c=ajax&a=newCancel&after_element=" + after_element,
                        function(data) {
                        x.parent().parent().replaceWith(data);
                        addJQueryHandlers();
                    });
                }
            });
            $(".editnewchoose").click( function() {
                var btn = $(this);
                var form = btn.parent();
                btn.attr("disabled", true);
                var formdata = form.serialize();
                $.post("index.php?c=ajax&a=emptyForm", formdata, function(data) {
                    form.parent().replaceWith(data);
                    addJQueryHandlers();
                });
            });
        }
        $(document).ready(addJQueryHandlers);
        </script>
<?
 */
/* Even ter informatie: de veranderingen tijdens het toevoegen van een nieuw
 * sub-element:
 * stap 1: <div class=editorlinkbox after_element=old_id />
 * stap 2: <div class=editornewwindow after_element=old_id />
 * stap 3: <div class=editorlinkbox after_element=old_id />
 *         <div class=editorframe id=new_id />
 *         <div class=editorlinkbox after_element=new_id />
 */
class ctrl_Ajax
{
    private $db;
    private $options;
    
    public function __construct($options)
    {
        $this->options = $options;
        $this->options->SetOption('template', 'editor');
        require_once 'template/editor/page.php';
    }

    private function SetupDB()
    {
        $this->db = new mod_Database($this->options);
        if (!$this->db->Connect()) {
            die('Kon geen verbinding maken.');
        }
    }
    public function Show(array $parameters)
    {
        if (!ctrl_CheckReferer::Check($this->options)) {
            throw new Exception('Invalid referer.');
        }
        if (isset($parameters['id'])) {
            $element_id = $parameters['id'];
        } else {
            throw new InvalidArgumentException('Invalid element ID.');
        }
        $this->SetupDB();

        $element = $this->db->LoadElement($element_id);
        $page = new tpl_Page();
        $outelement = $page->ConvertModelToTpl($element);
        
        echo $page->ContentsForFrame($outelement);
    }
    public function Form(array $parameters)
    {
        if (!ctrl_CheckReferer::Check($this->options)) {
            throw new Exception('Invalid referer.');
        }
        if (isset($parameters['id'])) {
            $element_id = $parameters['id'];
        } else {
            throw new InvalidArgumentException('Invalid element ID.');
        }
        $this->SetupDB();

        $this->db->SetLoadRecursive(false);
        $element = $this->db->LoadElement($element_id);
        $page = new tpl_Page();
        $outelement = $page->ConvertModelToTpl($element);
        echo $page->FormForFrame($outelement);
    }
    private function NewElementFromType($type)
    {
        foreach ($this->options->GetOption('classlist') as $classbase) {
            $classname = 'mod_' . $classbase;
            $callable_typename = array($classname, 'GetName');
            if (is_callable($callable_typename)) {
                $name = call_user_func($callable_typename);
                if ($name == $type) {
                    return new $classname();
                }
            }
        }
        return null;
    }
    public function EmptyForm(array $parameters)
    {
        if (!ctrl_CheckReferer::Check($this->options)) {
            throw new Exception('Invalid referer.');
        }
        if (isset($_POST['type'])) {
            $element = $this->NewElementFromType($_POST['type']);
        }
        if (!$element) {
            throw new InvalidArgumentException('Invalid element type.');
        }
        $page = new tpl_Page();
        $outelement = $page->ConvertModelToTpl($element);
        echo '<div class="editorframe">'
           . $page->FormForFrame($outelement)
           . '</div>';
    }
    public function NewElement(array $parameters)
    {
        $output = '<div class="editornewchoice"><form>';
        if (isset($parameters['parent_id'])) {
            $output .= '<input type="hidden" name="parent_id" value="'
                     . htmlspecialchars($parameters['parent_id']) . '">';
        }
        if (isset($parameters['after_element'])) {
            $output .= '<input type="hidden" name="after_element" value="'
                     . htmlspecialchars($parameters['after_element']) . '">';
        }
        $output .= '<label for="type">Type voor nieuw element:</label>'
                 . '<select name="type">';
        foreach ($this->options->GetOption('classlist') as $classbase) {
            $classname = 'tpl_' . $classbase;
            $callable_typename = array($classname, 'TypeName');
            if (is_callable($callable_typename)) {
                $screenname = call_user_func($callable_typename);
            }
            $callable_name = array($classname, 'GetName');
            if (is_callable($callable_name)) {
                $name = call_user_func($callable_name);
            }
            if (isset($screenname) && isset($name)) {
                $output .= '<option value="' . $name . '">' . $screenname 
                        . '</option>';
            }
        }

        $output .= '<input type=submit class=editnewchoose value="OK">'
                 . '<input type=reset class=editnewcancel value="Annuleren">'
                 . '</form></div>';
        echo $output;
    }
    public function NewCancel(array $parameters)
    {
        if (isset($parameters['parent_id'])) {
            echo tpl_Page::EditSubFirst($parameters['parent_id']);
        } elseif (isset($parameters['after_element'])) {
            echo tpl_Page::EditSubFirst($parameters['after_element']);
        }
    }
}
?>
