<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 25/03/2015
 * Time: 18:48
 */

class AffichageArticles {

    function listingArticle($articles)
    {


        $html = <<<HTML
<html>
<head>
<link rel="stylesheet" href="css/bootstrap.min.css" />
</head>
<body>
<div class="list-group">
  <a href="#" class="list-group-item active">
    Cras justo odio
  </a>
  <a href="#" class="list-group-item">Dapibus ac facilisis in</a>
  <a href="#" class="list-group-item">Morbi leo risus</a>
  <a href="#" class="list-group-item">Porta ac consectetur ac</a>
  <a href="#" class="list-group-item">Vestibulum at eros</a>
</div>
</body>
</html>

HTML;
        echo $html;
    }

}