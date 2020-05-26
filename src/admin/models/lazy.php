<?php
defined('_JEXEC') || die('Restricted access');

use Joomla\CMS\Factory;

class AddlazyloadingModelLazy extends Joomla\CMS\MVC\Model\ListModel {
  private $table;
  private $columns;
  private $id;

  public function __construct($config = array()) {
    $params = \Joomla\CMS\Component\ComponentHelper::getParams('com_addlazyloading');

    $this->table = $params->get('table', 'content');
    $this->id = $params->get('id', 'id');
    $cols = $params->get('columns', 'introtext,fulltext');
    $this->columns = explode(',', $cols);

    parent::__construct($config);
  }

  /** Run Forest, run... */
  public function updatedb() {
    $input = Factory::getApplication()->input->json;
    $from = $input->getInt('from', 1);
    $to = $input->getInt('to', 1);

    $itemsCount = $input->getInt('itemsCount', 0);

    // Get the items
    $items = $this->getSelectedItems($from, $to);

    if (count($items) === 0 || (($from + 1) === $itemsCount)) {
      return array(
        'itemsNo' => $itemsCount,
        'from' => $to,
      );
    }

    $updatedItems = $from;

    // Loop da loop
    for ($i = $from; $i < ($from + $to); $i++) {
      $item = $items[$i];
      $results = array();

      // Loop the columns
      foreach($this->columns as $col) {
        if ($item->{$col}) {
          $result = $this->convert($item->{$col});
          $results[$col] = $result;
        }
      }

      if (count($results) > 0) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $fields = array();

        foreach( $results as $key => $value ) {
          $fields[] = $db->quoteName($key) . ' = ' . $db->quote($value);
        }

        $query->update($db->quoteName('#__' . $this->table))->set($fields)->where($db->quoteName($this->id) . ' = ' . $db->quote($item->id));

        $db->setQuery($query);
        $db->execute();
      }

      $updatedItems = $i +1;
    }

    // Return the ids of the items touched
    return [
      'itemsNo' => $updatedItems,
      'from' => $from,
    ];
  }

  /** Get all the items */
  public function getSelectedItems($from, $to) {
    $db = $this->getDbo();

    $query = $db->getQuery(true)
      ->select('*')
      ->from($db->quoteName('#__' .$this->table));

    $offset = 0;

    if ($from > 0) {
      $offset = (int) $from;
    }

    $limit = (int) $to;

    $query->setLimit($limit, $offset);

    $db->setQuery($query);

    return $db->loadObjectList();
  }

  /**
   * Adds the loading=lazy attribute to image tags on a given string
   *
   * @param   string   $context  The context of the content being passed to the plugin.
   *
   * @return  string  Always returns void or true
   *
   * @since   1.0.0
   *
   * Coded by @	zero-24: https://github.com/joomla/joomla-cms/pull/28838
   */
  private function convert($content) {
    if (strpos($content, '<img') === false) {
      return '';
    }

    if (!preg_match_all('/<img\s[^>]+>/', $content, $matches)) {
      return '';
    }

    $responsivePlugin = false;
    if (JPluginHelper::isEnabled('content', 'responsive')) {
      JLoader::register('Ttc\Freebies\Responsive\Helper', JPATH_ROOT . '/plugins/content/responsive/helper.php', true);
      $responsivePlugin = true;
    }

    foreach ($matches[0] as $image) {
      // Make sure we have a src but no loading attribute
      if (strpos($image, ' src=') !== false && strpos($image, ' loading=') === false) {
        $lazyloadImage = str_replace('<img ', '<img loading="lazy" ', $image);
        $content = str_replace($image, $lazyloadImage, $content);
      }

      if ($responsivePlugin) {
        $helper = new \Ttc\Freebies\Responsive\Helper;
        $helper->transformImage($image, array(200, 320, 480, 768, 992, 1200, 1600, 1920));
      }
    }

    return $content;
  }

  /** Get all the items */
  public function countItems() {
    $db = $this->getDbo();

    $query = $db->getQuery(true)
      ->select('COUNT(' . $db->quoteName($this->id) . ')')
      ->from($db->qn('#__' .$this->table));

    $db->setQuery($query);

    return (int) $db->loadResult();
  }
}
