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

namespace Thelia\Tests\Model;

use Thelia\Model\ConfigQuery;
use Symfony\Component\Filesystem\Filesystem;
use Thelia\Model\Message;
use Thelia\Core\Template\Smarty\SmartyParser;
use Thelia\Core\Template\ParserContext;
use Thelia\Core\Template\TemplateHelper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\HttpFoundation\Session\Session;

/**
 * Class CustomerTest
 * @package Thelia\Tests\Action
 * @author Etienne Roudeix <eroudeix@openstudio.fr>
 */
class Message extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder $container
     */
    protected $container, $parser;

    private $backup_mail_template = 'undefined';

    public function setUp()
    {
        $this->backup_mail_template = ConfigQuery::read('active-mail-template', 'default');

        ConfigQuery::write('active-mail-template', 'test');

        @mkdir(TemplateHelper::getInstance()->getActiveMailTemplate()->getAbsolutePath(), 0777, true);

        $container = new ContainerBuilder();

        $session = new Session(new MockArraySessionStorage());
        $request = new Request();
        $dispatcher = $this->getMock("Symfony\Component\EventDispatcher\EventDispatcherInterface");

        $request->setSession($session);

        /*
         *  public function __construct(
            Request $request, EventDispatcherInterface $dispatcher, ParserContext $parserContext,
            $env = "prod", $debug = false)

         */
        $container->set("event_dispatcher", $dispatcher);
        $container->set('request', $request);

        $this->parser = new SmartyParser($request, $dispatcher, new ParserContext($request), 'dev', true);
        $this->parser->setTemplate(TemplateHelper::getInstance()->getActiveMailTemplate());

        $container->set('thelia.parser', $this->parser);

        $this->container = $container;
    }

    /**
     * Create message with HTML and TEXT body from message HTMl and TEXT fields
     */
    public function testMessageWithTextAndHtmlBody()
    {
        $message = new Message();

        $message->setLocale('fr_FR');

        $message->setSubject("The subject");
        $message->setHtmlMessage("The HTML content");
        $message->setTextMessage("The TEXT content");

        $instance = \Swift_Message::newInstance();

        $message->buildMessage($this->parser, $instance);

        $this->assertEquals("The subject", $instance->getSubject());
        $this->assertEquals("The HTML content", $instance->getBody());
        $this->assertEquals("The TEXT content", $instance->getChildren()[0]->getBody());
    }

    /**
     * Create message with TEXT body only from message HTMl and TEXT fields
     */
    public function testMessageWithTextOnlyBody()
    {
        $message = new Message();

        $message->setLocale('fr_FR');

        $message->setSubject("The subject");
        $message->setTextMessage("The TEXT content");

        $instance = \Swift_Message::newInstance();

        $message->buildMessage($this->parser, $instance);

        $this->assertEquals("The subject", $instance->getSubject());
        $this->assertEquals("The TEXT content", $instance->getBody());
        $this->assertEquals(0, count($instance->getChildren()));
    }

    /**
     * Create message with HTML and TEXT body from message HTMl and TEXT fields
     * using a text and a html layout
     */
    public function testMessageWithTextAndHtmlBodyAndTextAndHtmlLayout()
    {
        $message = new Message();

        $message->setLocale('fr_FR');

        $message->setSubject("The subject");
        $message->setTextMessage("The TEXT content");
        $message->setHtmlMessage("The HTML content");

        $message->setHtmlLayoutFileName('layout.html.tpl');
        $message->setTextLayoutFileName('layout.text.tpl');

        $path = TemplateHelper::getInstance()->getActiveMailTemplate()->getAbsolutePath();

        file_put_contents($path.DS.'layout.html.tpl', 'HTML Layout: {$message_body nofilter}');
        file_put_contents($path.DS.'layout.text.tpl', 'TEXT Layout: {$message_body nofilter}');

        $instance = \Swift_Message::newInstance();

        $message->buildMessage($this->parser, $instance);

        $this->assertEquals("The subject", $instance->getSubject());
        $this->assertEquals("HTML Layout: The HTML content", $instance->getBody());
        $this->assertEquals("TEXT Layout: The TEXT content", $instance->getChildren()[0]->getBody());
    }

    /**
     * Create message with TEXT only body from message HTMl and TEXT fields
     * using a text only layout
     */
    public function testMessageWithTextOnlyBodyAndTextOnlyLayout()
    {
        $message = new Message();

        $message->setLocale('fr_FR');

        $message->setSubject("The subject");
        $message->setTextMessage("The <TEXT> & content");

        $message->setTextLayoutFileName('layout3.text.tpl');

        $path = TemplateHelper::getInstance()->getActiveMailTemplate()->getAbsolutePath();

        file_put_contents($path.DS.'layout3.text.tpl', 'TEXT Layout 3: {$message_body nofilter} :-) <>');

        $instance = \Swift_Message::newInstance();

        $message->buildMessage($this->parser, $instance);

        $this->assertEquals("The subject", $instance->getSubject());
        $this->assertEquals("TEXT Layout 3: The <TEXT> & content :-) <>", $instance->getBody());
        $this->assertEquals(0, count($instance->getChildren()));
    }

    /**
     * Create message with TEXT and HTML body from message HTMl and TEXT fields
     * using a text only layout
     */
    public function testMessageWithTextAndHtmlBodyAndTextOnlyLayout()
    {
        $message = new Message();

        $message->setLocale('fr_FR');

        $message->setSubject("The subject");
        $message->setTextMessage("The <TEXT> & content");
        $message->setHtmlMessage("The <HTML> & content");

        $message->setTextLayoutFileName('layout3.text.tpl');

        $path = TemplateHelper::getInstance()->getActiveMailTemplate()->getAbsolutePath();

        file_put_contents($path.DS.'layout3.text.tpl', 'TEXT Layout 3: {$message_body nofilter} :-) <>');

        $instance = \Swift_Message::newInstance();

        $message->buildMessage($this->parser, $instance);

        $this->assertEquals("The subject", $instance->getSubject());
        $this->assertEquals("The <HTML> & content", $instance->getBody());
        $this->assertEquals("TEXT Layout 3: The <TEXT> & content :-) <>", $instance->getChildren()[0]->getBody());
    }

    /**
     * Create message with HTML and TEXT body from template HTMl and TEXT fields
     * using a text and a html layout
     */
    public function testMessageWithTextAndHtmlBodyAndTextAndHtmlLayoutAndTextAndHtmlTemplate()
    {
        $message = new Message();

        $message->setLocale('fr_FR');

        $message->setSubject("The subject");
        $message->setTextMessage("The TEXT content");
        $message->setHtmlMessage("The HTML content");

        $message->setTextTemplateFileName('template4-text.txt');
        $message->setHtmlTemplateFileName('template4-html.html');

        $message->setHtmlLayoutFileName('layout4.html.tpl');
        $message->setTextLayoutFileName('layout4.text.tpl');

        $path = TemplateHelper::getInstance()->getActiveMailTemplate()->getAbsolutePath();

        $this->parser->assign('myvar', 'my-value');

        file_put_contents($path.DS.'layout4.html.tpl', 'HTML Layout 4: {$message_body nofilter}');
        file_put_contents($path.DS.'layout4.text.tpl', 'TEXT Layout 4: {$message_body nofilter}');

        file_put_contents($path.DS.'template4-html.html', 'HTML <template> & content v={$myvar}');
        file_put_contents($path.DS.'template4-text.txt', 'TEXT <template> & content v={$myvar}');

        $instance = \Swift_Message::newInstance();

        $message->buildMessage($this->parser, $instance);

        $this->assertEquals("The subject", $instance->getSubject());
        $this->assertEquals("HTML Layout 4: HTML <template> & content v=my-value", $instance->getBody());
        $this->assertEquals("TEXT Layout 4: TEXT <template> & content v=my-value", $instance->getChildren()[0]->getBody());
    }

    /**
     * Create message with HTML and TEXT body from template HTMl and TEXT fields
     * using a text and a html layout
     */
    public function testMessageWithTextAndHtmlBodyAndTextAndHtmlLayoutAndTextAndHtmlTemplateWichExtendsLayout()
    {
        $message = new Message();

        $message->setLocale('fr_FR');

        $message->setSubject("The subject");
        $message->setTextMessage("The TEXT content");
        $message->setHtmlMessage("The HTML content");

        $message->setTextTemplateFileName('template5-text.txt');
        $message->setHtmlTemplateFileName('template5-html.html');

        //$message->setHtmlLayoutFileName('layout5.html.tpl');
        //$message->setTextLayoutFileName('layout5.text.tpl');

        $path = TemplateHelper::getInstance()->getActiveMailTemplate()->getAbsolutePath();

        $this->parser->assign('myvar', 'my-value');

        file_put_contents($path.DS.'layout5.html.tpl', 'HTML Layout 5: {block name="message-body"}{$message_body nofilter}{/block}');
        file_put_contents($path.DS.'layout5.text.tpl', 'TEXT Layout 5: {block name="message-body"}{$message_body nofilter}{/block}');

        file_put_contents($path.DS.'template5-html.html', '{extends file="layout5.html.tpl"}{block name="message-body"}HTML <template> & content v={$myvar}{/block}');
        file_put_contents($path.DS.'template5-text.txt' , '{extends file="layout5.text.tpl"}{block name="message-body"}TEXT <template> & content v={$myvar}{/block}');

        $instance = \Swift_Message::newInstance();

        $message->buildMessage($this->parser, $instance);

        $this->assertEquals("The subject", $instance->getSubject());
        $this->assertEquals("HTML Layout 5: HTML <template> & content v=my-value", $instance->getBody());
        $this->assertEquals("TEXT Layout 5: TEXT <template> & content v=my-value", $instance->getChildren()[0]->getBody());
    }

    /**
     * Create message with HTML and TEXT body from template HTMl and TEXT fields
     * using a text and a html layout
     */
    public function testMessageWithTextAndHtmlBodyAndTextAndHtmlExtendableLayout()
    {
        $message = new Message();

        $message->setLocale('fr_FR');

        $message->setSubject("The subject");
        $message->setTextMessage('TEXT <template> & content v={$myvar}');
        $message->setHtmlMessage('HTML <template> & content v={$myvar}');

        $message->setHtmlLayoutFileName('layout6.html.tpl');
        $message->setTextLayoutFileName('layout6.text.tpl');

        $path = TemplateHelper::getInstance()->getActiveMailTemplate()->getAbsolutePath();

        $this->parser->assign('myvar', 'my-value');

        file_put_contents($path.DS.'layout6.html.tpl', 'HTML Layout 6: {block name="message-body"}{$message_body nofilter}{/block}');
        file_put_contents($path.DS.'layout6.text.tpl', 'TEXT Layout 6: {block name="message-body"}{$message_body nofilter}{/block}');

        $instance = \Swift_Message::newInstance();

        $message->buildMessage($this->parser, $instance);

        $this->assertEquals("The subject", $instance->getSubject());
        $this->assertEquals("HTML Layout 6: HTML <template> & content v=my-value", $instance->getBody());
        $this->assertEquals("TEXT Layout 6: TEXT <template> & content v=my-value", $instance->getChildren()[0]->getBody());
    }

    protected function tearDown()
    {
        $dir = TemplateHelper::getInstance()->getActiveMailTemplate()->getAbsolutePath();

        ConfigQuery::write('active-mail-template', $this->backup_mail_template);

        $fs = new Filesystem();

        $fs->remove($dir);
    }
}
