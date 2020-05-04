<?php
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('script', 'com_addlazyloading/app.esm.js', ['version' => 'auto', 'relative' => true), ['type' => 'module', 'defer' => true]];
HTMLHelper::_('script', 'com_addlazyloading/app.es5.js', ['version' => 'auto', 'relative' => true), ['nomodule' => '', 'defer' => true]];

echo '<h1 id="lazyLoadingTitle">⬆️⬆️⬆️ Click on the Update Articles button to initialise the process</h1>'
     . '<div id="lazyLoadingApp"></div>'
     . '<script type="application/json" id="lazyLoadingCategories">' . json_encode($this->categories) . '</script>';
