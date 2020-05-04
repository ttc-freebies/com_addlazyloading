<?php
defined('_JEXEC') or die('Restricted access');

$controller = Joomla\CMS\MVC\Controller\BaseController::getInstance('Addlazyloading');
$controller->execute(Joomla\CMS\Factory::getApplication()->input->get('task'));
$controller->redirect();
