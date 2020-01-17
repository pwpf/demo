<?php
/** @var Router $router */


use Plugin_Name\PWPF\Routing\Router;
use Plugin_Name\PWPF\Routing\RouteType;

/*
|---------------------------------------------------------------------------------------------------------
| Available Route Types:-
|
| NOTE - Except Late Frontend Routes, all other routes are triggerred on 'init' hook.
|
+----------------------------------------------+---------------------------------------------------------+
| ROUTE TYPE                                   | ROUTE DESCRIPTION                                       |
+----------------------------------------------+---------------------------------------------------------+
| RouteType::ANY                              | To be used if model/controller is                       |
|                                              | required on all pages admin as well as frontend         |
+----------------------------------------------+---------------------------------------------------------+
| RouteType::ADMIN                            | To be used if model/controller needs to be loaded on    |
|                                              | on admin pages only                                     |
+----------------------------------------------+---------------------------------------------------------+
| RouteType::ADMIN_WITH_POSSIBLE_AJAX         | To be used if model/controller contains Ajax & needs    |
|                                              | to be loaded on admin pages only                        |
+----------------------------------------------+---------------------------------------------------------+
| RouteType::AJAX                             | To be used if model/controller contains Ajax            |
+----------------------------------------------+---------------------------------------------------------+
| RouteType::CRON                             | To be used if model/controller contains Cron            |
|                                              | functionality                                           |
+----------------------------------------------+---------------------------------------------------------+
| RouteType::FRONTEND                         | To be used if model/controller needs to be loaded on    |
|                                              | frontend pages only                                     |
+----------------------------------------------+---------------------------------------------------------+
| RouteType::FRONTEND_WITH_POSSIBLE_AJAX      | To be used if model/controller contains Ajax & needs    |
|                                              | to be loaded on frontend pages only                     |
+----------------------------------------------+---------------------------------------------------------+
| RouteType::LATE_FRONTEND                    | To be used if model/controller needs to be loaded when  |
|                                              | specific conditions are matched                         |
+----------------------------------------------+---------------------------------------------------------+
| RouteType::LATE_FRONTEND_WITH_POSSIBLE_AJAX | To be used if model/controller contains Ajax & needs    |
|                                              | to be loaded when specific conditions are matched       |
+----------------------------------------------+---------------------------------------------------------+
*
* Possible Routes Combinations :-
*
* 1. $router->registerRouteOfType(...)->with_controller(...)->withModel(...)->withView(...);
* 2. $router->registerRouteOfType(...)->with_controller(...)->withModel(...);
* 3. $router->registerRouteOfType(...)->with_controller(...)->withView(...);
* 4. $router->registerRouteOfType(...)->with_controller(...);
* 5. $router->registerRouteOfType(...)->with_just_model(...);
*
* with_controller, withModel, withView, with_just_model methods accept either a string or
* a callback. But the callback must return respective Controller/Model or View name.
*
* with_controlller & with_just_model methods supports '@' in the Controller/Model passed to
* them allowing you to call a particular method. That method does not need to be a static
* method. It can be a public method.
*/

/*
|-------------------------------------------------------------------------------------------
| Simple Example - This example creates a admin route (triggered only when it is a dashboard)
|-------------------------------------------------------------------------------------------
|	$router
|		->registerRouteOfType( RouteType::ADMIN )
|		>with_controller( 'Admin_Settings' ) // Resolved by Router to 'Plugin_Name\App\Controller\Admin\Admin_Settings'.
|		->withModel( 'Admin_Settings' ) // Resolved by Router to 'Plugin_Name\App\Models\Admin\Admin_Settings'.
|		->withView( 'Admin_Settings' ); // Resolved by Router to 'Plugin_Name\App\Views\Admin\Admin_Settings'.
|-------------------------------------------------------------------------------------------
*/

/*
|-------------------------------------------------------------------------------------------
| Routes with Full Class Names Example. Above route could also be written as :-
|-------------------------------------------------------------------------------------------
|	$router
|		->registerRouteOfType( RouteType::ADMIN )
|		->withController( 'Plugin_Name\App\Controller\Admin\Admin_Settings' )
|		->withModel( 'Plugin_Name\App\Models\Admin\Admin_Settings' )
|		->withView( 'Plugin_Name\App\Views\Admin\Admin_Settings' );
|-------------------------------------------------------------------------------------------
*/

/*
|-------------------------------------------------------------------------------------------
| '@' Symbol Example :-
|
|  Again, @ is supported in withController & with_just_model methods only.
|-------------------------------------------------------------------------------------------
|	$router
|		->registerRouteOfType( RouteType::LATE_FRONTEND )
|		->withController( 'Sample_Shortcode@register_shortcode' )
|		->withModel( 'Sample_Shortcode' )
|		->withView( 'Sample_Shortcode' );
|-------------------------------------------------------------------------------------------
*/

/*
|-------------------------------------------------------------------------------------------
| Example of Loading controller if conditions match. Late Frontend Routes are triggerred on
| `wp` hook. Therefore, you should ideally be able to access template related functions
| in the callback passed to `withController` method below
|-------------------------------------------------------------------------------------------
|	$router
|		->registerRouteOfType( RouteType::LATE_FRONTEND )
|		->withController(
|			function() {
|
|				if ( get_the_ID() == '3367' ) {
|					return 'Sample_Shortcode';
|				}
|
|				return false;
|			}
|		)
|		->withView( 'Sample_Shortcode' );
|-------------------------------------------------------------------------------------------
*/

/*
|-------------------------------------------------------------------------------------------
| If you want to load only model for specific route, you can use with_just_model.
|
| Note: This type of route is referred as `Model Only` route.`Model Only` routes
| don't support Views. This type of route should ideally be used when you have
| to work at data layer but there is nothing to print on the screen.
|-------------------------------------------------------------------------------------------
|	$router
|		->registerRouteOfType( RouteType::ADMIN )
|		->with_just_model('Plugin_Name_Model_Admin_Settings');
|-------------------------------------------------------------------------------------------
*/

/* That's all, start creating your own routes below this line! Happy coding. */

// Route for Settings Page.
$router
    ->registerRouteOfType(RouteType::ADMIN)
    ->withController(
        'Admin_Settings@register_hook_callbacks'
    ) // Resolved by Router to 'Plugin_Name\App\Controller\Admin\Admin_Settings'.
    ->withModel('Admin_Settings') // Resolved by Router to 'Plugin_Name\App\Models\Admin\Admin_Settings'.
    ->withView('Admin_Settings'); // Resolved by Router to 'Plugin_Name\App\Views\Admin\Admin_Settings'.
