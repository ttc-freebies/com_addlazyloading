<?php
defined('_JEXEC') || die('Restricted access');

use Joomla\CMS\Factory;

class AddlazyloadingModelLazy extends Joomla\CMS\MVC\Model\ListModel {
  private $table;
  private $tablePointer;
  private $id;
  private $columns;
  private $from;
  private $to;
  private $itemsCount;
  private $total;

  public function __construct($config = array()) {
    // Get the data from the input
    $input = Factory::getApplication()->input->json;
    $this->table = $input->get('table', 'content');
    $this->tablePointer = $input->getInt('tablePointer', 0);
    $this->id = $input->get('id', 'id');
    $this->columns = $input->get('columns', '');
    $this->from = $input->getInt('from', 1);
    $this->to = $input->getInt('to', 1);
    $this->itemsCount = $input->getInt('itemsCount', 0);
    $this->total = $input->getInt('total', 0);

    parent::__construct($config);
  }

  /** Run Forest, run... */
  public function updatedb() {
    $items = $this->getSelectedItems();

    if (count($items) === 0) {
      return array(
        'tablePointer' => $this->tablePointer,
        'itemsCount' => $this->itemsCount,
        'from' => $this->to,
        'total' => $this->total,
      );
    }

    $updatedItems = $this->from;

    // Loop da loop
    foreach ($items as $item) {
      $results = array();

      // Loop the columns
      foreach($this->columns as $col) {
        if ($item->{$col}) {
          $result = $this->convertTextareaImages($item->{$col});
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

      $updatedItems += 1;
    }

    if ((($this->from + 1) === $this->itemsCount)) {
      return array(
        'tablePointer' => $this->tablePointer,
        'itemsCount' => $this->itemsCount,
        'from' => $this->to,
        'total' => $this->total,
      );
    }

    // Return the ids of the items touched
    return [
      'tablePointer' => $this->tablePointer,
      'itemsCount' => $this->updatedItems,
      'from' => $this->from,
      'total' => $this->total,
    ];
  }

  /** Get a range of items from the table */
  public function getSelectedItems() {
    $db = $this->getDbo();

    $query = $db->getQuery(true)
      ->select('*')
      ->from($db->quoteName('#__' . $this->table));

    $offset = 0;

    if ((int) $this->from > 0) {
      $offset = (int) $this->from;
    }

    $limit = (int) $this->to;

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
  private function convertTextareaImages($content) {
    if (strpos($content, '<img') === false) {
      return '';
    }

    if (!preg_match_all('/<img\s[^>]+>/', $content, $matches)) {
      return '';
    }

    foreach ($matches[0] as $image) {
      // Make sure we have a src but no loading attribute
      if (strpos($image, ' src=') !== false && strpos($image, ' loading=') === false) {
        $lazyloadImage = str_replace('<img ', '<img loading="lazy" ', $image);
        $content = str_replace($image, $lazyloadImage, $content);
      }
    }

    return $content;
  }

  /** Return the tables/num of rows object */
  public function getTables() {
    $output = new \stdClass;

    try {
      $tablesRaw = file_get_contents(__DIR__ . '/tables.json');
    } catch (\Exception $e) {
      throw new \Exception('File is missing');
    }

    try {
    $this->tables = json_decode($tablesRaw);
    } catch (\Exception $e) {
      throw new \Exception('Couldn\'t parse the JSON file');
    }

    foreach($this->tables as $key => $value) {
      $totalRows = $this->countItems($this->tables->{$key}->id, $key);

      if ($totalRows !== false && $totalRows > 0) {
        $output->{$key} = $totalRows;
      }
    }

    return $output;
  }

  /** Return the num of rows of a table */
  public function countItems($id, $table) {
    $result = false;
    $db = $this->getDbo();

    $query = $db->getQuery(true)
      ->select('COUNT(' . $db->quoteName($id) . ')')
      ->from($db->qn('#__' . $table));

    $db->setQuery($query);

    try {
        $result = (int) $db->loadResult();
    } catch (Exception $e) {
      // The table/id combo doesn't exist
    }

    return $result;
  }
}
