<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace Thelia\Action;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Thelia\Core\Template\ParserInterface;
use Thelia\Exception\AdminAccessDenied;
use Thelia\Model\ConfigQuery;
use Thelia\Core\Template\TemplateHelper;

/**
 *
 * Class HttpException
 * @package Thelia\Action
 * @author Etienne Roudeix <eroudeix@openstudio.fr>
 */
class HttpException extends BaseAction implements EventSubscriberInterface
{
    /**
     * @var ParserInterface
     */
    protected $parser;

    public function __construct(ParserInterface $parser)
    {
         $this->parser = $parser;
    }

    public function checkHttpException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if ($exception instanceof NotFoundHttpException) {
            $this->display404($event);
        }

        if ($exception instanceof AccessDeniedHttpException) {
            $this->display403($event);
        }

        if ($exception instanceof AdminAccessDenied) {
            $this->displayAdminGeneralError($event);
        }
    }

    protected function displayAdminGeneralError(GetResponseForExceptionEvent $event)
    {
        // Define the template thant shoud be used
        $this->parser->setTemplateDefinition(TemplateHelper::getInstance()->getActiveAdminTemplate());

        $message = $event->getException()->getMessage();

        $response = Response::create(
            $this->parser->render('general_error.html',
                array(
                    "error_message" => $message
                )),
            403
        ) ;

        $event->setResponse($response);
    }

    protected function display404(GetResponseForExceptionEvent $event)
    {
        // Define the template thant shoud be used
        $this->parser->setTemplateDefinition(TemplateHelper::getInstance()->getActiveFrontTemplate());

        $response = new Response($this->parser->render(ConfigQuery::getPageNotFoundView()), 404);

        $event->setResponse($response);
    }

    protected function display403(GetResponseForExceptionEvent $event)
    {
        $event->setResponse(new Response("You don't have access to this resources", 403));
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array("checkHttpException", 128),
        );
    }
}
