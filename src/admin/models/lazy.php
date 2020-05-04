<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

class AddlazyloadingModelLazy extends Joomla\CMS\MVC\Model\ListModel {

  /** Run Forest, run... */
  public function updatedb() {
    $input = Factory::getApplication()->input->json;
    $catId = $input->getInt('catId');
    $from = $input->getInt('from');
    $to = $input->getInt('to');
    $requested = $input->getInt('requested');

    // Get the articles
    $articles = $this->getArticles($catId, $from, $to);

    if (count($articles) === 0) {
      return ['articles' => [], 'category' => ['id' => $catId, 'done' => true], 'request' => ['from' => $from, 'to' => $to, 'catId' => $catId, 'requested' => $requested]];
    }

    $updatedArticles = [];

    // Loop da loop
    for ($i = $from; $i < $to; $i++) {
      $article = $articles[$i];
      $newIntroText = $this->convert($article->intro_text);
      $newFullText = $this->convert($article->full_text);

      if ($newIntroText !== '' || $newFullText !== '') {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->update($db->qn('#__content'))->whereIn($db->qn('id'), $article->id);

        if ($newIntroText !== '') {
          $query->set($db->qn('intro_text') . ' = ' . $newIntroText);
        }

        if ($newFullText !== '') {
          $query->set($db->qn('intro_text') . ' = ' . $newFullText);
        }

        $db->setQuery($query)->execute();

        $updatedArticles[] = $article->id;
      }
    }

    // Return the ids of the articles touched
    return ['articles' => $updatedArticles, 'category' => ['id' => $catId, 'done' => true], 'request' => ['from' => $from, 'to' => $to, 'catId' => $catId, 'requested' => $requested]];
  }

  /** Get all the articles */
  private function countArticles($catId) {
    $db = $this->getDbo();

    $query = $db->getQuery(true)
      ->select('COUNT(id)')
      ->from($db->qn('#__content'))
      ->where(
        $db->qn('catid') . ' = ' . $db->q($catId)
      );
    $db->setQuery($query);

    return (int) $db->loadResult();
  }

  /** Get all the articles */
  private function getArticles($catId, $from, $to) {
    $db = $this->getDbo();

    $query = $db->getQuery(true)
      ->select('*')
      ->from($db->qn('#__content'))
      ->where(
        $db->qn('catid') . ' = ' . $db->q($catId)
      );

      if ($from > 0) {
        $query->offset($db->q((int) $from));
      }

      if ($to > 0) {
        $query->limit($db->q((int) $to));
      }

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

    foreach ($matches[0] as $image) {
      // Make sure we have a src but no loading attribute
      if (strpos($image, ' src=') !== false && strpos($image, ' loading=') === false) {
        $lazyloadImage = str_replace('<img ', '<img loading="lazy" ', $image);
        $content = str_replace($image, $lazyloadImage, $content);
      }
    }

    return $content;
  }

  public function getCategories() {
    $db = $this->getDbo();

    $query = $db->getQuery(true)
      ->select('*', 'extension')
      ->from($db->qn('#__categories'))
      ->where(
        $db->qn('extension') .' = ' . $db->q('com_content')
      );
    $db->setQuery($query);
    $db->getCount();
    $categories = $db->loadObjectList();

    foreach ($categories as $category) {
      $category->articlesCount = $this->countArticles($category->id, true);
    }

    return $categories;
  }
}
