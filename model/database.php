<?php

/* The database for this application looks roughly as following:
 * ELEMENT -> id, parent_id, order_index(int), no_of_children, creation_time, active(enum), type
 *  possible values for 'active':
 *  y (active)
 *  d (active, but marked for deletion)
 *  n (not active)
 *  p (not active, pending)
 * CONTAINER -> X
 * IMAGE_GALLERY -> X
 * IMAGE -> element_id, filename(str), alttext(str), caption(str), width(int), height(int)
 * PARAGRAPH -> element_id, text(str)
 * HEADING -> element_id, text(str)
 * LISTING_ITEM -> element_id, text(str), order_index(int)
 */

class mod_Database
{
    const recursive_no = 0;
    const recursive_yes = -1;
    const recursive_one_level = 1;
    const match_id = 1;
    const match_name = 2;
    const perm_over_store = 1;
    const perm_over_deactivate = 2;
    const perm_over_rollback = 4;
    const perm_over_commit = 8;
    const perm_over_changeperm = 16;
    const perm_over_view = 32;
    const commit_error = 1;
    const commit_nothing = 2;
    const commit_committed = 4;
    const tbl_eventlog_actionlength = 20;
    const tbl_eventlog_infolength = 100;
    const mode_view = 1;
    const mode_preview = 2;
    const mode_edit = 3;
    
    private $db;
    private $mode = self::mode_view;
    private $recursive = self::recursive_no;
    private $commit_affected_users;
    private $currentuser = null;
    private $permission_overrides = 0;
    private $user_cache = array();
    private $group_cache = array();
    
