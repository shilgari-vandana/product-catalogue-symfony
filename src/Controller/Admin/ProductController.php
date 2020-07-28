<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Entity\Product;
use App\Form\CommentType;
use App\Form\ProductType;
use App\Security\ProductVoter;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Controller used to manage product contents in the backend.
 *
 * @Route("/admin/product")
 * @IsGranted("ROLE_ADMIN")
 *
 */
class ProductController extends AbstractController
{
    /**
     * Lists all Product entities.
     *    
     * @Route("/", methods="GET", name="admin_index")
     * @Route("/", methods="GET", name="admin_product_index")
     */
    public function index(ProductRepository $products): Response
    {
        $authorPosts = $products->findBy(['author' => $this->getUser()], ['publishedAt' => 'DESC']);

        return $this->render('admin/product/index.html.twig', ['products' => $authorPosts]);
    }

    /**
     * Creates a new Product entity.
     *
     * @Route("/new", methods="GET|POST", name="admin_product_new")
     *     
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $product = new Product();
        $product->setAuthor($this->getUser());

        $form = $this->createForm(ProductType::class, $product)
            ->add('saveAndCreateNew', SubmitType::class);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $brochureFile = $form->get('brochure')->getData();
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();
                try {
                    $brochureFile->move($this->getParameter('products_directory'), $newFilename);
                } catch (FileException $e) {
                }
                $product->setFileName($newFilename);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            // Flash messages are used to notify the user about the result of the
            // actions. They are deleted automatically from the session as soon
            // as they are accessed.            
            $this->addFlash('success', 'product.created_successfully');

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('admin_product_new');
            }

            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);



    }

    /**
     * Finds and displays a Product entity.
     *
     * @Route("/{id<\d+>}", methods="GET", name="admin_product_show")
     */
    public function show(Product $product): Response
    {
        $this->denyAccessUnlessGranted(ProductVoter::SHOW, $product, 'Products can only be shown to their authors.');

        return $this->render('admin/product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * Displays a form to edit an existing Product entity.
     *
     * @Route("/{id<\d+>}/edit", methods="GET|POST", name="admin_product_edit")
     * @IsGranted("edit", subject="product", message="Products can only be edited by their authors.")
     */
    public function edit(Request $request, Product $product,SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $brochureFile = $form->get('brochure')->getData();
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();
                try {
                    $brochureFile->move($this->getParameter('products_directory'), $newFilename);
                }
                catch(FileException $e) {
                }
                $product->setFilename($newFilename);
            }

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'product.updated_successfully');

            return $this->redirectToRoute('admin_product_edit', ['id' => $product->getId()]);
        }

        return $this->render('admin/product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    

    

    /**
     * Deletes a Product entity.
     *
     * @Route("/{id}/delete", methods="POST", name="admin_product_delete")
     * @IsGranted("delete", subject="product")
     */
    public function delete(Request $request, Product $product): Response
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('admin_product_index');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();

        $this->addFlash('success', 'product.deleted_successfully');

        return $this->redirectToRoute('admin_product_index');
    }
    
    /**
     * Comment approved by admin
     * @Route("/{product_id}/comment/{id}/new/approve", methods="GET|POST", name="admin_comment_approve")
     * @IsGranted("edit", subject="product")
     * @ParamConverter("product", options={"mapping": {"product_id": "id"}})
     * @ParamConverter("comment", options={"mapping": {"id": "id"}})
     *
     */  
    public function commentApprove(Request $request, Product $product, Comment $comment, EventDispatcherInterface $eventDispatcher): Response
    {
        $comment->setAuthor($this->getUser());
        $comment->approved = true;
        $product->addComment($comment);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
           
        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();

        $this->addFlash('success', 'product.comment_approved_sucessfully');

        return $this->redirectToRoute('product_post', ['slug' => $product->getSlug()]);       
    }

     /**
     * Comment reject by admin
     * @Route("/{product_id}/comment/{id}/new/reject", methods="GET|POST", name="admin_comment_reject")
     * @IsGranted("edit", subject="product")
     * @ParamConverter("product", options={"mapping": {"product_id": "id"}})
     * @ParamConverter("comment", options={"mapping": {"id": "id"}})
     *
     */  
    public function commentReject(Request $request, Product $product, Comment $comment, EventDispatcherInterface $eventDispatcher): Response
    {
        $comment->setAuthor($this->getUser());
        $comment->approved = false;
        $product->addComment($comment);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
           
        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();

        $this->addFlash('success', 'product.comment_rejected_successfully');

        return $this->redirectToRoute('product_post', ['slug' => $product->getSlug()]);       
    }

     /**
     * Comment delete by admin
     * @Route("/{product_id}/comment/{id}/new/delete", methods="POST", name="admin_comment_delete")
     * @IsGranted("delete", subject="product")
     * @ParamConverter("product", options={"mapping": {"product_id": "id"}})
     * @ParamConverter("comment", options={"mapping": {"id": "id"}})
     *
     */  
    public function commentDelete(Request $request, Product $product, Comment $comment, EventDispatcherInterface $eventDispatcher): Response
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('admin_product_index');
        }

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();

        $this->addFlash('success', 'product.comment_deleted_successfully');

        return $this->redirectToRoute('product_post', ['slug' => $product->getSlug()]);         
    }
}
