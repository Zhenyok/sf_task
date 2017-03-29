<?php

namespace Itransition\DataImporterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ItransitionDataImporterBundle:Default:index.html.twig');
    }
}
