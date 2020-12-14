<?php
namespace ADM\WPPlugin;

if (file_exists('../../../../wp-load.php')) {
    require_once('../../../../wp-load.php');
}

$args = array(
    'method'  => $_SERVER['REQUEST_METHOD'],
    'uri'     => @$_REQUEST['_uri'],
);

/**
 *
 *  Define your routes here:
 *
 *  Fore a RESTful controller you can use the resource method:
 *
 *  Router::resource(resource_name);
 *
 *  Which will automatically add the following routes for you:
 *
 *  Router::addRoute('GET',    'selections',                'selections#index');
 *  Router::addRoute('GET',    'selections/new',            'selections#new');
 *  Router::addRoute('POST',   'selections',                'selections#create');
 *  Router::addRoute('GET',    'selections/:id',            'selections#show');
 *  Router::addRoute('GET',    'selections/:id/edit',       'selections#edit');
 *  Router::addRoute('POST',   'selections/:id',            'selections#update');
 *  Router::addRoute('PUT',    'selections/:id',            'selections#update');
 *  Router::addRoute('PATCH',  'selections/:id',            'selections#update');
 *  Router::addRoute('PUT',    'selections/:id/status',     'selections#status');
 *  Router::addRoute('DELETE', 'selections/:id',            'selections#destroy');
 *
 *  To add a custom route, use the add_route function:
 *
 *  Router::add_route(METHOD, uri_pattern, callback);
 *
 *      METHOD:         HTTP verb: GET, PUT, POST, PATCH, DELETE
 *      uri_pattern:    the uri that will be passed, eg: 'carts/:id'
 *      callback:       the controller/action combo to call. eg: 'carts#show'
 **/

// Activation
Router::addRoute('POST', 'oauth/authorize', 'activation#authorize');
//Router::addRoute('GET', 'oauth/callback', 'activation#callback');

// Settings
Router::addRoute('PUT', 'settings/:id/reset', 'settings#reset');
Router::addRoute('GET', 'settings/importLearningCategories', 'settings#importLearningCategories');
Router::addRoute('GET', 'settings/importCourses', 'settings#importCourses');
Router::addRoute('GET', 'settings/importLearningPathes', 'settings#importLearningPathes');

Router::run($args);
