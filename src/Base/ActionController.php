<?php
namespace ADM\WPPlugin\Base;

use ADM\WPPlugin\Main as Main;
use ADM\WPPlugin\Models as Models;

abstract class ActionController
{
    static $model_class_name;
    static $models_namespace = "ADM\WPPlugin\Models\\";
    static $params;
    static $method;
    static $format;
    static $action;
    static $object;

    /**
     * Render the index page for the object.
     *
     * @return void
     */
    public static function index()
    {
    }

    /**
     * Render the new page for the object.
     *
     * @return void
     */
    public static function _new()
    {
    }

    /**
     * Render the edit page for the object.
     *
     * @return void
     */
    public static function create()
    {
        $class          = get_called_class();
        $class_name     = $class::$model_class_name;
        $object_class   = $class::$models_namespace . $class_name;
        $params         = $class::$params;
        $object         = new $object_class($class::get_object_params());

        $saved =  $object->save();

        if ($saved) {
            if ($class::formatIsJson()) {
                $object = $object->to_array();
                $response = array(
                    'status'    => 'success',
                    'message'   => '',
                    'object'    => $object
                );
                echo json_encode($response);
                return;
            } else {
                self::setFlash("$class_name created successfully.");

                $class::$params['id'] = $object->get_id();
                $class::show();
                return;
            }
        } else {
            if ($class::formatIsJson()) {
                $object = $object->to_array();
                $response = array(
                    'status'    => 'error',
                    'message'   => 'Oooops! Could not create object.',
                    'object'    => $object
                );
                echo json_encode($response);
                return;
            } else {
                self::setFlash("Unable to create $class_name. Please check your values and try again.", false);

                $class::_new();
                return;
            }
        }
    }

    /**
     * Render the edit page for the object.
     *
     * @return void
     */
    public static function edit()
    {
        $class          = get_called_class();
        $class_name     = $class::$model_class_name;
        $object_class   = $class::$models_namespace . $class_name;
        $params         = $class::$params;
        $class::$object = $object_class::find($params['id']);

        $class::handle_object_not_found();
    }

    /**
     * Update object and redirect to the edit page.
     *
     * @return void
     */
    public static function update()
    {
        $class          = get_called_class();
        $class_name     = $class::$model_class_name;
        $object_class   = $class::$models_namespace . $class_name;
        $params         = $class::$params;
        $class::$object = $object_class::find($params['id']);

        $class::handle_object_not_found();

        $saved = $class::$object->update_attributes($class::get_object_params());

        if ($saved) {
            if ($class::formatIsJson()) {
                $object = $class::$object->to_array();
                $response = array(
                    'status'    => 'success',
                    'message'   => 'Object updated successfully',
                    'object'    => $object
                );
                echo json_encode($response);
                return;
            } else {
                self::setFlash("$class_name updated successfully.");

                $class::edit();
                return;
            }
        } else {
            if ($class::formatIsJson()) {
                $object = $object->to_array();
                $response = array(
                    'status'    => 'error',
                    'message'   => 'Oooops! Could not update object.',
                    'object'    => $object
                );
                echo json_encode($response);
                return;
            } else {
                self::setFlash("Unable to update $class_name. Please check your values and try again.", false);

                $class::edit();
                return;
            }
        }
    }

    /**
     * Update object status and redirect to the edit page.
     *
     * @return void
     */
    public static function status()
    {
        $class          = get_called_class();
        $class_name     = $class::$model_class_name;
        $object_class   = $class::$models_namespace . $class_name;
        $params         = $class::$params;
        $class::$object = $object_class::find($params['id']);

        $class::handle_object_not_found();

        $saved = 0;

        $object_params = $class::get_object_params();

        $status = $object_params['status'];

        if (in_array($status, array_keys($class::$object->get_statuses()))) {
            switch ($status) {
                case 'trashed':
                    $saved = $class::$object->move_to_trash();
                    break;
                case 'published':
                    $saved = $class::$object->restore_from_trash();
                    break;
                default:
                    $class::$object->set_status($status);
                    $saved = $class::$object->save();
                    break;
            }
        }

        if ($saved) {
            if ($class::formatIsJson()) {
                $object = $class::$object->to_array();
                $response = array(
                    'status'    => 'success',
                    'message'   => 'Object status updated successfully',
                    'object'    => $object
                );
                echo json_encode($response);
                return;
            } else {
                self::setFlash("$class_name status updated successfully.");

                $class::edit();
                return;
            }
        } else {
            if ($class::formatIsJson()) {
                $object = $object->to_array();
                $response = array(
                    'status'    => 'error',
                    'message'   => 'Oooops! Could not update object status.',
                    'object'    => $object
                );
                echo json_encode($response);
                return;
            } else {
                self::setFlash("Unable to update $class_name status. Please check your values and try again.", false);

                $class::edit();
                return;
            }
        }
    }

    /**
     * Render the view page for the object.
     *
     * @return void
     */
    public static function show()
    {
        $class          = get_called_class();
        $class_name     = $class::$model_class_name;
        $object_class   = $class::$models_namespace . $class_name;
        $params         = $class::$params;
        $class::$object = $object_class::find($params['id']);

        $class::handle_object_not_found();

        if ($class::formatIsJson()) {
            $object = $class::$object->to_array();
            $response = array(
                'status'    => 'success',
                'message'   => '',
                'object'    => $object
            );
            echo json_encode($response);
            return;
        } else {
            // render show template
            return;
        }
    }

    /**
     * Delete object and redirect to index page.
     *
     * @return void
     */
    public static function destroy()
    {
        $class          = get_called_class();
        $class_name     = $class::$model_class_name;
        $object_class   = $class::$models_namespace . $class_name;
        $params         = $class::$params;
        $class::$object = $object_class::find($params['id']);

        $class::handle_object_not_found();

        $object = $class::$object->to_array();
        $class::$object->delete();

        if ($class::formatIsJson()) {
            $response = array(
                'status'    => 'success',
                'message'   => 'Object deleted successfully',
                'object'    => $object
            );
            echo json_encode($response);
            return;
        } else {
            self::setFlash("$class_name deleted successfully.");

            $class::index();
            return;
        }
    }

    /**
     * Returns object parameters.
     *
     * @return $params, array, the parameters array.
     */
    protected static function get_object_params()
    {
        $class          = get_called_class();
        $class_name     = $class::$model_class_name;
        $params         = $class::$params;

        return $params[strtolower($class_name)];
    }

    /**
     * Handles object if not found.
     *
     * If no object found and "_format" is set to "json":
     *
     * echo $response, json string, with error message.
     *
     * Else If no object found redirect to index page.
     *
     * @return void.
     */
    protected static function handle_object_not_found()
    {
        $class  = get_called_class();
        $id     = $class::$object->get_id();

        if (empty($id)) {
            if ($class::formatIsJson()) {
                $response = array(
                    'status' => 'error',
                    'message' => 'Object not found!'
                );
                echo json_encode($response);
                exit;
            } else {
                $class::index();
                exit;
            }
        }
    }

    protected static function setFlash($message, $success = true)
    {
        $flash = Main::instance()->getFlash();
        $flash->setMessage(array(
          'message' => $message,
          'success' => $success ? 'success' : 'error'
        ));
    }

    protected static function formatIsJson()
    {
        $class = get_called_class();
        return (!empty($class::$format) && 'json' == $class::$format);
    }

    protected static function formatIsHtml()
    {
        $class = get_called_class();
        return (empty($class::$format) || 'html' == $class::$format);
    }
}
