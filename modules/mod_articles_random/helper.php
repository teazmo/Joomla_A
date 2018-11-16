<?php

abstract class ModArticlesRandomHelper
{
  public function getArticles(){

    $db = JFactory::getDbo();

    $query = $db->getQuery(true);

    $query->select('title');

    $query->from('#__content');

    $db->setQuery($query);

    $articles = $db->loadObjectList();

    return $articles;
  }
}
