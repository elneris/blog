<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use App\Service\Slugify;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route({
 *     "fr": "/article",
 *     "en": "/article",
 *     "es": "/articulo",
 * })
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="article_index", methods={"GET"})
     */
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAllWithCategoriesAndTags(),
        ]);
    }

    /**
     * @Route({
     *     "fr": "/ajout",
     *     "en": "/new",
     *     "es": "/nuevo"
     * }, name="article_new", methods={"GET","POST"})
     * @param Request $request
     * @param Slugify $slugify
     * @return Response
     */
    public function new(Request $request, Slugify $slugify, \Swift_Mailer $mailer): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $article->setSlug($slugify->generate($article->getTitle()));
            $author = $this->getUser();
            $article->setAuthor($author);
            $entityManager->persist($article);
            $entityManager->flush();

            $message = (new \Swift_Message('Un nouvel article vient d\'être publié !'))
                ->setFrom($this->getParameter('mailer_from'))
                ->setTo($this->getParameter('mailer_from'))
                ->setBody(
                    $this->renderView(
                        'Article/email/notification.html.twig',
                        ['article' => $article,'name' => $this->getParameter('mailer_from')
                        ]
                    ),
                    'text/html'
                )
            ;
                $mailer->send($message);

            $this->addFlash(
                'success',
                'Action bien executé'
            );

            return $this->redirectToRoute('article_index');
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="article_show", methods={"GET"})
     */
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
            'isFavorite' => $this->getUser()->isFavorite($article)
        ]);
    }

    /**
     * @Route({
     *     "fr": "/{id}/editer",
     *     "en": "/{id}/edit",
     *     "es": "/{id}/editar"
     * }, name="article_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Article $article
     * @param Slugify $slugify
     * @return Response
     */
    public function edit(Request $request, Article $article, Slugify $slugify): Response
    {
        $this->denyAccessUnlessGranted('EDIT',$article);
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setSlug($slugify->generate($article->getTitle()));
            $this->getDoctrine()->getManager()->flush();


            $this->addFlash(
                'success',
                'Action bien executé'
            );

            return $this->redirectToRoute('article_index', [
                'id' => $article->getId(),
            ]);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="article_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Article $article): Response
    {
        $this->denyAccessUnlessGranted('DELETE', $article);

        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($article);
            $entityManager->flush();

            $this->addFlash(
                'danger',
                'Action bien executé'
            );
        }

        return $this->redirectToRoute('article_index');
    }

    /**
     * @Route("/{id}/favorite", name="article_favorite", methods={"GET","POST"})
     */
    public function favorite($id, ArticleRepository $articleRepository, EntityManagerInterface $em): Response
    {
        $article = $articleRepository->find($id);

        if ($this->getUser()->getFavori()->contains($article)) {
            $this->getUser()->removeFavori($article)   ;
        }
        else {
            $this->getUser()->addFavori($article);
        }

        $em->flush();

        return $this->json([
            'isFavorite' => $this->getUser()->isFavorite($article)
        ]);
    }
}
