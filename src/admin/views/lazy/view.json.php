<?php

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class AddlazyloadingViewLazy extends JViewLegacy {
  public function display($tpl = null) {
    // Access check.
    if (!Factory::getUser()->authorise('core.admin')) {
      throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
    }

    echo json_encode($this->getModel()->updatedb());
    Factory::getApplication()->close();
  }
}
