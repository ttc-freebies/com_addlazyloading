<?php

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class AddlazyloadingViewLazy extends Joomla\CMS\MVC\View\HtmlView {
  public function display($tpl = null) {
    // Access check.
    if (!Factory::getUser()->authorise('core.admin')) {
      throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
    }

    echo json_encode($this->getModel()->updatedb());
    Factory::getApplication()->close();
  }
}
