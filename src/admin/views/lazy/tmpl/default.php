<?php
defined('_JEXEC') || die('Restricted Access');

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('script', 'com_addlazyloading/app.esm.js', ['version' => 'auto', 'relative' => true], ['type' => 'module', 'defer' => true]);
HTMLHelper::_('script', 'com_addlazyloading/app.es5.js', ['version' => 'auto', 'relative' => true], ['nomodule' => '', 'defer' => true]);

echo
'<section style="min-height: 100vh">' .
    '<h1>Select how many articles should be processed per request:</h1>' .
    '<div class="control-group">' .
        '<label class="control-label" for="step">Articles per request</label>' .
        '<div class="controls">' .
          '<input id="step" type="number" value="1" min="1" max="99999" pattern="[0-9]{5}"/>' .
        '</div>' .
     '</div>' .
    '<hr/>' .
    '<h2 id="lazyLoadingTitle">Then click on the Update Articles button to initiate the process</h2>' .
    '<div id="lazyLoadingApp"></div>' .
'</section>';
