<?php

namespace App\Controller;

use App\Entity\Formule;
use App\Form\FavoriteType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccountFavoriteController extends AbstractController
{
    #[Route('/compte/favorite', name: 'app_account_favorite')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(FavoriteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Traitez les données du formulaire ici, par exemple, enregistrez-les en base de données
            
        }

        return $this->render('account/favorite.html.twig', [
            'form' => $form->createView(),]);
    }
}
