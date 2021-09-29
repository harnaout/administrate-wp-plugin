<?php
namespace ADM\WPPlugin\Base;

if (file_exists(ABSPATH . 'wp-load.php')) {
    require_once(ABSPATH . 'wp-load.php');
}

/**
* Setup the base class for all models
*
* @package admwpp
*
*/
abstract class ActiveModel
{

    protected $id;
    protected $created_at;
    protected $updated_at;
    protected $status;

    static $table_name;
    static $_meta;
    static $create_table_query;

    protected $statuses    = array(
        'published' => 'Published',
        'trashed'   => 'Trashed'
    );

    protected $default_status   = 'published';
    protected $published_status = 'published';
    protected $trash_status     = 'trashed';

    const CHARSET = 'utf8mb4';
    const COLLATE = 'utf8mb4_unicode_ci';

    public $errors = array();

    /**
     * Setup WordPress hooks/actions
     *
     * @return void
     *
     */
    public function __construct($args = array())
    {
        $this->set_default_params($args);
    }

    /*---------------------------------------------*/
    /*--------- Active Relations START ------------*/
    /*---------------------------------------------*/
   /**
     * Get object helper method.
     * Gets object from database by id.
     *
     * @param   $id,    int
     * @return  object, result from database query.
     **/
    public static function find($id)
    {
        global $wpdb;
        $class        = get_called_class();
        $table_name   = $class::tabel_name_with_prefix($class::$table_name);

        return new $class(
            $wpdb->get_row(
                "SELECT * FROM {$table_name} WHERE id='$id'",
                ARRAY_A
            )
        );
    }

    /**
     * Build the WHERE clause of the SQL query.
     *
     * @param   $clause,    Array,  the array of attributes & their queries.
     * @param   $operator,  String, AND/OR.
     * @return  String,     the WHERE clause.
     **/
    public static function build_clause($clause = array(), $operator = 'AND')
    {
        $where_clause = '';
        if (!empty($clause)) {
            $where_clause .= 'WHERE';

            foreach ($clause as $attribute => $query) {
                $where_clause .= " $operator $attribute $query";
            }

            $where_clause = preg_replace(
                "/$operator\ /",
                '',
                $where_clause,
                1
            );
        }

        return $where_clause;
    }

    /**
     * Build the ORDER BY clause of the SQL query.
     *
     * @param   $clause,    Array,  the array of attributes & their queries.
     * @param   $operator,  String, AND/OR.
     * @return  String,     the ORDER clause.
     **/
    public static function build_order($order = array())
    {
        $order_clause = '';
        if (!empty($order)) {
            $order_clause .= 'ORDER BY';
            foreach ($order as $sort => $direction) {
                $direction = " ".$direction;
                $order_clause .= " $sort $direction,";
            }
        }
        return rtrim($order_clause, ",");
    }

    /**
     * Build Pager for the SQL query.
     *
     * @param   $clause,    Array,  the array of attributes & their queries.
     * @return  String,     the LIMIT clause.
     **/
    public static function build_pager($page, $per_page)
    {
        $pager = '';
        if ($page == 0 && $per_page) {
            $pager = 'LIMIT ' . $per_page;
        }
        if ($page >= 1 && $per_page) {
            $skip   = ($page - 1) * $per_page;
            $pager  = 'LIMIT ' . $skip . ', ' . $per_page;
        }
        return $pager;
    }

