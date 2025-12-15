<?php

namespace App\Controller;

use App\Entity\Meubles;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class MeublesController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(EntityManagerInterface $em, Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add("critere", TextType::class, [
                'label' => 'Rechercher un meuble',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nom ou description...']
            ])
            ->add('Valider', SubmitType::class, [
                'attr' => ['class' => 'btn btn-info mt-2']
            ])
            ->getForm();

        $form->handleRequest($request);

        $repo = $em->getRepository(Meubles::class);
        $lesMeubles = $repo->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $critere = $data['critere'];

            if (!empty($critere)) {
                $lesMeubles = $repo->recherche($critere);
            }
        }

        return $this->render('meubles/home.html.twig', [
            'lesMeubles' => $lesMeubles,
            'form' => $form->createView()
        ]);
    }

    #[Route('/meubles/ajouter', name: 'add_meuble')]
    public function ajouterMeuble(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $meuble = new Meubles();

        $form = $this->createFormBuilder($meuble)
            ->add('nomM', TextType::class, [
                'label' => 'Nom du meuble',
                'attr' => ['class' => 'form-control']
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix (TND)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('since_At', DateType::class, [
                'label' => 'Date d\'ajout',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ])
            ->add('image', FileType::class, [
                'label' => 'Image du meuble (optionnel)',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'nomCategory',
                'label' => 'Catégorie',
                'attr' => ['class' => 'form-control']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Gestion de l'upload de l'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l’upload de l’image.');
                }

                $meuble->setImage($newFilename);
            }

            $em->persist($meuble);
            $em->flush();

            $this->addFlash('success', 'Meuble ajouté avec succès !');
            return $this->redirectToRoute('app_meubles');
        }

        return $this->render('meubles/ajouter.html.twig', [
            'f' => $form->createView(),
            'titre' => 'Ajouter un meuble'
        ]);
    }

    #[Route('/meubles/editer/{id}', name: 'edit_meuble', requirements: ['id' => '\d+'])]
    public function edit(Request $request, int $id, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $meuble = $em->getRepository(Meubles::class)->find($id);

        if (!$meuble) {
            throw $this->createNotFoundException('Aucun meuble trouvé avec l\'id '.$id);
        }

        $fb = $this->createFormBuilder($meuble)
            ->add('nomM', TextType::class, [
                'label' => 'Nom du meuble',
                'attr' => ['class' => 'form-control']
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix (TND)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('since_At', DateType::class, [
                'label' => 'Date d\'ajout',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ])
            ->add('image', FileType::class, [
                'label' => 'Image du meuble (optionnel)',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'nomCategory',
                'label' => 'Catégorie',
                'attr' => ['class' => 'form-control']
            ]);

        $form = $fb->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l’upload de l’image.');
                }

                $meuble->setImage($newFilename);
            }

            $em->flush();
            $this->addFlash('success', 'Meuble modifié avec succès !');
            return $this->redirectToRoute('app_meubles');
        }

        return $this->render('meubles/ajouter.html.twig', [
            'f' => $form->createView(),
            'titre' => 'Modifier le meuble'
        ]);
    }

    #[Route('/meubles/supp/{id}', name: 'meuble_delete', requirements: ['id' => '\d+'])]
    public function delete(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $meuble = $em->getRepository(Meubles::class)->find($id);

        if (!$meuble) {
            throw $this->createNotFoundException('Aucun meuble trouvé avec l\'id '.$id);
        }

        $em->remove($meuble);
        $em->flush();

        $this->addFlash('success', 'Meuble supprimé avec succès !');
        return $this->redirectToRoute('app_meubles');
    }

    #[Route('/meubles/editAll', name: 'app_meubles_edit_all', methods: ['GET', 'POST'])]
    public function editAll(Request $request, EntityManagerInterface $em): Response
    {
        $meubles = $em->getRepository(Meubles::class)->findAll();

        if ($request->isMethod('POST')) {
            foreach ($meubles as $meuble) {
                $nom = $request->request->get('nomM_'.$meuble->getId());
                $prix = $request->request->get('prix_'.$meuble->getId());

                if ($nom) $meuble->setNomM($nom);
                if ($prix) $meuble->setPrix(floatval($prix));

                $em->persist($meuble);
            }

            $em->flush();
            $this->addFlash('success', 'Tous les meubles ont été modifiés avec succès.');
            return $this->redirectToRoute('app_meubles_edit_all');
        }

        return $this->render('meubles/edit_all.html.twig', [
            'meubles' => $meubles,
        ]);
    }

    #[Route('/meubles', name: 'app_meubles')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $meubles = $entityManager->getRepository(Meubles::class)->findAll();

        return $this->render('meubles/index.html.twig', [
            'meubles' => $meubles,
        ]);
    }

    #[Route('/meubles/{id}', name: 'meuble_show', requirements: ['id' => '\d+'])]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $meuble = $entityManager->getRepository(Meubles::class)->find($id);

        if (!$meuble) {
            throw $this->createNotFoundException('Aucun meuble trouvé avec l\'id '.$id);
        }

        return $this->render('meubles/show.html.twig', [
            'meuble' => $meuble,
        ]);
    }
}
