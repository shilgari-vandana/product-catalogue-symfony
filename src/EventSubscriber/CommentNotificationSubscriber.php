<?php

namespace App\EventSubscriber;

use App\Entity\Comment;
use App\Events\CommentCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Notifies product author about new comments.
 *
 */
class CommentNotificationSubscriber implements EventSubscriberInterface
{
    private $mailer;
    private $translator;
    private $urlGenerator;
    private $sender;

    public function __construct(MailerInterface $mailer, UrlGeneratorInterface $urlGenerator, TranslatorInterface $translator, $sender)
    {
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->sender = $sender;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CommentCreatedEvent::class => 'onCommentCreated',
        ];
    }

    public function onCommentCreated(CommentCreatedEvent $event): void
    {
        /** @var Comment $comment */
        $comment = $event->getComment(); 

       
        $product = $comment->getProduct();
        $generateUrl = "product_post";
        $title = $product->getName();
       

        $linkToPost = $this->urlGenerator->generate($generateUrl, [
            'slug' => $product->getSlug(),
            '_fragment' => 'comment_'.$comment->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $subject = $this->translator->trans('notification.comment_created');
        $body = $this->translator->trans('notification.comment_created.description', [
            '%title%' => $title,
            '%link%' => $linkToPost,
        ]);

        // See https://symfony.com/doc/current/mailer.html
        $email = (new Email())
            ->from($this->sender)
            ->to($product->getAuthor()->getEmail())
            ->subject($subject)
            ->html($body)
        ;

        // In config/packages/dev/mailer.yaml the delivery of messages is disabled.
        // That's why in the development environment you won't actually receive any email.
        // However, you can inspect the contents of those unsent emails using the debug toolbar.
        $this->mailer->send($email);
    }
}