    /**
     * Search for objects and return an Array of objects that match
     * the search query.
     *
     * @param   $clause,    Array,  the array of attributes & their queries.
     * @param   $operator,  String, AND/OR.
     * @return  Array,      the matching search results.
     **/
    public static function where($clause = array(), $order = array(), $page = 0, $per_page = 0, $operator = 'AND')
    {
        global $wpdb;
        $class        = get_called_class();
        $table_name   = $class::tabel_name_with_prefix($class::$table_name);
        $where        = $class::build_clause($clause, $operator);
        $order        = $class::build_order($order);
        $pager        = $class::build_pager($page, $per_page);

        $query = "SELECT * FROM {$table_name} $where $order $pager";
        return $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Get the count of objects that match the search query.
     *
     * @param   $clause,    Array,  the array of attributes & their queries.
     * @param   $operator,  String, AND/OR.
     * @return  Integer,    the count matching the search results.
     **/
    public static function count($clause = array(), $operator = 'AND')
    {
        global $wpdb;
        $class        = get_called_class();
        $table_name   = $class::tabel_name_with_prefix($class::$table_name);
        $where_clause = $class::build_clause($clause, $operator);

        $query = "SELECT COUNT(*) FROM {$table_name} $where_clause";
        return $wpdb->get_var($query);
    }

    /**
     * Search for objects by status and returns an Array of objects that match
     * the search query.
     * If the status passed is 'all', 'All', or empty it returns all of them.
     *
     * @param   $status,    String, the status string.
     * @return  Array,      the matching search results.
     **/
    public static function find_by_status($status = 'all')
    {
        $class  = get_called_class();
        $clause = array();

        if (!(empty($status) || 'all' == $status || 'All' == $status)) {
            $clause = array('status' => " = '$status'");
        }

        return $class::where($clause);
    }

    /**
     * Search for objects by status and returns the Count of objects that match
     * the search query.
     * If the status passed is 'all', 'All', or empty it returns all of them.
     *
     * @param   $status,    String, the status string.
     * @return  Integer,    the count of objects that match the search.
     **/
    public static function count_by_status($status = 'all')
    {
        $class  = get_called_class();
        $clause = array();

        if (!(empty($status) || 'all' == $status || 'All' == $status)) {
            $clause = array('status' => " = '$status'");
        }

        return $class::count($clause);
    }
    /*---------------------------------------------*/
    /*--------- Active Relations END --------------*/
    /*---------------------------------------------*/

    /*---------------------------------------------*/
    /*--------- Active Records START --------------*/
    /*---------------------------------------------*/

    /**
     * Get table name with the WP prefix set by the site.
     *
     * @return String, $table_name
     *
     */
    public static function tabel_name_with_prefix($table_name)
    {
        require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
        global $wpdb;

        $table_name = $wpdb->prefix . $table_name;
        return $table_name;
    }

     /**
     * Create the table associated with the model.
     *
     * @return void
     *
     */
    public static function create_table()
    {
        require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
        global $wpdb;

        $class      = get_called_class();
        $table_name = $class::tabel_name_with_prefix($class::$table_name);
        $query      = $class::get_create_table_query();
        $sql        = "CREATE TABLE IF NOT EXISTS $table_name ( $query )";
        $sql        .= " DEFAULT CHARSET=" . self::CHARSET;
        $sql        .= " COLLATE=" . self::COLLATE;

        $wpdb->query($sql);
    }

    /**
     * Construct the SQL query for creating the table for the model.
     *
     * @return String
     *
     */
    public static function get_create_table_query()
    {
        $class = get_called_class();
        $query = $class::$create_table_query;

        $basic_query = "
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            status varchar(255) NULL,
            created_at datetime NULL,
            updated_at datetime NULL,
        ";

        $unique_key = "UNIQUE KEY id (id)";

        return "$basic_query $query $unique_key";
    }

    /**
     * Delete the table associated with the model.
     *
     * @return void
     *
     */
    public static function drop_table()
    {
        global $wpdb;
        $class      = get_called_class();
        $table_name = $class::tabel_name_with_prefix($class::$table_name);
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

    /**
     * Set default array.
     * This is used when saving or updating.
     *
     * @return Array, array of attributes that have a value.
     *
     */
    public function set_default_save_array()
    {
        $array = array();

        if ($this->is_new()) {
            $array['created_at']    = date(ADMWPP_DATE_FORMAT);
            $array['updated_at']    = date(ADMWPP_DATE_FORMAT);
            $array['status']        = $this->get_status();
        } else {
            $array['updated_at']    = date(ADMWPP_DATE_FORMAT);
            $array['id']            = $this->get_id();
            $array['status']        = $this->get_status();
        }

        return $array;
    }

    /**
     * Set save array.
     * This is used when saving or updating.
     *
     * @return Array, array of attributes that have a value.
     *
     */
    public function set_save_array()
    {
        $array = array();
        $default_array = array();

        $class = get_class($this);
        foreach ($class::$_meta as $key) {
            $value = $this->get_meta($key);
            if (isset($value)) {
                $array[$key] = $value;
            }
        }

        $default_array = $this->set_default_save_array();

        $array = array_merge($default_array, $array);

        return $array;
    }

     /**
     * POST Save callbacks
     *
     * @return void
     **/
    public function post_save()
    {
    }

     /**
     * Save current object to the database.
     *
     * @return false, if failed, true otherwise.
     **/
    public function save()
    {
        global $wpdb;
        $class = get_class($this);

        if ($this->has_errors()) {
            return false;
        }

        $attributes = $this->set_save_array();

        if (empty($attributes)) {
            return false;
        }

        $saved = false;

        if ($this->is_new()) {
            // Try to insert the new object to the database
            // Return false if exception raised, or it fails.
            // Return true if succeeds.
            $saved = $this->add($attributes);
        } else {
            // Try to update the existing object in the database
            // Return false if exception raised, or it fails.
            // Return true if succeeds.
            $saved = $this->update($attributes);
        }

        // Execute POST Save callbacks
        $this->post_save();

        return $saved;
    }

     /**
     * Add object to the database.
     *
     * @return false, if failed, true otherwise.
     **/
    public function add($attributes = array())
    {
        global $wpdb;
        $class      = get_called_class();
        $saved      = false;
        $table_name = $class::tabel_name_with_prefix($class::$table_name);

        try {
            $result = $wpdb->insert($table_name, $attributes);
            if ($result) {
                $class::set_id($wpdb->insert_id);
                $class::set_created_at($attributes['created_at']);
                $saved = true;
            }
        } catch (Exception $exception) {
            var_dump($exception);
        }
        return $saved;
    }

    /**
     * Update object to the database.
     *
     * @return false, if failed, true otherwise.
     **/
    public function update($attributes = array())
    {
        global $wpdb;
        $class      = get_called_class();
        $saved      = false;
        $table_name = $class::tabel_name_with_prefix($class::$table_name);

        try {
            $result = $wpdb->update($table_name, $attributes, array('id' => $class::get_id()));
            $saved = true;
        } catch (Exception $exception) {
                var_dump($exception);
        }
        return $saved;
    }

    public static function create($args = array())
    {
        $class = get_called_class();
        $object = new $class($args);

        if ($object->save()) {
            return $object;
        } else {
            return false;
        }
    }

    /**
     * Update the attributes of the current object.
     *
     * @return false, if failed, true otherwise.
     **/
    public function update_attributes($attributes = array(), $save = true)
    {
        foreach ($attributes as $key => $value) {
            $this->set_meta($key, $value);
        }

        if ($save) {
            return $this->save();
        }

        return true;
    }

    /**
     * Delete current object from the database.
     *
     * @return false, if failed, true otherwise.
     **/
    public function delete()
    {
        global $wpdb;
        $class      = get_class($this);
        $table_name = $class::tabel_name_with_prefix($class::$table_name);

        $deleted = true;

        if (!$this->is_new()) {
            // Execute PRE Delete callbacks
            $this->pre_delete();

            // Delete from database
            $id = $this->get_id();
            $deleted = $wpdb->query("DELETE FROM {$table_name} WHERE id=$id");

            // Execute POST Delete callbacks
            $this->post_delete();
        }

        return $deleted;
    }

    /**
     * PRE Delete callbacks.
     *
     * @return void
     **/
    public function pre_delete()
    {
    }

    /**
     * POST Delete callbacks
     *
     * @return void
     **/
    public function post_delete()
    {
    }

    /**
     * Delete the object from the database.
     *
     * @return false, if failed, true otherwise.
     **/
    public static function delete_object($id)
    {
        $class = get_called_class();
        $object = $class::find($id);

        return $object->delete();
    }

    /**
     * Delete objects from the database.
     *
     * @return void
     **/
    public static function delete_objects($ids)
    {
        $class = get_called_class();

        foreach ($ids as $id) {
            $class::delete_object($id);
        }
    }

    /**
     * Delete all objects from the database.
     *
     * @return void
     **/
    public static function delete_all()
    {
        $class   = get_called_class();
        $objects = $class::find_by_status();
        foreach ($objects as $object) {
            $class::delete_object($object['id']);
        }
    }

    /*---------------------------------------------*/
    /*--------- Active Records END ----------------*/
    /*---------------------------------------------*/

    /*---------------------------------------------*/
    /*--------- Active Statusable START -----------*/
    /*---------------------------------------------*/

    /**
     * PRE Move to Trash callbacks.
     *
     **/
    public function pre_move_to_trash()
    {
    }

    /**
     * Move current object to trash.
     *
     * @return false, if failed, true otherwise.
     **/
    public function move_to_trash()
    {
        $class = get_class($this);

        // Execute PRE Move to Trash callbacks
        $this->pre_move_to_trash();

        $this->set_status($this->trash_status);
        return $this->save();
    }

    /**
     * Move object with $id to trash.
     *
     * @return false, if failed, true otherwise.
     **/
    public static function move_object_to_trash($id)
    {
        $class = get_called_class();
        $object = $class::find($id);

        return $object->move_to_trash();
    }

    /**
     * Move objects with id in $ids to trash.
     *
     * @return void
     **/
    public static function move_objects_to_trash($ids)
    {
        $class = get_called_class();

        foreach ($ids as $id) {
            $class::move_object_to_trash($id);
        }
    }

    /**
     * PRE Restore from Trash callbacks.
     *
     **/
    public function pre_restore_from_trash()
    {
    }

    /**
     * Move current object from trash to published.
     *
     * @return false, if failed, true otherwise
     **/
    public function restore_from_trash()
    {
        $class = get_class($this);

        // Execute PRE Restore from Trash callbacks
        $this->pre_restore_from_trash();

        $this->set_status($this->published_status);
        return $this->save();
    }

    /**
     * Move object with $id from trash back to published.
     *
     * @return false, if failed, true otherwise
     **/
    public static function restore_object_from_trash($id)
    {
        $class = get_called_class();
        $object = $class::find($id);

        return $object->restore_from_trash();
    }

    /**
     * Move objects with id in $ids from trash back to published.
     *
     * @return void
     **/
    public static function restore_objects_from_trash($ids)
    {
        $class = get_called_class();

        foreach ($ids as $id) {
            $class::restore_object_from_trash($id);
        }
    }
    /*---------------------------------------------*/
    /*--------- Active Statusable END -------------*/
    /*---------------------------------------------*/

    /**
    * Set Default Parameters
    *
    * @return void
    *
    */
    public function set_default_params($args)
    {
        $this->set_id(self::array_get($args, 'id'));
        $this->set_status(self::array_get($args, 'status'));
        $this->set_created_at(self::array_get($args, 'created_at'));
        $this->set_updated_at(self::array_get($args, 'updated_at'));

        $class = get_class($this);
        foreach ($class::$_meta as $key) {
            $this->set_meta($key, self::array_get($args, $key));
        }
    }

    /**
    * Run necessary logic on install.
    *
    * @return void
    *
    */
    public static function on_install()
    {
        $class = get_called_class();
        $class::create_table();
    }

    /**
    * Run necessary logic on uninstall.
    *
    * @return void
    *
    */
    public static function on_uninstall()
    {
        $class = get_called_class();
        $class::drop_table();
    }

    /**
     * Setter methods.
     *
     * @return void
     *
     */
    public function set_id($id)
    {
        $this->set_meta('id', (int)$id);
    }

    public function set_status($status)
    {
        if (empty($status)) {
            $status = $this->default_status;
        }

        $this->set_meta('status', $status);
    }

    public function set_created_at($created_at)
    {
        $this->set_meta('created_at', $created_at);
    }

    public function set_updated_at($updated_at)
    {
        $this->set_meta('updated_at', $updated_at);
    }

    public function set_meta($key, $value)
    {
        $this->$key = $value;
    }

    /**
     * Getter methods.
     *
     * @param  String, key,     the name of the attribute.
     * @return String, value,   the value of the attribute.
     *
     */
    public function get_meta($key)
    {
        return $this->$key;
    }

    public function get_id()
    {
        return $this->get_meta('id');
    }

    public function get_status()
    {
        return $this->get_meta('status');
    }

    public function get_statuses()
    {
        return $this->statuses;
    }

    public function get_created_at()
    {
        return $this->get_meta('created_at');
    }

    public function get_updated_at()
    {
        return $this->get_meta('updated_at');
    }

    /**
     * Check if the object is new or has been previously persisted
     * to the database.
     *
     * @return Boolean, false if an id is present, true otherwise.
     *
     */
    public function is_new()
    {
        return empty($this->id);
    }

    /**
     * Check if the object has any errors.
     *
     * @return Boolean, true if it has any errors, false otherwise.
     *
     */
    public function has_errors()
    {
        return !empty($this->errors);
    }

    /**
    * Helper method to default array index to null if not set.
    *
    * @return void
    * @author
    * */
    public static function array_get($array, $key, $default = null)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }

    /**
     * A helper method to access static attributes for classes
     */
    public static function get_static_value($attribute)
    {
        $class = get_called_class();
        return $class::$$attribute;
    }

    /**
     * Transform an array of attributes into an array of objects from the
     * same class as the callee class.
     *
     * @param   $array,     Array, original array from query result.
     * @return  $objects,   Array of Objects, the resulting array.
     */
    public static function array_to_objects($array)
    {
        $class      = get_called_class();
        $objects    = array();
        foreach ($array as $item) {
            array_push($objects, new $class($item));
        }

        return $objects;
    }

    public function to_array()
    {
        return get_object_vars($this);
    }

    public function serialize()
    {
        $clone = clone $this;
        return $clone->to_array();
    }
}
