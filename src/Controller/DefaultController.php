<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route({
     *     "fr": "/",
     *     "en": "/",
     *     "es": "/"
     * },
     *      name="app_index")
     */
    public function  index()
    {
        return $this->render('default.html.twig');
    }
}