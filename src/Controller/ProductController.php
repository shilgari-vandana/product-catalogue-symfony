<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Product;
use App\Events\CommentCreatedEvent;
use App\Form\CommentType;
use App\Repository\ProductRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller used to manage product contents in the public part of the site.
 *
 * @Route("/product")
 * @IsGranted("ROLE_USER")
 */
class ProductController extends AbstractController
{
     /**
     * @Route("/", defaults={"page": "1", "_format"="html"}, methods="GET", name="product_index")
     * @Route("/page/{page<[1-9]\d*>}", defaults={"_format"="html"}, methods="GET", name="product_index_paginated")
     * @Cache(smaxage="10")    
     */
    public function index(Request $request, int $page, string $_format, ProductRepository $products): Response
    {
        $tag = null;
        $latestPosts = $products->findLatest($page, $tag);
        return $this->render('product/index.'.$_format.'.twig', [
            'paginator' => $latestPosts,
        ]);
    }

    /**
     * @Route("/details/{slug}", methods="GET", name="product_post")
     *
     */
    public function postShow(Product $product): Response
    {
        return $this->render('product/post_show.html.twig', ['product' => $product]);
    }

    /**
     * @Route("/comment/{postSlug}/new", methods="POST", name="comment_new")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @ParamConverter("product", options={"mapping": {"postSlug": "slug"}})
     *
     */
    public function commentNew(Request $request, Product $product, EventDispatcherInterface $eventDispatcher): Response
    {
        $comment = new Comment();
        $comment->setAuthor($this->getUser());
        $comment->approved = 0;
        $product->addComment($comment);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            // When an event is dispatched, Symfony notifies it to all the listeners
            // and subscribers registered to it. 
            $eventDispatcher->dispatch(new CommentCreatedEvent($comment));
            $this->addFlash('success', 'product.comment_created');

            return $this->redirectToRoute('product_post', ['slug' => $product->getSlug()]);
        }

        return $this->render('product/comment_form_error.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * This controller is called directly via the render() function in the
     * product/post_show.html.twig template. That's why it's not needed to define
     * a route name for it.
     *
     * The "id" of the Product is passed in and then turned into a Product object
     * automatically by the ParamConverter.
     */
    public function commentForm(Product $product): Response
    {
        $form = $this->createForm(CommentType::class);

        return $this->render('product/_comment_form.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/search", methods="GET", name="product_search")
     */
    public function search(Request $request, ProductRepository $products): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->render('product/search.html.twig');
        }

        $query = $request->query->get('q', '');
        $limit = $request->query->get('l', 10);
        $foundPosts = $products->findBySearchQuery($query, $limit);

        $results = [];
        foreach ($foundPosts as $product) {
            $results[] = [
                'title' => htmlspecialchars($product->getName(), ENT_COMPAT | ENT_HTML5),
                'date' => $product->getPublishedAt()->format('M d, Y'),
                'author' => htmlspecialchars($product->getAuthor()->getFullName(), ENT_COMPAT | ENT_HTML5),
                'summary' => htmlspecialchars($product->getSummary(), ENT_COMPAT | ENT_HTML5),
                'url' => $this->generateUrl('product_post', ['slug' => $product->getSlug()]),
            ];
        }

        return $this->json($results);
    }
}
