<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TestMongoController extends AbstractController
{
    /**
     * @Route("/test/mongo", name="test_mongo")
     */
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/TestMongoController.php',
        ]);
    }
}
