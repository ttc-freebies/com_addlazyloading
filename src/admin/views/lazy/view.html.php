<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

class AddlazyloadingViewLazy extends JViewLegacy {
  function display($tpl = null) {
    // Access check.
    if (!Factory::getUser()->authorise('core.admin')) {
      throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
    }

    // Check for errors.
    if (count($errors = $this->get('Errors'))) {
      JError::raiseError(500, implode('<br />', $errors));

      return false;
    }

    Factory::getDocument()->setTitle('Add the loading=lazy attribute to all Articles images [intro+full]');
    $this->addToolbar();

    $this->categories = $this->getModel()->getCategories();

    // Display the template
    parent::display($tpl);
  }


  protected function addToolbar() {
    ToolbarHelper::title('Do the DB magic', 'info-2 systeminfo');
    $bar = Toolbar::getInstance('toolbar');
    $bar->appendButton('Custom', '<button class="btn btn-danger" disabled id="lazyLoadingButton" data-url="' . Uri::root(false) . '" data-records="' . Uri::root(false) . '" data-token="' . JSession::getFormToken() . '" >♻︎ Update Articles</button>', 'Update Articles', 'updateArticles');
  }
}