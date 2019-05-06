<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog/show/{article<[a-z-0-9]+>?article-sans-titre}", name="blog_show")
     */
    public function show($article)
    {
        $titre = ucwords(implode(' ',explode('-',$article)));
        return $this->render('Blog/show.html.twig',['titre' => $titre]);
    }
}
