<?php

defined('_JEXEC') or die;

print_r($params);

?>

<ul>
  <?php foreach($articles as $article) : ?>
  <li><?php echo $article->title; ?></li>
  <?php endforeach; ?>
</ul>
