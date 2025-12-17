<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Meubles;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CategoryController extends AbstractController
{
    #[Route('/category/ajouter', name: 'add_category')]
    public function ajouterCategory(Request $request, EntityManagerInterface $em): Response
    {
        $category = new Category();
        
        $form = $this->createFormBuilder($category)
            ->add('nomCategory', TextType::class, [
                'label' => 'Nom de la catégorie'
            ])
            ->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('app_category');
        }
        
        return $this->render('category/ajouter.html.twig', [
            'f' => $form->createView(),
        ]);
    }
    
    
    #[Route('/category', name: 'app_category')]

    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer toutes les catégories depuis la base de données
        $categories = $entityManager->getRepository(Category::class)->findAll();
        
        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }
    
    // 3. ROUTE DE DÉTAIL EN DERNIER (avec contrainte numérique)
    #[Route('/category/{id}', name: 'category_show', requirements: ['id' => '\d+'])]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $category = $entityManager->getRepository(Category::class)->find($id);
        
        if (!$category) {
            throw $this->createNotFoundException(
                'No category found for id '.$id
            );
        }
        
        // Récupérer les meubles de cette catégorie
        $meubles = $entityManager->getRepository(Meubles::class)
            ->findBy(['category' => $category]);
        
        return $this->render('category/show.html.twig', [
            'category' => $category,
            'meubles' => $meubles,
        ]);
    }
}