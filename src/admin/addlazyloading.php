<?php
defined('_JEXEC') or die('Restricted access');

$controller = JControllerLegacy::getInstance('Addlazyloading');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
