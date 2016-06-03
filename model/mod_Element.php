<?php

interface mod_iElement {
    /*
     * For identification
     */
    static function GetName();
    /*
     * This function should return the table name this type of element uses
     * for storing data, or <false> if it does not need storage.
     */
    static function GetDbTableName();
}

abstract class mod_Element implements mod_iElement {
    protected $children = array();
    protected $element_id;
    protected $time_created;
    protected $time_modified;
    protected $status = 'p';
    protected $fullyloaded = 'y';
    protected $permissions = null;
    protected $currentuser = null;
    protected $options = null;
    
    public function __construct(mod_Options $options)
    {
        $this->options = $options;
    }
    public function SetTimeCreated($time)
    {
        $this->time_created = $time;
    }
    public function GetTimeCreated()
    {
        return $this->time_created;
    }
    public function SetTimeModified($time)
    {
        $this->time_modified = $time;
    }
    public function GetTimeModified()
    {
        return $this->time_modified;
    }
    public function SetCurrentUser($user)
    {
        $this->currentuser = $user;
    }
    public function GetCurrentUser()
    {
        return $this->currentuser;
    }
    public function SetPermissions(mod_Permissions $permissions)
    {
        $this->permissions = $permissions;
    }
    public function GetPermissions()
    {
        return $this->permissions;
    }
    public function SetFullyLoaded($fullyloaded)
    {
        $this->fullyloaded = $fullyloaded;
    }
    public function GetFullyLoaded()
    {
        return $this->fullyloaded;
    }
    public function SetStatus($status)
    {
        $this->status = $status;
    }
    public function GetStatus()
    {
        return $this->status;
    }
    public function SetID($id)
    {
	$this->element_id = $id;
    }
    public function GetID()
    {
	return $this->element_id;
    }
    public function GetChildren()
    {
	return $this->children;
    }
    public function AddChild(mod_Element $child_element)
    {
	$this->children[] = $child_element;
    }
    public function AddChildren(array $children)
    {
	$this->children = array_merge($this->children, $children);
    }
    public function SetChildren(array $children)
    {
	$this->children = $children;
    }
    public function FindPossibleChildClasses($options)
    {
        $result = array();
        $childlimit = $this->LimitChildren();
        foreach ($options->GetOption('classlist') as $classbase) {
            $classname = 'mod_' . $classbase;
            /*
             * First check if we allow $classname as a child ourselves.
             */
            if ($childlimit === false) {
                $childok = true;
            } else {
                $childok = false;
                foreach ($childlimit as $allowedclassbase) {
                    $allowedclassname = 'mod_' . $allowedclassbase;
                    if (($classname == $allowedclassname) ||
                        is_subclass_of($classname, $allowedclassname)) {
                        $childok = true;
                    }
                }
            }
            /*
             * Then check if $classname allows us as its parent.
             */
            if ($childok) {
                $child = new $classname($this->options);
                $parentlimit = $child->LimitParent();
                if ($parentlimit === false) {
                    $parentok = true;
                } else {
                    $parentok = false;
                    foreach ($parentlimit as $allowedclassbase) {
                        $allowedclassname = 'mod_' . $allowedclassbase;
                        if ((get_class($this) == $allowedclassname) ||
                            is_subclass_of($this, $allowedclassname)) {
                            $parentok = true;
                        }
                    }
                }
            }
            if ($childok && $parentok) {
                $result[] = $classbase;
            }
        }
        return $result;
    }
    public function CanHaveAsChild(mod_Element $child)
    {
        $type_ok = false;
        foreach($this->FindPossibleChildClasses($this->options) as $classbase) {
            $classname = 'mod_' . $classbase;
            if ((get_class($child) == $classname) ||
                is_subclass_of($child, $classname)) {
                $type_ok = true;
                break;
            }
        }
        $status_ok = (($child->GetStatus() == $this->status) ||
                      ($this->status == 'y'));
        return ($type_ok && $status_ok);
    }
    /*
     * This function should return the column names within aforementioned table
     * or <false> too.
     */
    public abstract function GetDbColumnNames();
    /*
     * When an element is retrieved from the database, for every row returned
     * this function is called.
     */
    public abstract function AddFromDb(array $row);
    /*
     * This function should return an array containing the rows which should
     * be added to the database. It should therefore always be either empty
     * or a two-dimensional array.
     */
    public abstract function GetForDb();
    /*
     * This function should return a string containing the part of the CREATE
     * TABLE statement with the column definitions specific to this element
     * type.
     */
    public abstract function GetDbTableDefinition();
    /*
     * This function should return an array containing the element types which
     * (or whose derivated classes) this element can have as parent. If there
     * is no restriction, the result should be false.
     */
    public abstract function LimitParent();
    /*
     * This function should return an array containing the element types which
     * (or whose derivated classes) this element can have as child. If there
     * is no restriction, the result should be false.
     */
    public abstract function LimitChildren();
}

?>
