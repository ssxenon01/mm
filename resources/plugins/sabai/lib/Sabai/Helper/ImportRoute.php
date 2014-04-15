<?php
class Sabai_Helper_ImportRoute extends Sabai_Helper
{
    /**
     * Imports content using ajax to a specific part of the page
     *
     * @param Sabai $application
     * @param string $id
     * @param Sabai_Route $route
     * @param Sabai_Context $parentContext
     */
    public function help(Sabai $application, $id, Sabai_Route $route, Sabai_Context $parentContext)
    {
        $current_plugin = $application->getCurrentAddonName();
        $application->setCurrentAddon($route->getAddon());

        // Check if the current user is allowed access to this route content
        $route_data = $route->getData();
        if ($route_data['access_callback']) {
            $method = $parentContext->isAdmin() ? 'systemOnAccessAdminRoute' : 'systemOnAccessMainRoute';
            if (!$application->getAddon($route_data['callback_addon'])->$method(
                $parentContext,
                $route_data['callback_path'],
                Sabai::ROUTE_ACCESS_CONTENT,
                $route_data
            )) {
                return __('Access denied.', 'sabai');
            }
        }

        // Create context
        $context = new Sabai_Context();
        $context->setContainer($id)->setRoute($route)->setParent($parentContext);

        // Create controller
        $controller_class = $route->getController();
        require_once $route->getControllerFile();
        $controller = new $controller_class;
        $controller->setApplication($application)->setRoute((string)$route);

        // Run the application
        try {
            $response = $application->run($controller, $context);
            if ($context->isView()) {
                ob_start();
                $response->send($context);
                $ret = ob_get_clean();
            } else {
                if ($context->isError()) {
                    $ret = sprintf(
                        __('An error occurred while importing content. Error: %s', 'sabai'),
                        $context->getErrorType() . ' ' . $context->getErrorMessage()
                    );
                } else {
                    $ret = __('Could not import content.', 'sabai');
                }
            }
        } catch (Exception $e) {
            $ret = sprintf(
                __('An error occurred while importing content. Error: %s', 'sabai'),
                $e->getMessage()
            );
        }

        $application->setCurrentAddon($current_plugin); // set back the current plugin name

        return $ret;
    }
}