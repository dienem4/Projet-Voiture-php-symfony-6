<?php

namespace App\Controller;

use App\Entity\RechercheVoiture;
use App\Entity\Voiture;
use App\Form\RechercheControllerType;
use App\Form\VoitureType;
use App\Repository\VoitureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin')]
    public function index(VoitureRepository $repo,PaginatorInterface $paginatorInterface, Request $request)
    {
        $rechercheVoiture = new RechercheVoiture();

        $form = $this->createForm(RechercheControllerType::class,$rechercheVoiture);
        $form->handleRequest($request);

        $voitures = $paginatorInterface->paginate(
            $repo->findAllWithPagination($rechercheVoiture),
            $request->query->getInt('page', 1), /*page number*/
            6 /*limit per page*/
        );
        return $this->render('voiture/voitures.html.twig',[
            "voitures" => $voitures,
            "form" => $form->createView(),
            "admin" => true
        ]);
    }

    #[Route('/admin/creation', name: 'creationVoiture')]
    #[Route('/admin/modication/{id}', name: 'modifVoiture', methods:'GET|POST')]
    public function modification(Voiture $voiture = null, Request $request, EntityManagerInterface $om){
        if(!$voiture){
            $voiture = new Voiture();
        }
        
        $form = $this->createForm(VoitureType::class,$voiture);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $om->persist($voiture);
            $om->flush();
            $this->addFlash('success', "L'action a été effectué");
            return $this->redirectToRoute("admin");
        }

        return $this->render('admin/modification.html.twig',[
            "voiture" => $voiture,
            "form" => $form->createView()
        ]);
    }

    #[Route('/admin/{id}', name: 'supVoiture')]
    public function suppression(Voiture $voiture, Request $request, EntityManagerInterface $om){
        if($this->isCsrfTokenValid("SUP".$voiture->getId(), $request->get("_token"))){
            $om->remove($voiture);
            $om->flush();
            $this->addFlash('success', "L'action a été effectué");
            return $this->redirectToRoute("admin");
        }
    }


}
