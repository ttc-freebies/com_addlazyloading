<?php
defined('_JEXEC') || die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

class AddlazyloadingViewLazy extends Joomla\CMS\MVC\View\HtmlView {
  function display($tpl = null) {
    // Access check.
    if (!Factory::getUser()->authorise('core.admin')) {
      throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
    }

    // Check for errors.
    if (count($errors = $this->get('Errors'))) {
      throw new Exception(implode('<br />', $errors), 500);

      return false;
    }

    Factory::getDocument()->setTitle('Add the loading=lazy attribute to all Articles images [intro+full]');
    $this->addToolbar();

    // Display the template
    parent::display($tpl);
  }

  protected function addToolbar() {
    $user  = JFactory::getUser();
    ToolbarHelper::title('Do the DB magic', 'info-2 systeminfo');
    $bar = Toolbar::getInstance('toolbar');
    $bar->appendButton('Custom', '<button class="btn btn-danger" disabled id="lazyLoadingButton" data-url="' . Uri::root(false) . '" data-items-count="' . $this->getModel()->countItems() . '" data-token="' . Session::getFormToken() . '" >♻︎ Update Articles</button>', 'Update Articles', 'updateArticles');
    if ($user->authorise('core.admin', 'com_addlazyloading') || $user->authorise('core.options', 'com_addlazyloading')) {
      ToolbarHelper::preferences('com_addlazyloading');
    }
  }
}
