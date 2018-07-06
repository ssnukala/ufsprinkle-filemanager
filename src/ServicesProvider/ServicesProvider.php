<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/ufsprinkle-dsd
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\FileManager\ServicesProvider;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager;
use UserFrosting\Sprinkle\Core\Facades\Debug;

/**
 * Registers services for the DSD sprinkle.
 *
 * @author Srinivas Nukala (https://srinivasnukala.com)
 */
class ServicesProvider
{
    /**
     * Register UserFrosting's DSD services.
     *
     * @param Container $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register($container)
    {

        /**
         * Returns a callback that forwards to dashboard if user is already logged in.
         */
        $container['redirect.serveStub'] = function ($c) {
            /**
             * This method is invoked when a user attempts to perform certain public actions when they are already logged in.
             *
             * @todo Forward to user's landing page or last visited page
             * @param \Psr\Http\Message\ServerRequestInterface $request
             * @param \Psr\Http\Message\ResponseInterface      $response
             * @param array $args
             * @return \Psr\Http\Message\ResponseInterface
             */
            return function (Request $request, Response $response, array $args) use ($c) {
                $redirect = $c->router->pathFor('flystub');
                return $response->withRedirect($redirect, 302);
            };
        };
    }
}