    public function ResetPermit()
    {
        $this->permission_overrides = 0;
    }
    public function SetPermitSetup()
    {
        $this->permission_overrides = self::perm_over_store +
                                      self::perm_over_commit +
                                      self::perm_over_changeperm +
                                      self::perm_over_view;
    }
    public function SetPermitCheckLogin()
    {
        $this->permission_overrides = self::perm_over_view;
        return true;
    }
    private function SetPermissionOverride($override)
    {
        $this->permission_overrides = $this->permission_overrides | $override;
    }
    private function UnsetPermissionOverride($override)
    {
        $this->permission_overrides = $this->permission_overrides & ~$override;
    }
    private function GetPermissionOverride($override)
    {
        return $this->permission_overrides & $override;
    }
    private function SetPermissionOverrides($overrides)
    {
        $this->permission_overrides = $overrides;
    }
    private function GetPermissionOverrides()
    {
        return $this->permission_overrides;
    }
    public function SetRecursive($recursive)
    {
        $this->recursive = $recursive;
    }
    public function GetRecursive()
    {
        return $this->recursive;
    }
    public function SetMode($mode)
    {
        $this->mode = $mode;
    }
    public function GetMode()
    {
        return $this->mode;
    }
    public function SetCurrentUser(mod_User $user)
    {
        $this->currentuser = $user;
    }
    public function GetCurrentUser()
    {
        return $this->currentuser;
    }
    public function Connect()
    {
        try {
            $options = mod_Options::instance();
            $this->db = 
                new PDO('mysql:host=' . $options->GetOption('db_hostname')
                      . ';dbname=' . $options->GetOption('db_database')
                      . ';charset=utf8',
                        $options->GetOption('db_username'),
                        $options->GetOption('db_password'));
            $this->db->query("SET NAMES 'utf8'");
        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
        return true;
    }
    public function SetDb($db)
    {
        $this->db = $db;
    }
    public function CheckError()
    {
        if ($this->db->errorCode() != '00000') {
            $errorinfo = $this->db->errorInfo();
            echo "SQL Error: ".$errorinfo[2]."\n";
            return false;
        } else
            return true;
    }
    public function IsValidElement($element)
    {
        if ($element === false) {
            return false;
        } elseif (is_null($element)) {
            return false;
        } elseif ($element instanceof mod_ElementPermissionDenied) {
            return false;
        } elseif ($element instanceof mod_Element) {
            return true;
        } else {
            return false;
        }
    }
    private function TypeNameToClassName($typename)
    {
        foreach (mod_Options::instance()->GetOption('classlist') as $classbase) {
            $classname = 'mod_' . $classbase;
            /* PHP bug: PHP at some web hosting providers acts stupidly when
             * combining is_callable or call_user_func with __autoload. */
            //$callable = array($classname, 'GetName');
            //if (is_callable($callable)) {
            //    if (call_user_func($callable) == $typename) {
            $x = new $classname();
            if (is_callable(array($x, 'GetName'))) {
                if ($x->GetName() == $typename) {
                    return $classname;
               }
            }
        }
        return false;
    }
    public function LoadUser($match, $mode)
    {
        if (($mode == self::match_id) && isset($this->user_cache[$match])) {
            return $this->user_cache[$match];
        }

        $oldmode = $this->GetMode();
                
        try {
            $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');
            $tbl_name = mod_User::GetDbTableName();
        
            if ($mode == self::match_id) {
                $sql = "SELECT element_id FROM {$tbl_prefix}{$tbl_name} "
                     . "WHERE uid = :id;";
            } elseif ($mode == self::match_name) {
                $sql = "SELECT element_id FROM {$tbl_prefix}{$tbl_name} "
                     . "WHERE username = :name;";
            } else {
                throw new IllegalArgumentException('Illegal mode.');
            }
            $statement = $this->db->prepare($sql);
            if ($mode == self::match_id) {
                $statement->bindParam(':id', $match);
            } elseif ($mode == self::match_name) {
                $statement->bindParam(':name', $match);
            }
            $statement->execute();
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $this->SetMode(self::mode_view);
                $mod_element = $this->LoadElement($row['element_id']);
                $this->SetMode($oldmode);
                if ($this->IsValidElement($mod_element)) {
                    $this->group_cache[$row['element_id']] = $mod_element;
                    return $mod_element;
                }
            }
            $this->SetMode($oldmode);
            return false;
        } catch (PDOException $e) {
            $this->SetMode($oldmode);
            trigger_error($e->getMessage());
            return false;
        }
    }
    public function LoadGroup($match, $mode)
    {
        if (($mode == self::match_id) && isset($this->group_cache[$match])) {
            return $this->group_cache[$match];
        }
        
        $oldmode = $this->GetMode();
        
        try {
            $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');
            $tbl_name = mod_Usergroup::GetDbTableName();
        
            if ($mode == self::match_id) {
                $sql = "SELECT element_id FROM {$tbl_prefix}{$tbl_name} "
                     . "WHERE gid = :id;";
            } elseif ($mode == self::match_name) {
                $sql = "SELECT element_id FROM {$tbl_prefix}{$tbl_name} "
                     . "WHERE groupname = :name;";
            } else {
                throw new IllegalArgumentException('Illegal mode.');
            }
            $statement = $this->db->prepare($sql);
            if ($mode == self::match_id) {
                $statement->bindParam(':id', $match);
            } elseif ($mode == self::match_name) {
                $statement->bindParam(':name', $match);
            }
            $statement->execute();
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $this->SetMode(self::mode_view);
                $mod_element = $this->LoadElement($row['element_id']);
                $this->SetMode($oldmode);
                if ($this->IsValidElement($mod_element)) {
                    $this->group_cache[$row['element_id']] = $mod_element;
                    return $mod_element;
                }
            }
            $this->SetMode($oldmode);
            return false;
        } catch (PDOException $e) {
            $this->SetMode($oldmode);
            trigger_error($e->getMessage());
            return false;
        }
    }
    public function LoadLink($linkname)
    {
        $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');
        
        try {
            $tbl_name = mod_TitleDescription::GetDbTableName();
        
            $sql = "SELECT element_id FROM {$tbl_prefix}{$tbl_name} "
                 . "WHERE linkname = :linkname;";
            $statement = $this->db->prepare($sql);
            $statement->bindParam(':linkname', $linkname);
            $statement->execute();
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $mod_element = $this->LoadElement($row['element_id']);
                if ($this->IsValidElement($mod_element)) {
                    return $mod_element;
                }
            }
            return false;
        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }
    public function LoadTypeAll($classbase)
    {
        $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');
        
        try {
            $classname = 'mod_' . $classbase;
            $type = $classname::GetName();
            $result = array();
        
            if ($this->mode == self::mode_edit) {
                $sql = "SELECT id FROM {$tbl_prefix}element "
                     . "WHERE type = :type;";
            } elseif ($this->mode == self::mode_view) {
                $sql = "SELECT id FROM {$tbl_prefix}element "
                     . "WHERE type = :type AND active IN ('y','d');";
            } elseif ($this->mode == self::mode_preview) {
                $sql = "SELECT id FROM {$tbl_prefix}element "
                     . "WHERE type = :type AND active IN ('y','p');";
            } else {
                return false;
            }
            $statement = $this->db->prepare($sql);
            $statement->bindParam(':type', $type);
            $statement->execute();
            while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $mod_element = $this->LoadElement($row['id']);
                if ($this->IsValidElement($mod_element)) {
                    $result[] = $mod_element;
                }
            }
            return $result;
        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }
    private function LoadElementData($element_id, $typename)
    {
        $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');
        $classname = $this->TypeNameToClassName($typename);
        if ($classname === false) {
            return false;
        }
        /*
         * Cannot use "$classname instanceof mod_Element", because a string
         * of course isn't a subclass of mod_Element. With is_subclass_of, this
         * is not a problem though.
         */
        if (!is_subclass_of($classname, 'mod_Element')) {
            return false;
        }
        $mod_element = new $classname();
        $tbl_name = $mod_element->GetDbTableName();
        if (!($tbl_name === false)) {
            $tbl_columns = '';
            foreach ($mod_element->GetDbColumnNames()
                as $column) {
                if ($tbl_columns != '') {
                    $tbl_columns .= ', ';
                }
                $tbl_columns .= $column;
            }
            $sql = "SELECT {$tbl_columns} FROM {$tbl_prefix}{$tbl_name} "
                 . "WHERE element_id = :id ORDER BY order_index;";
            $statement = $this->db->prepare($sql);
            $statement->bindParam(':id', $element_id);
            $statement->execute();
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $mod_element->AddFromDb($row);
            }
        }
        return $mod_element;
    }
    private function LoadElementChildren(mod_Element $mod_element, 
        $no_of_children, $level)
    {
        $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');

        /*
         * Do a full recursion if we haven't reached the max. depth
         * yet, but not if we have encountered a page, except when we
         * are at the root.
         */
        if ((($this->recursive == -1) || ($level < $this->recursive))
            && (($level == 0) || !($mod_element instanceof mod_Page))) {
            if ($this->mode == self::mode_edit) {
                $sql = "SELECT id FROM {$tbl_prefix}element "
                     . "WHERE parent_id = :id ORDER BY order_index;";
            } elseif ($this->mode == self::mode_view) {
                $sql = "SELECT id FROM {$tbl_prefix}element "
                     . "WHERE parent_id = :id AND active IN ('y','d') "
                     . "ORDER BY order_index;";
            } elseif ($this->mode == self::mode_preview) {
                $sql = "SELECT id FROM {$tbl_prefix}element "
                     . "WHERE parent_id = :id AND active IN ('y','p') "
                     . "ORDER BY order_index;";
            } else {
                return false;
            }
            $statement = $this->db->prepare($sql);
            $element_id = $mod_element->GetID();
            $statement->bindParam(':id', $element_id);
            $statement->execute();
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $child_element = $this->LoadElement($row['id'], $level + 1);
                if ($child_element) {
                    $mod_element->AddChild($child_element);
                }
            }
            $mod_element->SetFullyLoaded(true);
            return $mod_element;
        }
        /*
         * We are at the bottom of the recursion. Therefore, we should
         * stop now. However, if the current element is a page or a
         * folder, it is a good idea to still fetch the title and
         * description sub-element, if it exists.
         */
        if (($mod_element instanceof mod_Page) ||
            ($mod_element instanceof mod_Folder)) {
            if ($this->mode == self::mode_edit) {
                $sql = "SELECT id FROM {$tbl_prefix}element "
                     . "WHERE parent_id = :id ORDER BY order_index LIMIT 1;";
            } elseif ($this->mode == self::mode_view) {
                $sql = "SELECT id FROM {$tbl_prefix}element "
                     . "WHERE parent_id = :id AND active IN ('y','d') "
                     . "ORDER BY order_index LIMIT 1;";
            } elseif ($this->mode == self::mode_preview) {
                $sql = "SELECT id FROM {$tbl_prefix}element "
                     . "WHERE parent_id = :id AND active IN ('y','p') "
                     . "ORDER BY order_index LIMIT 1;";
            } else {
                return false;
            }
            $statement = $this->db->prepare($sql);
            $element_id = $mod_element->GetID();
            $statement->bindParam(':id', $element_id);
            $statement->execute();
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $child_element = $this->LoadElement($row['id'], false);
                if ($child_element instanceof mod_TitleDescription) {
                    $mod_element->AddChild($child_element);
                }
            }
            $mod_element->SetFullyLoaded(count(
                $mod_element->GetChildren()) == $no_of_children);
        }
        /*
         * We are really at the end of the allowed recursion depth, so
         * stop now.
         */
        $mod_element->SetFullyLoaded(false);
        return $mod_element;
    }
    private function LoadElementPermissions($element_id)
    {
        $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');
            
        $permissions = new mod_Permissions();
        $sql = "SELECT permissiontype, subject, permission "
             . "FROM {$tbl_prefix}acl WHERE element_id = :id;";
        $statement = $this->db->prepare($sql);
        $statement->bindParam(':id', $element_id);
        $statement->execute();
        while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            if ($row['permissiontype'] == 'u') {
                $permissions->SetUserPermission($row['subject'],
                    $row['permission']);
            } elseif ($row['permissiontype'] == 'g') {
                $permissions->SetGroupPermission($row['subject'],
                    $row['permission']);
            }
        }
        return $permissions;
    }
    /*
     * This function fetches a tree structure, starting with the specified
     * element as its root. The return value is the newly created root object.
     * The function can be configured to fetch all elements or only activated
     * ones.
     * 
     * Permission required: view
     */
    public function LoadElement($element_id, $level = 0)
    {
        $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');

        try {
            if ($this->mode == self::mode_edit) {
                $sql = "SELECT * FROM {$tbl_prefix}element WHERE id = :id;";
            } elseif ($this->mode == self::mode_view) {
                $sql = "SELECT * FROM {$tbl_prefix}element WHERE id = :id "
                     . "AND active IN ('y','d');";
            } elseif ($this->mode == self::mode_preview) {
                $sql = "SELECT * FROM {$tbl_prefix}element WHERE id = :id "
                     . "AND active IN ('y','p');";
            }
            $statement = $this->db->prepare($sql);
            $statement->bindParam(':id', $element_id);
            $statement->execute();
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                /*
                 * Load permissions and check for view permission (in case of
                 * active elements) or for view and edit permission (in case of
                 * inactive elements).
                 */
                $permissions = $this->LoadElementPermissions($element_id);
                if (($row['active'] == 'y') || ($row['active'] == 'd')) {
                    $haspermission = ($permissions->HasPermission(
                        $this->currentuser, mod_Permissions::perm_view) || 
                        $this->GetPermissionOverride(self::perm_over_view));
                } else {
                    $haspermission = ($permissions->HasPermission(
                        $this->currentuser, mod_Permissions::perm_view)
                        && $permissions->HasPermission($this->currentuser,
                        mod_Permissions::perm_edit)) || 
                        $this->GetPermissionOverride(self::perm_over_view);
                }
                if ($haspermission) {
                    /*
                     * Create element and load associated data
                     */
                    $mod_element = $this->LoadElementData($element_id, 
                        $row['type']);
                    /*
                     * If loading failed, because of an unsupported type,
                     * process this as a simple container.
                     */
                    if ($mod_element === false) {
                        $mod_element = new mod_Container();
                    }
                    $mod_element->SetID($element_id);
                    $mod_element->SetStatus($row['active']);
                    $mod_element->SetTimeCreated(
                        strtotime($row['creation_time']));
                    $mod_element->SetTimeModified(
                        strtotime($row['last_modified']));
                    $mod_element->SetPermissions($permissions);
                    $mod_element->SetCurrentUser($this->currentuser);
                    $no_of_children = $row['no_of_children'];

                    if ($no_of_children == 0) {
                        $mod_element->SetFullyLoaded(true);
                        return $mod_element;
                    }
                    
                    return $this->LoadElementChildren($mod_element, 
                        $no_of_children, $level);
                } else {
                    // View permission denied
                    $mod_element = new mod_ElementPermissionDenied();
                    $mod_element->SetID($element_id);
                    $mod_element->SetPermissions($permissions);
                    return $mod_element;
                }
            } else {
                // Element not found
                return false;
            }
        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }
    /*
     * Finds the parent element ID. It accepts both an instance of mod_Element
     * and a normal element ID as parameter.
     */
    public function ParentID($element)
    {
        $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');
        
        if ($element instanceof mod_Element) {
            $element_id = $element->GetID();
        } else {
            $element_id = $element;
        }

        try {
            $sql = "SELECT parent_id FROM {$tbl_prefix}element "
                 . "WHERE id = :id;";
            $statement = $this->db->prepare($sql);
            $statement->bindParam(':id', $element_id);
            $statement->execute();
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return $row['parent_id'];
            } else {
                return false;
            }
        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }
    /*
     * This function is for calling at the end of a commit cycle. The group
     * membership table (allowing reverse search to find users belonging to a
     * group) is updated for the specified user ID.
     */
    private function UpdateGroupMemberships($uid, $groups)
    {
        $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');

        $sql_d = "DELETE FROM {$tbl_prefix}groupmembership WHERE uid = :uid;";
        $sql_i = "INSERT INTO {$tbl_prefix}groupmembership SET "
               . "uid = :uid, gid = :gid";
        
        $statement_d = $this->db->prepare($sql_d);
        $statement_i = $this->db->prepare($sql_i);
        $statement_d->bindParam(':uid', $uid);
        $statement_i->bindParam(':uid', $uid);
        $statement_d->execute();
        foreach($groups as $group_id) {
            $statement_i->bindParam(':gid', $group_id);
            $statement_i->execute();
        }
        return true;
    }
    /*
     * For the specified element and its parents, update the last modified
     * timestamp.
     * 
     * Permissions required: none
     */
    private function UpdateMTimes(mod_Element $mod_element)
    {
        $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');
        
        $update_sql = "UPDATE {$tbl_prefix}element "
             . "SET last_modified = NOW() WHERE id = :id";
        $update_statement = $this->db->prepare($update_sql);
        $element_id = $mod_element->GetID();
        while ($element_id != 0) {
            $update_statement->bindParam(':id', $element_id);
            $update_statement->execute();
            $element_id = $this->ParentID($element_id);
        }
    }
    /*
     * Restore element to active state when marked for deletion, or delete it
     * when marked for creation.
     * 
     * Permission required: edit (override: perm_over_rollback)
     */
    private function RollbackElement(mod_Element $mod_element)
    {
        $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');
       
        if (!$mod_element->GetPermissions()->HasPermission($this->currentuser,
            mod_Permissions::perm_edit) && 
            !$this->GetPermissionOverride(self::perm_over_rollback)) {
            return false;
        }
        
        // Fetch some information
        if ($mod_element->GetStatus() == 'd') {
            $sql = "UPDATE {$tbl_prefix}element SET active = 'y' "
                 . "WHERE id = :id";
            $statement = $this->db->prepare($sql);
            $statement->bindParam(':id', $mod_element->GetID());
            $statement->execute();
            $mod_element->SetStatus('y');
            $this->UpdateCaches($mod_element);
        }
        if ($mod_element->GetStatus() == 'p') {
            $sql = "DELETE FROM {$tbl_prefix}element WHERE id = :id;";
            $statement = $this->db->prepare($sql);
            $statement->bindParam(':id', $mod_element->GetID());
            $statement->execute();
            $this->RemoveFromCaches($mod_element);
            $subtable = $mod_element->GetDbTableName();
            if ($subtable) {
                $sql = "DELETE FROM {$tbl_prefix}{$subtable} "
                     . "WHERE element_id = :id;";
                $statement = $this->db->prepare($sql);
                $statement->bindParam(':id', $mod_element->GetID());
                $statement->execute();
            }
        }
        return true;
    }
    /*
     * Activate or inactivate the specified element if a change is pending.
     * It returns 'false' on failure. In case of success it returns the number
     * of children the element has, so that one knows whether to iterate over
     * them.
     * 
     * Permission required: commit (override: perm_over_commit)
     */
    private function CommitElement(mod_Element $mod_element)
    {
        $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');

        /*
         * If we don't need to do anything, don't do anything. Especially, do
         * not give access denied errors in that case.
         */
        if (($mod_element->GetStatus() != 'd') && 
            ($mod_element->GetStatus() != 'p')) {
            return self::commit_nothing;
        }
        
        if (!$mod_element->GetPermissions()->HasPermission($this->currentuser,
            mod_Permissions::perm_commit) &&
            !$this->GetPermissionOverride(self::perm_over_commit)) {
            return self::commit_error;
        }

        /* 
         * Go update the element.
         */
        if ($mod_element->GetStatus() == 'd') {
            $new_state = 'n';
            $update_lm = '';
        } elseif ($mod_element->GetStatus() == 'p') {
            $new_state = 'y';
            $update_lm = ', last_modified = NOW() ';
        }
        $mod_element->SetStatus($new_state);
        $this->UpdateCaches($mod_element);

        $element_id = $mod_element->GetID();
        $update_sql = "UPDATE {$tbl_prefix}element SET active = :newstate "
             . "{$update_lm} WHERE id = :id";
        $update_statement = $this->db->prepare($update_sql);
        $update_statement->bindParam(':id', $element_id);
        $update_statement->bindParam(':newstate', $new_state);
        $update_statement->execute();
        
        /* 
         * If we defintively deactivate an old user object, clean up the old
         * password. After all, obsolete passwords should not be kept forever.
         */
        if (($mod_element instanceof mod_User) && ($new_state == 'n')) {
            $tbl_name = $mod_element->GetDbTableName();
            $sql = "UPDATE {$tbl_prefix}{$tbl_name} SET password = '' "
                 . "WHERE element_id = :id";
            $statement = $this->db->prepare($sql);
            $statement->bindParam(':id', $element_id);
            $statement->execute();
        }
        
        /*
         * Updating the reverse search (gid -> uid's) table. If a user element
         * gets activated, we should update the group memberships. If a user
         * element gets deactivated, we should remove the group memberships. If
         * both apply for the same UID, we should make sure that the correct
         * information will be recorded and that not all group memberships get
         * lost in the reverse search table.
         */
        if ($mod_element instanceof mod_User) {
            $uid = $mod_element->GetUID();
            if ($new_state == 'y') {
                $this->commit_affected_users[$uid] = 
                    $mod_element->GetGroups();
            } elseif (($new_state == 'n') 
                && !isset($this->commit_affected_users[$uid])) {
                $this->commit_affected_users[$element_id] = array();
            }
        }
        return self::commit_committed;
    }
    /* 
     * Delete the specified element if creation was still pending, and rollback
     * any pending changes to its children.
     * 
     * Permission required: edit (override: perm_over_rollback; handled in
     * ::RollbackElement)
     */
    public function Rollback(mod_Element $mod_element)
    {
        $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');

        if ($this->mode != self::mode_edit) {
            return false;
        }
        if (($this->recursive != self::recursive_no) &&
            ($this->recursive != self::recursive_yes)) {
            return false;
        }

        try {
            // Rollback this element
            $this->RollbackElement($mod_element);
            // Handle children
            $handlechildren = ($this->recursive == self::recursive_yes) || 
                ($mod_element->GetStatus() == 'p');
            if ($handlechildren && (count($mod_element->GetChildren()) || 
                !$mod_element->GetFullyLoaded())) {
                $sql = "SELECT id FROM {$tbl_prefix}element "
                     . "WHERE parent_id = :id;";
                $statement = $this->db->prepare($sql);
                $statement->bindParam(':id', $mod_element->GetID());
                $statement->execute();
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $oldrecursive = $this->GetRecursive();
                    $this->SetRecursive(self::recursive_no);
                    $child = $this->LoadElement($row['id']);
                    $this->SetRecursive($oldrecursive);
                    if (!$this->Rollback($child)) {
                        return false;
                    }
                }
            }
            return true;
        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }
    /* 
     * Activate the specified element and commit any pending changes to its
     * children.
     * 
     * Permission required: commit (override: perm_over_commit; handled by
     * ::CommitElement)
     */
    public function Commit(mod_Element $mod_element, $level = 0)
    {
        if ($this->mode != self::mode_edit) {
            return self::commit_error;
        }

        try {
            $this->commit_affected_users = array();
            // Commit this element
            $status = $this->CommitElement($mod_element);
            // Handle children
            if (count($mod_element->GetChildren()) || 
                !$mod_element->GetFullyLoaded()) {
                if ($mod_element->GetFullyLoaded()) {
                    $children = $mod_element->GetChildren();
                } else {
                    $oldrecursive = $this->GetRecursive();
                    $this->SetRecursive(self::recursive_one_level);
                    $cpy_element = $this->LoadElement($mod_element->GetID());
                    $this->SetRecursive($oldrecursive);
                    if (!$this->IsValidElement($cpy_element)) {
                        return false;
                    }
                    $children = $cpy_element->GetChildren();
                }
                foreach($children as $child) {
                    $status = $status | $this->Commit($child, $level + 1);
                }
            }
            
            if ($level == 0) {
                if (($status & self::commit_committed) > 0) {
                    $this->UpdateMTimes($mod_element);
                }
                if (count($this->commit_affected_users) > 0) {
                    foreach($this->commit_affected_users as $uid => $groups) {
                        $this->UpdateGroupMemberships($uid, $groups);
                    }
                }
                $this->commit_affected_users = array();
            }
            return $status;
        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return self::commit_error;
        }
    }
    /*
     * Create an element. Returns the new element id.
     * 
     * Permission required: addchild (for parent, override: perm_over_store)
     */
    private function CreateElement(mod_Element $mod_element,
        $parent_element = null, $order_index = 0)
    {
        $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');
        
        if (!$this->GetPermissionOverride(self::perm_over_store)) {
            if (!$this->IsValidElement($parent_element)) {
                return false;
            }
            if (!$parent_element->GetPermissions()->HasPermission(
                $this->currentuser, mod_Permissions::perm_addchild)) {
                return false;
            }
        }

        if ($this->IsValidElement($parent_element)) {
            $parent_id = $parent_element->GetID();
        } else {
            $parent_id = 0;
        }

        $sql_u = "UPDATE {$tbl_prefix}element "
               . "SET order_index = order_index + 1 "
               . "WHERE parent_id = :parent_id "
               . "AND order_index >= :order_index";
        $sql_i = "INSERT INTO {$tbl_prefix}element "
               . "SET parent_id = :parent_id, order_index = :order_index, "
               . "creation_time = NOW(), last_modified = NOW(), active = 'p', "
               . "type = :element_type;";
        $statement_u = $this->db->prepare($sql_u);
        $statement_u->bindParam(':parent_id', $parent_id);
        $statement_u->bindParam(':order_index', $order_index);
        $statement_i = $this->db->prepare($sql_i);
        $statement_i->bindParam(':parent_id', $parent_id);
        $statement_i->bindParam(':order_index', $order_index);
        $statement_i->bindParam(':element_type', $mod_element->GetName());
        $statement_u->execute();
        $statement_i->execute();
        $mod_element->SetID($this->db->lastInsertID());
        $this->UpdateCaches($mod_element);
        if ($parent_id > 0) {
            $sql = "UPDATE {$tbl_prefix}element "
                 . "SET no_of_children = no_of_children + 1 "
                 . "WHERE id = :parent_id";
            $statement = $this->db->prepare($sql);
            $statement->bindParam(':parent_id', $parent_id);
            $statement->execute();
        }
        return true;
    }
    /*
     * Permissions required: edit (for element itself), deletechild (for parent)
     * Permission override: perm_over_deactivate
     */
    private function DeactivateElement(mod_Element $mod_element, 
        mod_Element $parent_element = null)
    {
        $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');
        try {
            if (!$this->GetPermissionOverride(self::perm_over_deactivate)) {
                if (!$mod_element->GetPermissions()->HasPermission(
                    $this->currentuser, mod_Permissions::perm_edit)) {
                    return false;
                }
                if (is_null($parent_element)) {
                    $parent_id = $this->ParentID($mod_element);
                    $oldrecursive = $this->GetRecursive();
                    $this->SetRecursive(self::recursive_no);
                    $parent_element = $this->LoadElement($parent_id);
                    $this->SetRecursive($oldrecursive);
                    if (!$this->IsValidElement($parent_element)) {
                        return false;
                    }
                }
                if (!$mod_element->GetPermissions()->HasPermission(
                    $this->currentuser, mod_Permissions::perm_deletechild)) {
                    return false;
                }
            }

            $update_sql = "UPDATE {$tbl_prefix}element "
                        . "SET active = 'd' WHERE id = :element_id AND "
                        . "active = 'y';";
            $update_statement = $this->db->prepare($update_sql);
            $update_statement->bindParam(':element_id', $mod_element->GetID());
            $update_statement->execute();
            $mod_element->SetStatus('d');
            $this->UpdateCaches($mod_element);
            if ($update_statement->rowCount() > 0) {
                if ($mod_element->GetFullyLoaded()) {
                    $children = $mod_element->GetChildren();
                } else {
                    $oldrecursive = $this->GetRecursive();
                    $this->SetRecursive(self::recursive_yes);
                    $cpy_element = $this->LoadElement($mod_element->GetID());
                    $this->SetRecursive($oldrecursive);
                    $children = $cpy_element->GetChildren();
                }
                $result = true;
                foreach($children as $child_element) {
                    if ($child_element->GetStatus() == 'y') {
                        $result = $result && $this->DeactivateElement(
                            $child_element, $mod_element);
                    }
                    if ($child_element->GetStatus() == 'p') {
                        $oldrecursive = $this->GetRecursive();
                        $this->SetRecursive(self::recursive_yes);
                        $result = $result && $this->Rollback(
                            $child_element->GetID());
                        $this->SetRecursive($oldrecursive);
                    }
                }
                return $result;
            }
        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }
    /*
     * Mark an element for being disabled. This only works for elements which
     * are enabled or pending. This function (or rather its helper function) is
     * recursive and has the effect that activated items are deactivated and
     * that pending items are deleted.
     * 
     * Permission required: depends on actual function called.
     */
    public function Deactivate(mod_Element $mod_element)
    {
        if ($mod_element->GetStatus() == 'y') {
            return $this->DeactivateElement($mod_element);
        }
        if ($mod_element->GetStatus() == 'p') {
            return $this->Rollback($mod_element);
        }
        return false;
    }
    /*
     * Find the position of an element in the tree, as recorded in the database,
     * which is recorded as an array containing the 'parent_id' and
     * 'order_index' items.
     */
    private function GetElementPosition(mod_Element $mod_element) {
        $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');
        
        $sql = "SELECT parent_id, order_index FROM {$tbl_prefix}element "
             . "WHERE id = :element_id;";
        $statement = $this->db->prepare($sql);
        $statement->bindParam(':element_id', $mod_element->GetID());
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row;
        } else {
            return false;
        }
    }
    
    /*
     * Find the appropriate order index for creating a new element directly
     * after $after_element. If $after_element is not a child of $parent_id,
     * an error is returned.
     */
    private function GetOrderIndex($parent_id = 0, $after_element = 0)
    {
        if ($after_element == 0) {
            $order_index = 0;
        } else {
            $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');
            $sql = "SELECT parent_id, order_index FROM {$tbl_prefix}element "
                 . "WHERE id = :element_id;";
            $statement = $this->db->prepare($sql);
            $statement->bindParam(':element_id', $after_element);
            $statement->execute();
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                if ($row['parent_id'] == $parent_id) {
                    $order_index = $row['order_index'] + 1;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        return $order_index;
    }
    
    /*
     * Permission required: addchild (for parent, handled in ::CreateElement)
     * Permission override: perm_over_store (handled in ::CreateElement)
     */
    private function StoreElement(mod_Element $mod_element, $parent_id = 0,
        $order_index = 0)
    {
        $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');
        $tbl_name = $mod_element->GetDbTableName();

        $oldrecursive = $this->GetRecursive();
        $this->SetRecursive(self::recursive_no);
        $parent_element = $this->LoadElement($parent_id);
        $this->SetRecursive($oldrecursive);
        if ($parent_element === false) {
            $parent_element = null;
        }
        /*
         * First, create the element itself
         */
        $ok = $this->CreateElement($mod_element, $parent_element, $order_index);
        if (!$ok) {
            return false; 
        }
        
        /*
         * Magic for creating UID's and GID's
         */
        if ($mod_element instanceof mod_User) {
            if (!$mod_element->UIDIsset()) {
                $mod_element->SetUID($mod_element->GetID());
            }
        }
        if ($mod_element instanceof mod_Usergroup) {
            if (!$mod_element->GIDIsset()) {
                $mod_element->SetGID($mod_element->GetID());
            }
        }
        
        /*
         * Create and store the permissions
         * If a certain permission override is in effect, we only store the
         * permissions but do not create them, because we assume they are
         * already set appropriately.
         */
        
        if (!$this->GetPermissionOverride(self::perm_over_store)) {
            $mod_element->SetPermissions(
                mod_PermissionsFactory::PermissionsForChild($parent_element,
                $this->currentuser));
        }
        
        $prev_overrides = $this->GetPermissionOverrides();
        $this->SetPermissionOverride(self::perm_over_changeperm);
        $this->UpdatePermissions($mod_element, $mod_element->GetPermissions());
        $this->SetPermissionOverrides($prev_overrides);
            
        /*
         * Store element-specific data.
         */
        $order_index = 0;
        foreach ($mod_element->GetForDb() as $items) {
            $set = "";
            foreach ($items as $key => $value) {
                if ($set != '') {
                    $set .= ', ';
                }
                $set .= $key . ' = :' . $key;
            }
            $sql = "INSERT INTO {$tbl_prefix}{$tbl_name} "
                 . 'SET element_id = :element_id, order_index = :order_index, '
                 . $set . ';';
            $statement = $this->db->prepare($sql);
            $statement->bindParam(':element_id', $mod_element->GetID());
            $statement->bindParam(':order_index', $order_index);
            foreach ($items as $key => $value) {
                /*
                 * bindParam's second parameter is passed as a reference, so
                 * $items[$key] must be used instead of $value.
                 */
                $statement->bindParam(':' . $key, $items[$key]);
            }
            $statement->execute();
            $order_index++;
        }
        return true;
    }
    
    /*
     * Permission required: addchild (for parent, handled in ::CreateElement
     *     through ::StoreElement)
     */
    public function AddElement(mod_Element $mod_element, $parent_id = 0,
        $after_element = 0)
    {
        try {
            // Find out the desired order index
            $order_index = $this->GetOrderIndex($parent_id, $after_element);
            if ($order_index === false) {
                return false;
            }
            // Create the new element
            $ok = $this->StoreElement($mod_element, $parent_id, $order_index);
            if (!$ok) {
                return false;
            }
        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }        
    }
    
    /*
     * Permission required: edit
     */
    private function UpdatePassword(mod_User $mod_element) {
        if (!$mod_element->GetPermissions()->HasPermission($this->currentuser,
            mod_Permissions::perm_edit)) {
            return false;
        }

        $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');
        $tbl_name = $mod_element->GetDbTableName();
        $sql = "UPDATE {$tbl_prefix}{$tbl_name} "
             . "SET password = :password WHERE element_id = :id";
        $statement = $this->db->prepare($sql);
        $statement->bindParam(':id', $mod_element->GetID());
        $statement->bindParam(':password', $mod_element->GetPasshash());
        $statement->execute();
        $this->UpdateCaches($mod_element);
        return true;
    }
    
    /*
     * Moves an element to another position in the tree.
     * 
     * Permissions required: edit (for the element itself)
     *                       deletechild (for the old parent)
     *                       addchild (for the new parent)
     */
    public function UpdatePosition(mod_Element $mod_element, $new_parent_id,
                                   $after_element = 0)
    {
        try {
            $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');
            if (!$mod_element->GetPermissions()->HasPermission($this->currentuser,
                mod_Permissions::perm_edit)) {
                return false;
            }
            $old_parent_id = $this->ParentID($mod_element);
            $oldrecursive = $this->GetRecursive();
            $this->SetRecursive(self::recursive_no);
            $old_parent = $this->LoadElement($old_parent_id);
            if ($old_parent_id == $new_parent_id) {
                $new_parent = $old_parent;
            } else {
                $new_parent = $this->LoadElement($new_parent_id);
            }
            $this->SetRecursive($oldrecursive);
            if (!$this->IsValidElement($new_parent)) {
                return false;
            }
            if (!($old_parent === false) &&
                !$old_parent->GetPermissions()->HasPermission($this->currentuser,
                mod_Permissions::perm_deletechild)) {
                return false;
            }
            if (!$new_parent->GetPermissions()->HasPermission($this->currentuser,
                mod_Permissions::perm_addchild)) {
                return false;
            }
            if (!$new_parent->CanHaveAsChild($mod_element)) {
                return false;
            }

            $new_order_index = $this->GetOrderIndex($new_parent_id, 
                $after_element);

            $reorder_sql = "UPDATE {$tbl_prefix}element "
                 . "SET order_index = order_index + 1 "
                 . "WHERE parent_id = :parent_id "
                 . "AND order_index >= :order_index";
            $reorder_statement = $this->db->prepare($reorder_sql);
            $reorder_statement->bindParam(':parent_id', $new_parent_id);
            $reorder_statement->bindParam(':order_index', $new_order_index);
            $reorder_statement->execute();

            $sql = "UPDATE {$tbl_prefix}element "
                 . "SET parent_id = :parent_id, order_index = :order_index "
                 . "WHERE id = :id";
            $statement = $this->db->prepare($sql);
            $element_id = $mod_element->GetID();
            $statement->bindParam(':id', $element_id);
            $statement->bindParam(':parent_id', $new_parent_id);
            $statement->bindParam(':order_index', $new_order_index);
            $statement->execute();
            
            if ($old_parent_id != $new_parent_id) {
                $dec_sql = "UPDATE {$tbl_prefix}element "
                         . "SET no_of_children = no_of_children - 1 "
                         . "WHERE id = :id";
                $inc_sql = "UPDATE {$tbl_prefix}element "
                         . "SET no_of_children = no_of_children + 1, "
                         . "last_modified = NOW() "
                         . "WHERE id = :id";
                $dec_statement = $this->db->prepare($dec_sql);
                $inc_statement = $this->db->prepare($inc_sql);
                $dec_statement->bindParam(':id', $old_parent_id);
                $inc_statement->bindParam(':id', $new_parent_id);
                $dec_statement->execute();
                $inc_statement->execute();
            }
            return true;
        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }        
    }

    /*
     * Does an in-place update of the element permissions.
     * 
     * Permission required: changeperm
     */
    public function UpdatePermissions(mod_Element $mod_element,
        mod_Permissions $newpermissions)
    {
        if (!$mod_element->GetPermissions()->HasPermission($this->currentuser,
            mod_Permissions::perm_changeperm) && 
            !$this->GetPermissionOverride(self::perm_over_changeperm)) {
            return false;
        }

        try {
            $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');
            $delete_sql = "DELETE FROM {$tbl_prefix}acl "
                        . "WHERE element_id = :id;";
            $insert_sql = "INSERT INTO {$tbl_prefix}acl "
                        . "SET element_id = :id, permissiontype = :type, "
                        . "subject = :subj_id, permission = :permission;";
            $delete_statement = $this->db->prepare($delete_sql);
            $delete_statement->bindParam(':id', $mod_element->GetID());
            $delete_statement->execute();
            $insert_statement = $this->db->prepare($insert_sql);
            $insert_statement->bindParam(':id', $mod_element->GetID());
            foreach($newpermissions->GetRulesArray() as $rule) {
                $what = $newpermissions->RuleWhat($rule);
                $subj_id = $newpermissions->RuleWho($rule);
                $permission = $newpermissions->RulePermission($rule);
                if ($what == mod_Permissions::perm_user) {
                    $permtype = 'u';
                } elseif ($what == mod_Permissions::perm_group) {
                    $permtype = 'g';
                } else {
                    $permtype = '';
                }
                if ($permtype != '') {
                    $insert_statement->bindParam(':type', $permtype);
                    $insert_statement->bindParam(':subj_id', $subj_id);
                    $insert_statement->bindParam(':permission', $permission);
                    $insert_statement->execute();
                }
            }
            $mod_element->SetPermissions($newpermissions);
            $this->UpdateCaches($mod_element);
            return true;
        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }
    
    private function UpdateCaches(mod_Element $mod_element) {
        if ($mod_element instanceof mod_Usergroup) {
            $this->group_cache[$mod_element->GetGID()] = $mod_element;
        }
        if ($mod_element instanceof mod_User) {
            $this->user_cache[$mod_element->GetUID()] = $mod_element;
        }
    }
    private function RemoveFromCaches(mod_Element $mod_element) {
        if ($mod_element instanceof mod_Usergroup) {
            unset($this->group_cache[$mod_element->GetGID()]);
        }
        if ($mod_element instanceof mod_User) {
            unset($this->user_cache[$mod_element->GetUID()]);
        }
    }

    /*
     * Permission required: edit
     */
    public function UpdateElement(mod_Element $mod_element) {
        if (!$mod_element->GetPermissions()->HasPermission($this->currentuser, 
            mod_Permissions::perm_edit)) {
            return false;
        }        
        $old_permission_overrides = $this->GetPermissionOverrides();
        
        try {
            if ($mod_element instanceof mod_User) {
                $this->user_cache[$mod_element->GetUID()] = $mod_element;
                /*
                 * Update password in place
                 */
                if ($mod_element->GetModified() & 
                    mod_User::user_modified_password) {
                    if (!$this->UpdatePassword($mod_element)) {
                        return false;
                    }
                }
                /*
                 * Allow setting the administrator bit only if this is done by
                 * another administrator
                 */
                if (($mod_element->GetModified() &
                     mod_User::user_modified_admin) && $mod_element->GetAdmin()
                     && !$this->currentuser->GetAdmin()) {
                    return false;
                }
                /*
                 * If we changed nothing or only the password, we are finished
                 * now.
                 */
                if (!(($mod_element->GetModified() 
                     & ~mod_User::user_modified_none)
                    & ~mod_User::user_modified_password)) {
                    return true;
                }
            }
            // Find out the position of this element
            $position = $this->GetElementPosition($mod_element);
            if ($position === false) {
                return false;
            }
            
            $this->SetPermissionOverride(self::perm_over_deactivate +
                self::perm_over_store + self::perm_over_rollback);
            
            // Deactivate the old element
            if (!$this->Deactivate($mod_element)) {
                return false;
            }
            // Create a new one at the old position with the updated values.
            $ok = $this->StoreElement($mod_element, $position['parent_id'], 
                $position['order_index']);
            if (!$ok) {
                $this->RollbackElement($mod_element->GetID());
                return false;
            }
            $this->SetPermissionOverrides($old_permission_overrides);
        } catch (PDOException $e) {
                $this->SetPermissionOverrides($old_permission_overrides);
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function RegisterEvent($action, $success, $user, $object, $info) {
    try {
            $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');
            if ($success) {
                $success_value = 'y';
            } else {
                $success_value = 'n';
            }
            
            $sql = "INSERT INTO {$tbl_prefix}eventlog SET ";
            if (!is_null($user)) {
                $sql .= "uid = :uid, ";
            }
            $sql .= "time = NOW(), "
                  . "action = :action, ";
            if (!is_null($object)) {
                $sql .= "element_id = :id, ";
            }
            $sql .= "success = :success, "
                  . "info = :info";
            $statement = $this->db->prepare($sql);
            if (!is_null($user)) {
                $statement->bindParam(':uid', $user->GetUID());
            }
            $statement->bindParam(':action', $action);
            if (!is_null($object)) {
                $statement->bindParam(':id', $object->GetID());
            }
            $statement->bindParam(':success', $success_value);
            $statement->bindParam(':info', $info);
            $statement->execute();
            return true;
        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }
            
    /*
     * (Re)create the required database table(s) for this application. Any
     * existing data will be deleted.
     */
    public function CreateTableForClass($classname)
    {
        $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');

        $callable_name = array($classname, 'GetDbTableName');
        $callable_definition = array($classname, 'GetDbTableDefinition');
        if (is_callable($callable_name)) {
            $tbl_name = call_user_func($callable_name);
            if ($tbl_name === false) {
                return false;
            }
        } else {
            return false;
        }
        if (is_callable($callable_definition)) {
            $tbl_definition = call_user_func($callable_definition);
            if ($tbl_definition === false) {
                return false;
            }
        } else {
            return false;
        }
        if ($tbl_definition != "") {
            $tbl_definition .= ', ';
        }
        
        $this->db->query("DROP TABLE IF EXISTS {$tbl_prefix}{$tbl_name};");

        $this->db->query("CREATE TABLE {$tbl_prefix}{$tbl_name} ("
                       . "element_id INT NOT NULL, "
                       . "order_index SMALLINT NOT NULL, "
                       . $tbl_definition
                       . "KEY (element_id, order_index)) CHARACTER SET utf8;");
    }
    public function CreateTables()
    {
        $tbl_prefix = mod_Options::instance()->GetOption('tbl_prefix');
        try {
            $this->db->query("DROP TABLE IF EXISTS {$tbl_prefix}element, "
                           . "{$tbl_prefix}groupmembership, "
                           . "{$tbl_prefix}acl, "
                           . "{$tbl_prefix}eventlog;");

            $this->db->query("CREATE TABLE {$tbl_prefix}element ("
                              . "id INT NOT NULL AUTO_INCREMENT, "
                           . "parent_id INT NOT NULL, "
                           . "order_index SMALLINT NOT NULL, "
                           . "no_of_children SMALLINT NOT NULL DEFAULT 0, "
                           . "creation_time DATETIME NOT NULL, "
                           . "last_modified DATETIME NOT NULL, "
                           . "active ENUM ('y','d','n','p') NOT NULL DEFAULT 'y', "
                           . "type VARCHAR(16) NOT NULL, "
                           . "PRIMARY KEY (id), "
                       . "KEY (parent_id, order_index), "
                           . "KEY (parent_id, active, order_index)) "
                           . "CHARACTER SET utf8;");
            
            $this->db->query("CREATE TABLE {$tbl_prefix}acl ("
                           . "element_id INT NOT NULL, "
                           . "permissiontype ENUM('u', 'g'), "
                           . "subject INT NOT NULL, "
                           . "permission SMALLINT NOT NULL, "
                       . "KEY (element_id)) CHARACTER SET utf8;");
            
            $this->db->query("CREATE TABLE {$tbl_prefix}groupmembership ("
                           . "uid INT NOT NULL, gid INT NOT NULL, "
                           . "PRIMARY KEY (uid), KEY (gid)) "
                           . "CHARACTER SET utf8;");

            $this->db->query("CREATE TABLE {$tbl_prefix}eventlog ("
                           . "uid INT, "
                           . "time DATETIME NOT NULL, "
                           . "action VARCHAR("
                           . self::tbl_eventlog_actionlength . ") NOT NULL, "
                           . "element_id INT, "
                           . "success ENUM('y', 'n') NOT NULL, "
                           . "info VARCHAR(" . self::tbl_eventlog_infolength 
                           . ") NOT NULL, "
                           . "KEY(uid), key(time)) CHARACTER SET utf8;");
            
            foreach (mod_Options::instance()->GetOption('classlist') as $classbase) {
                $classname = 'mod_' . $classbase;
                $this->CreateTableForClass($classname);
            }
        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
        return true;
    }
}

?>
