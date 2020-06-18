<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use App\Form\ArticleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class BlogController extends AbstractController
{

    public function index()
    {
        $articles = $this->getDoctrine()->getRepository(Article::class)->findBy(
            ['isPublished' =>true],
            ['publicationDate' => 'desc']
        );
        return $this->render('blog/index.html.twig',[
            'articles' => $articles
        ]);
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function add(Request $request)
    {

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $article->setLastUpdateDate(new \DateTime());

            if($article->getPicture() !== null){
                $file = $form->get('picture')->getData();
                $filename = uniqid().'.'.$file->guessExtension();
                try{
                    $file->move(
                        $this->getParameter('images_directory'),
                        $filename
                    );
                }catch(FileException $e){
                    return new Response($e->getMessage());
                }
                $article->setPicture($filename);
            }
            if ($article->getIsPublished()) {
                $article->setPublicationDate(new \DateTime());
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            //return new Response('L\'article a bien été enregistrer.');
            return $this->redirectToRoute('admin');
        }

        return $this->render('blog/add.html.twig',['form'=>$form->createView()]);
    }

    public function show(Article $article)
    {
        return $this->render('blog/show.html.twig', [
            'article' => $article
        ]);
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Article $article,Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $old_picture = $article->getPicture();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $article->setLastUpdateDate(new \DateTime());
            if($article->getIsPublished()){
                $article->setPublicationDate(new \DateTime());
            }
            if($article->getPicture() !== null && $article->getPicture() !== $old_picture){
                $file = $form->get('picture')->getData();
                $filename = uniqid().'.'.$file->guessExtension();
                try{
                    $file->move(
                        $this->getParameter('images_directory'),
                        $filename
                    );
                }catch (FileException $e){
                    return new Response($e->getMessage());
                }
                $article->setPicture($filename);
            } else{
                $article->setPicture($old_picture);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();
            //return new Response('l\'article a été bien modifié');
            return $this->redirectToRoute('admin');
        }
        //$article = $this->getDoctrine()->getRepository(Article::class)->find($id);
        //$form = $this->createForm(ArticleType::class, $article);
        return $this->render('blog/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView()
        ]);
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    public function remove(Article $article,Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();
        return $this->redirectToRoute('admin');
    }
    public function admin()
    {
        $articles = $this->getDoctrine()->getRepository(Article::class)->findBy(
            [],
            ['lastUpdateDate' =>'desc']
        );
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
        return $this->render('admin/index.html.twig',[
            'articles' => $articles,
            'categories' => $categories,
            'users' => $users
        ]);
    }
}
