<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class CategoryController extends AbstractController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function add(Request $request)
    {

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $category->setLastUpdateDate(new \DateTime());


            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            //return new Response('L\'article a bien été enregistrer.');
            return $this->redirectToRoute('admin');
        }

        return $this->render('category/add.html.twig',['form'=>$form->createView()]);
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Category $categorie,Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $form = $this->createForm(CategoryType::class, $categorie);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $categorie->setLastUpdateDate(new \DateTime());
            if($categorie->getIsPublished()){
                $categorie->setPublicationDate(new \DateTime());
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($categorie);
            $em->flush();
            return $this->redirectToRoute('admin');
        }
        //$article = $this->getDoctrine()->getRepository(Article::class)->find($id);
        //$form = $this->createForm(ArticleType::class, $article);
        return $this->render('category/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form->createView()
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function remove(Category $categorie,Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $em->remove($categorie);
        $em->flush();
        return $this->redirectToRoute('admin');
    }
}
